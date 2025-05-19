<?php
namespace App\Repositories;
use App\Models\Organization;
class OrganizationRepository
{
    public function create(array $data)
    {
        return Organization::create($data);
    }
    public function findBySlug(string $slug)
    {
        return Organization::where('slug', $slug)->first();
    }
    public function update(int $id, array $data)
    {
        $organization = Organization::findOrFail($id);
        $organization->update($data);
        return $organization;
    }
    public function delete(int $id)
    {
        $organization = Organization::findOrFail($id);
        $organization->delete();
    }
}