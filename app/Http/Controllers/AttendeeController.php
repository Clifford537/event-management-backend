<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AttendeeController extends Controller
{
    /**
     * @group Attendees
     * List attendees for an event
     * @route GET /api/{org_slug}/events/{event}/attendees
     * @response 200 {"attendees":[{"id":1,"event_id":2,"name":"John Doe","email":"john.doe@example.com","phone":"+1234567890"}]}
     * @response 404 {"error":"Event not found or does not belong to the organization"}
     */
    public function index(Request $request, string $org_slug, Event $event)
    {
        $organization = $request->attributes->get('organization');

        if ($event->organization_id !== $organization->id) {
            return response()->json(['error' => 'Event not found or does not belong to the organization'], 404);
        }

        $attendees = Attendee::where('event_id', $event->id)->get();

        return response()->json(['attendees' => $attendees], 200);
    }

    /**
     * @group Attendees
     * Register an attendee for an event
     * @route POST /api/{org_slug}/events/{event}/attendees
     * @bodyParam name string required The name of the attendee
     * @bodyParam email string required The email of the attendee
     * @bodyParam phone string required The phone number of the attendee
     * @response 201 {"attendee":{"id":1,"event_id":2,"name":"John Doe","email":"john.doe@example.com","phone":"+1234567890"}}
     * @response 422 {"message":"Validation failed.","errors":{...}}
     * @response 404 {"error":"Event not found or does not belong to the organization"}
     */
    public function store(Request $request, string $org_slug, Event $event)
    {
        try {
            $organization = $request->attributes->get('organization');

            if ($event->organization_id !== $organization->id) {
                return response()->json(['error' => 'Event not found or does not belong to the organization'], 404);
            }

            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
            ]);

            $attendee = Attendee::create([
                'event_id' => $event->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
            ]);

            return response()->json(['attendee' => $attendee], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to register attendee', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to register attendee.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @group Attendees
     * Get details of an attendee
     * @route GET /api/{org_slug}/events/{event}/attendees/{attendee}
     * @response 200 {"attendee":{...}}
     * @response 404 {"error":"Attendee not found or does not belong to the event/organization"}
     */
    public function show(Request $request, string $org_slug, Event $event, Attendee $attendee)
    {
        $organization = $request->attributes->get('organization');

        if (
            $event->organization_id !== $organization->id ||
            $attendee->event_id !== $event->id
        ) {
            return response()->json(['error' => 'Attendee not found or does not belong to the event/organization'], 404);
        }

        return response()->json(['attendee' => $attendee], 200);
    }

    /**
     * @group Attendees
     * Update an attendee's information
     * @route PUT /api/{org_slug}/events/{event}/attendees/{attendee}
     * @bodyParam name string The name of the attendee
     * @bodyParam email string The email of the attendee
     * @bodyParam phone string The phone number of the attendee
     * @response 200 {"attendee":{...}}
     * @response 404 {"error":"Attendee not found or does not belong to the event/organization"}
     * @response 422 {"message":"Validation failed.","errors":{...}}
     */
    public function update(Request $request, string $org_slug, Event $event, Attendee $attendee)
    {
        $organization = $request->attributes->get('organization');

        if (
            $event->organization_id !== $organization->id ||
            $attendee->event_id !== $event->id
        ) {
            return response()->json(['error' => 'Attendee not found or does not belong to the event/organization'], 404);
        }

        try {
            $data = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|max:255',
                'phone' => 'sometimes|required|string|max:20',
            ]);

            $attendee->update($data);

            return response()->json(['attendee' => $attendee], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update attendee', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to update attendee.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @group Attendees
     * Delete an attendee
     * @route DELETE /api/{org_slug}/events/{event}/attendees/{attendee}
     * @response 200 {"message":"Attendee deleted successfully."}
     * @response 404 {"error":"Attendee not found or does not belong to the event/organization"}
     */
    public function destroy(Request $request, string $org_slug, Event $event, Attendee $attendee)
    {
        $organization = $request->attributes->get('organization');

        if (
            $event->organization_id !== $organization->id ||
            $attendee->event_id !== $event->id
        ) {
            return response()->json(['error' => 'Attendee not found or does not belong to the event/organization'], 404);
        }

        try {
            $attendee->delete();

            return response()->json(['message' => 'Attendee deleted successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete attendee', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to delete attendee.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
