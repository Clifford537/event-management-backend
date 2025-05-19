<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_list_organizations()
    {
        Sanctum::actingAs(User::factory()->create());

        Organization::factory()->count(3)->create();

        $response = $this->getJson('/api/organizations');

        $response->assertOk()
                 ->assertJsonCount(3);
    }

    /** @test */
    public function authenticated_user_can_create_organization()
    {
        Sanctum::actingAs($user = User::factory()->create());

        $payload = ['name' => 'New Organization'];

        $response = $this->postJson('/api/organizations', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure(['organization' => ['id', 'name', 'slug']]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'New Organization',
            'slug' => 'new-organization'
        ]);

        // Confirm user is associated with the new org
        $user->refresh();
        $this->assertEquals($response->json('organization.id'), $user->organization_id);
    }

    /** @test */
    public function organization_creation_requires_unique_name()
    {
        Sanctum::actingAs(User::factory()->create());

        Organization::factory()->create(['name' => 'Existing Org']);

        $response = $this->postJson('/api/organizations', ['name' => 'Existing Org']);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function authenticated_user_can_show_organization_by_slug()
    {
        Sanctum::actingAs(User::factory()->create());

        $organization = Organization::factory()->create();

        $response = $this->getJson('/api/organizations/' . $organization->slug);

        $response->assertOk()
                 ->assertJson([
                     'organization' => [
                         'id' => $organization->id,
                         'name' => $organization->name,
                         'slug' => $organization->slug,
                     ]
                 ]);
    }

    /** @test */
    public function show_returns_404_for_nonexistent_organization()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/organizations/nonexistent-slug');

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Organization not found.']);
    }

    /** @test */
    public function authenticated_user_can_update_organization()
    {
        Sanctum::actingAs(User::factory()->create());

        $organization = Organization::factory()->create(['name' => 'Old Name']);

        $payload = ['name' => 'Updated Organization'];

        $response = $this->putJson('/api/organizations/' . $organization->slug, $payload);

        $response->assertOk()
                 ->assertJson([
                     'organization' => [
                         'id' => $organization->id,
                         'name' => 'Updated Organization',
                         'slug' => 'updated-organization',
                     ]
                 ]);

        $this->assertDatabaseHas('organizations', ['name' => 'Updated Organization']);
    }

    /** @test */
    public function update_returns_404_for_nonexistent_organization()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->putJson('/api/organizations/nonexistent-slug', ['name' => 'Name']);

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Organization not found.']);
    }

    /** @test */
    public function update_requires_unique_name()
    {
        Sanctum::actingAs(User::factory()->create());

        Organization::factory()->create(['name' => 'Existing Org']);
        $organization = Organization::factory()->create(['name' => 'Another Org']);

        $response = $this->putJson('/api/organizations/' . $organization->slug, ['name' => 'Existing Org']);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function authenticated_user_can_soft_delete_organization()
    {
        Sanctum::actingAs(User::factory()->create());

        $organization = Organization::factory()->create();

        $response = $this->deleteJson('/api/organizations/' . $organization->slug);

        $response->assertOk()
                 ->assertJson(['message' => 'Organization deleted successfully.']);

        $this->assertSoftDeleted('organizations', ['id' => $organization->id]);
    }

    /** @test */
    public function destroy_returns_404_for_nonexistent_organization()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->deleteJson('/api/organizations/nonexistent-slug');

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Organization not found.']);
    }
}
