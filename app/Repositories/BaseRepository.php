<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*'])
    {
        return $this->model->all($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'])
    {
        return $this->model->paginate($perPage, $columns);
    }

    public function find(int|string $id, array $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    public function findOrFail(int|string $id, array $columns = ['*'])
    {
        return $this->model->findOrFail($id, $columns);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int|string $id, array $data)
    {
        $model = $this->findOrFail($id);
        $model->update($data);
        return $model;
    }

    public function delete(int|string $id): bool
    {
        $model = $this->findOrFail($id);
        return $model->delete();
    }

    public function query()
    {
        return $this->model->newQuery();
    }
}
