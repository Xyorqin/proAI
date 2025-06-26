<?php

namespace App\Services;

use App\Repositories\BaseRepository;

abstract class BaseService
{
    protected BaseRepository $repository;

    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
    }

    public function all()
    {
        return $this->repository->all();
    }

    public function paginate(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    public function find(int|string $id)
    {
        return $this->repository->find($id);
    }

    public function findOrFail(int|string $id)
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update(int|string $id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int|string $id): bool
    {
        return $this->repository->delete($id);
    }
}
