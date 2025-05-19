<?php
namespace App\Services;
use App\Repositories\EventRepository;
class EventService
{
    protected $eventRepository;
    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }
    public function getEvents(int $organizationId, array $filters = [])
    {
        return $this->eventRepository->getByOrganization($organizationId, $filters);
    }
    public function createEvent(int $organizationId, array $data)
    {
        $data['status'] = $data['status'] ?? 'published';
        return $this->eventRepository->create($organizationId, $data);
    }
    public function updateEvent(int $id, array $data)
    {
        return $this->eventRepository->update($id, $data);
    }
    public function deleteEvent(int $id)
    {
        return $this->eventRepository->delete($id);
    }
}