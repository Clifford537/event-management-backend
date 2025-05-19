<?php
namespace App\Services;
use App\Repositories\AttendeeRepository;
use App\Repositories\EventRepository;
class AttendeeService
{
    protected $attendeeRepository;
    protected $eventRepository;
    public function __construct(AttendeeRepository $attendeeRepository, EventRepository $eventRepository)
    {
        $this->attendeeRepository = $attendeeRepository;
        $this->eventRepository = $eventRepository;
    }
    public function getAttendees(int $eventId)
    {
        return $this->attendeeRepository->getByEvent($eventId);
    }
    public function registerAttendee(int $eventId, array $data)
    {
        $event = $this->eventRepository->find($eventId);
        $attendeeCount = Attendee::where('event_id', $eventId)->count();
        if ($attendeeCount >= $event->max_attendees) {
            throw new \Exception('Event is fully booked.');
        }
        return $this->attendeeRepository->create($eventId, $data);
    }
}