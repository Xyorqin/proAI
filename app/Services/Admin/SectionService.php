<?php

namespace App\Services\Admin;

use App\Repositories\Admin\SectionRepository;
use App\Services\BaseService;
use Illuminate\Support\Str;


class SectionService extends BaseService
{
    public function __construct(SectionRepository $repository)
    {
        parent::__construct($repository);
    }

    public function create(array $data)
    {
        if (!isset($data['slug']) || $data['slug'] === null) {
            $data['slug'] = Str::slug($data['name']);
            if ($this->repository->findBySlug($data['slug'])) {
                abort(403, 'Section with this slug already exists.');
            }
        }

        return parent::create($data);
    }

    public function update(int|string $id, array $data)
    {
        if (!isset($data['slug']) || $data['slug'] === null) {
            $data['slug'] = Str::slug($data['name']);
            $existingSection = $this->repository->findBySlug($data['slug']);
            if ($existingSection && $existingSection->id !== $id) {
                abort(403, 'Section with this slug already exists.');
            }
        }

        return parent::update($id, $data);
    }

    public function delete(int|string $id): bool
    {
        $model = $this->repository->findOrFail($id);
        $model->subsections()->delete();
        return parent::delete($id);
    }
}
