<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $organization;

    public function setUp(): void
    {
        parent::setUp();

        // Create organization
        $this->organization = Organization::factory()->create();

        // Create user belonging to organization
        $this->user = User::factory()->for($this->organization)->create();

        // Authenticate user for tests
        $this->actingAs($this->user);
    }

    /** @test */
    public function anyone_can_list_events_for_organization()
    {
        $events = Event::factory()->count(3)->for($this->organization)->create();

        $response = $this->getJson("/api/{$this->organization->slug}/events");

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function authenticated_user_can_create_event_for_their_organization()
    {
        $payload = [
            'title' => 'Test Event',
            'description' => 'Event Description',
            'venue' => 'Main Hall',
            'date' => now()->addDay()->toDateTimeString(),
            'price' => 100,
            'max_attendees' => 50,
            'status' => 'draft',
        ];

        $response = $this->postJson("/api/{$this->organization->slug}/events", $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'Test Event']);

        $this->assertDatabaseHas('events', [
            'title' => 'Test Event',
            'organization_id' => $this->organization->id,
        ]);
    }

    /** @test */
    public function authenticated_user_cannot_create_event_for_other_organization()
    {
        $otherOrg = Organization::factory()->create();

        $payload = [
            'title' => 'Invalid Event',
            'description' => 'Invalid',
            'venue' => 'Main Hall',
            'date' => now()->addDay()->toDateTimeString(),
            'price' => 50,
            'max_attendees' => 20,
            'status' => 'draft',
        ];

        $response = $this->postJson("/api/{$otherOrg->slug}/events", $payload);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('events', ['title' => 'Invalid Event']);
    }

    /** @test */
    public function authenticated_user_can_update_event()
    {
        $event = Event::factory()->for($this->organization)->create([
            'title' => 'Original Title',
            'venue' => 'Old Venue',
            'date' => now()->addDays(5),
            'max_attendees' => 30,
        ]);

        $payload = [
            'title' => 'Updated Title',
            'venue' => 'New Venue',
            'date' => now()->addDays(6)->toDateTimeString(),
            'max_attendees' => 40,
        ];

        $response = $this->putJson("/api/{$this->organization->slug}/events/{$event->id}", $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Updated Title']);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Updated Title',
            'venue' => 'New Venue',
            'max_attendees' => 40,
        ]);
    }

    /** @test */
    public function authenticated_user_cannot_update_event_of_other_organization()
    {
        $otherOrg = Organization::factory()->create();
        $event = Event::factory()->for($otherOrg)->create();

        $payload = [
            'title' => 'Hacked Title',
        ];

        $response = $this->putJson("/api/{$otherOrg->slug}/events/{$event->id}", $payload);

        // Because authenticated user does not belong to otherOrg
        $response->assertStatus(403);
    }

    /** @test */
    public function authenticated_user_can_soft_delete_event()
    {
        $event = Event::factory()->for($this->organization)->create();

        $response = $this->deleteJson("/api/{$this->organization->slug}/events/{$event->id}");

        $response->assertNoContent();

        $this->assertSoftDeleted('events', ['id' => $event->id]);
    }

    /** @test */
    public function authenticated_user_can_restore_soft_deleted_event()
    {
        $event = Event::factory()->for($this->organization)->create();
        $event->delete();

        $response = $this->postJson("/api/{$this->organization->slug}/events/{$event->id}/restore");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $event->id]);

        $this->assertDatabaseHas('events', ['id' => $event->id, 'deleted_at' => null]);
    }

    /** @test */
    public function authenticated_user_can_force_delete_event()
    {
        $event = Event::factory()->for($this->organization)->create();
        $event->delete();

        $response = $this->deleteJson("/api/{$this->organization->slug}/events/{$event->id}/force-delete");

        $response->assertNoContent();

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }
}
