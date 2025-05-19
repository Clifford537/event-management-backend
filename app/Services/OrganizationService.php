<?php
namespace App\Services;
use App\Repositories\OrganizationRepository;
class OrganizationService
{
    protected $repository;
    public function __construct(OrganizationRepository $repository)
    {
        $this->repository = $repository;
    }
    public function create(array $data)
    {
        $data['slug'] = \Str::slug($data['name']);
        return $this->repository->create($data);
    }
    public function update(int $id, array $data)
    {
        if (isset($data['name'])) {
            $data['slug'] = \Str::slug($data['name']);
        }
        return $this->repository->update($id, $data);
    }
    public function delete(int $id)
    {
        return $this->repository->delete($id);
    }
}