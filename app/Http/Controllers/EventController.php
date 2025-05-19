<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    /**
     * List all events for an organization.
     */
    public function index(Request $request, $organizationSlug)
    {
        try {
            $organization = Organization::where('slug', $organizationSlug)->firstOrFail();

            $events = Event::where('organization_id', $organization->id)->get();

            return response()->json(['data' => $events], 200);
        } catch (\Exception $e) {
            Log::error('Failed to list events', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to list events.'], 500);
        }
    }

    /**
     * Create a new event for the authenticated user's organization.
     */
    public function store(Request $request, $organizationSlug)
    {
        try {
            $organization = Organization::where('slug', $organizationSlug)->firstOrFail();
            $user = auth()->user();

            if ($user->organization_id !== $organization->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $data = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'venue' => 'required|string|max:255',
                'date' => 'required|date|after:now',
                'price' => 'nullable|numeric',
                'max_attendees' => 'required|integer|min:1',
                'status' => 'nullable|in:draft,published,cancelled',
            ]);

            $event = Event::create(array_merge($data, [
                'organization_id' => $organization->id,
            ]));

            return response()->json(['event' => $event], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create event', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to create event.'], 500);
        }
    }

    /**
     * Show a specific event by ID.
     */
    public function show(Request $request, $organizationSlug, $eventId)
    {
        try {
            $organization = Organization::where('slug', $organizationSlug)->firstOrFail();

            $event = Event::where('organization_id', $organization->id)
                ->where('id', $eventId)
                ->first();

            if (!$event) {
                return response()->json(['error' => 'Event not found'], 404);
            }

            return response()->json(['event' => $event], 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch event', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch event.'], 500);
        }
    }

    /**
     * Update an existing event.
     */
    public function update(Request $request, $organizationSlug, $eventId)
    {
        try {
            $organization = Organization::where('slug', $organizationSlug)->firstOrFail();
            $user = auth()->user();

            if ($user->organization_id !== $organization->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $event = Event::where('organization_id', $organization->id)
                ->where('id', $eventId)
                ->first();

            if (!$event) {
                return response()->json(['error' => 'Event not found'], 404);
            }

            $data = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'venue' => 'sometimes|required|string|max:255',
                'date' => 'sometimes|required|date|after:now',
                'price' => 'nullable|numeric',
                'max_attendees' => 'sometimes|required|integer|min:1',
                'status' => 'nullable|in:draft,published,cancelled',
            ]);

            $event->update($data);

            return response()->json(['event' => $event], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update event', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update event.'], 500);
        }
    }

    /**
     * Soft delete an event.
     */
    public function destroy(Request $request, $organizationSlug, $eventId)
    {
        try {
            $organization = Organization::where('slug', $organizationSlug)->firstOrFail();
            $user = auth()->user();

            if ($user->organization_id !== $organization->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $event = Event::where('organization_id', $organization->id)
                ->where('id', $eventId)
                ->first();

            if (!$event) {
                return response()->json(['error' => 'Event not found'], 404);
            }

            $event->delete();

            return response()->noContent(); // 204 No Content
        } catch (\Exception $e) {
            Log::error('Failed to delete event', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to delete event.'], 500);
        }
    }

    /**
     * List all soft-deleted (trashed) events for an organization.
     */
    public function trashed(Request $request, $organizationSlug)
    {
        try {
            $organization = Organization::where('slug', $organizationSlug)->firstOrFail();
            $user = auth()->user();

            if ($user->organization_id !== $organization->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $trashedEvents = Event::onlyTrashed()
                ->where('organization_id', $organization->id)
                ->get();

            return response()->json(['data' => $trashedEvents], 200);
        } catch (\Exception $e) {
            Log::error('Failed to list trashed events', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to list trashed events.'], 500);
        }
    }

    /**
     * Restore a soft-deleted event.
     */
    public function restore(Request $request, $organizationSlug, $eventId)
    {
        try {
            $organization = Organization::where('slug', $organizationSlug)->firstOrFail();
            $user = auth()->user();

            $event = Event::onlyTrashed()
                ->where('id', $eventId)
                ->where('organization_id', $organization->id)
                ->first();

            if (!$event) {
                return response()->json(['error' => 'Event not found'], 404);
            }

            if ($user->organization_id !== $organization->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $event->restore();

            return response()->json(['event' => $event], 200);
        } catch (\Exception $e) {
            Log::error('Failed to restore event', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to restore event.'], 500);
        }
    }

    /**
     * Permanently delete (force delete) a soft-deleted event.
     */
    public function forceDelete(Request $request, $organizationSlug, $eventId)
    {
        try {
            $organization = Organization::where('slug', $organizationSlug)->firstOrFail();
            $user = auth()->user();

            $event = Event::withTrashed()
                ->where('id', $eventId)
                ->where('organization_id', $organization->id)
                ->first();

            if (!$event) {
                return response()->json(['error' => 'Event not found'], 404);
            }

            if ($user->organization_id !== $organization->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $event->forceDelete();

            return response()->noContent();
        } catch (\Exception $e) {
            Log::error('Failed to force delete event', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to force delete event.'], 500);
        }
    }
}
