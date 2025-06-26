<?php

namespace App\Services\Admin;

use App\Repositories\Admin\SubsectionRepository;
use App\Services\BaseService;
use Illuminate\Support\Str;

class SubsectionService extends BaseService
{
    public function __construct(SubsectionRepository $repository)
    {
        parent::__construct($repository);
    }

    public function create(array $data)
    {
        if (!isset($data['slug']) || $data['slug'] === null) {
            $data['slug'] = Str::slug($data['name']);
            if ($this->repository->findBySlug($data['slug'])) {
                abort(403, 'Subsection with this slug already exists.');
            }
        }


        if (!isset($data['order']) || $data['order'] === null) {
            $lastOrder = $this->repository->getMaxOrder();
            $data['order'] = $lastOrder !== null ? $lastOrder + 1 : 1;
        }

        return parent::create($data);
    }
    public function update(int|string $id, array $data)
    {
        if (!isset($data['slug']) || $data['slug'] === null) {
            $data['slug'] = Str::slug($data['name']);
            $existingSubsection = $this->repository->findBySlug($data['slug']);
            if ($existingSubsection && $existingSubsection->id !== $id) {
                abort(403, 'Subsection with this slug already exists.');
            }
        }

        if (!isset($data['order']) || $data['order'] === null) {
            $lastOrder = $this->repository->getMaxOrder();
            $data['order'] = $lastOrder !== null ? $lastOrder + 1 : 1;
        }

        return parent::update($id, $data);
    }

    public function attachFile(int|string $id, $file, $type)
    {
        $subsection = $this->findOrFail($id);
        $filePath = $file->store('subsections/' . $type, 'public');

        $subsection->files()->create([
            'path' => $filePath,
            'type' => $type,
        ]);

        return $subsection;
    }
}
