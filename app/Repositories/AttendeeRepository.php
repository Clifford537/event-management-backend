<?php
namespace App\Repositories;
use App\Models\Attendee;
class AttendeeRepository
{
    public function getByEvent(int $eventId)
    {
        return Attendee::where('event_id', $eventId)->paginate(10);
    }
    public function create(int $eventId, array $data)
    {
        return Attendee::create(array_merge($data, ['event_id' => $eventId]));
    }
}