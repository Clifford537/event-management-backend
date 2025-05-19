<?php
namespace App\Repositories;
use App\Models\Event;
class EventRepository
{
    public function getByOrganization(int $organizationId, array $filters = [])
    {
        $query = Event::where('organization_id', $organizationId);
        if (isset($filters['upcoming'])) {
            $query->where('date', '>=', now())->where('status', 'published');
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        return $query->paginate(10);
    }
    public function find(int $id)
    {
        return Event::findOrFail($id);
    }
    public function create(int $organizationId, array $data)
    {
        return Event::create(array_merge($data, ['organization_id' => $organizationId]));
    }
    public function update(int $id, array $data)
    {
        $event = Event::findOrFail($id);
        $event->update($data);
        return $event;
    }
    public function delete(int $id)
    {
        $event = Event::findOrFail($id);
        $event->delete();
    }
}