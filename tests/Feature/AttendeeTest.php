<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Attendee;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendeeTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $organization;
    protected $event;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test organization, user and event
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->for($this->organization)->create();
        $this->event = Event::factory()->for($this->organization)->create();

        // Simulate authenticated user
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_lists_attendees_for_an_event()
    {
        Attendee::factory()->count(3)->create(['event_id' => $this->event->id]);

        $response = $this->getJson("/api/{$this->organization->slug}/events/{$this->event->id}/attendees");

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'attendees');
    }

    /** @test */
    public function it_creates_an_attendee_for_an_event()
    {
        $payload = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
        ];

        $response = $this->postJson("/api/{$this->organization->slug}/events/{$this->event->id}/attendees", $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment(['email' => 'john@example.com']);

        $this->assertDatabaseHas('attendees', [
            'event_id' => $this->event->id,
            'email' => 'john@example.com',
        ]);
    }

    /** @test */
    public function it_returns_404_if_event_does_not_belong_to_organization_on_create()
    {
        // Create a separate organization and event not belonging to the test user
        $otherOrg = Organization::factory()->create();
        $otherEvent = Event::factory()->for($otherOrg)->create();

        $payload = [
            'name' => 'Invalid Attendee',
            'email' => 'bad@example.com',
            'phone' => '+000000000',
        ];

        // Attempt to create attendee on another organization's event using current org slug
        $response = $this->postJson("/api/{$this->organization->slug}/events/{$otherEvent->id}/attendees", $payload);

        $response->assertStatus(404); // Should not find the event under given organization slug
    }

    /** @test */
    public function it_shows_a_single_attendee()
    {
        $attendee = Attendee::factory()->create(['event_id' => $this->event->id]);

        $response = $this->getJson("/api/{$this->organization->slug}/events/{$this->event->id}/attendees/{$attendee->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $attendee->id]);
    }

    /** @test */
    public function it_updates_an_attendee()
    {
        $attendee = Attendee::factory()->create(['event_id' => $this->event->id]);

        $payload = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->putJson("/api/{$this->organization->slug}/events/{$this->event->id}/attendees/{$attendee->id}", $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('attendees', [
            'id' => $attendee->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /** @test */
    public function it_deletes_an_attendee()
    {
        $attendee = Attendee::factory()->create(['event_id' => $this->event->id]);

        $response = $this->deleteJson("/api/{$this->organization->slug}/events/{$this->event->id}/attendees/{$attendee->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Attendee deleted successfully.']);

        $this->assertSoftDeleted('attendees', [
            'id' => $attendee->id,
        ]);
    }
}
