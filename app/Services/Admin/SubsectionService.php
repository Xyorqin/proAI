<?php

namespace App\Services\Admin;

use App\Models\Structure\Subsection;
use App\Models\Structure\SubsectionFile;
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

        $subsection = parent::create($data);

        $this->toggleFile($subsection->id, $data['files']);

        return $subsection;
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

        if (isset($data['files']) && is_array($data['files'])) {
            $this->toggleFile($id, $data['files']);
        }

        return parent::update($id, $data);
    }

    public function attachFile($file, $type)
    {
        $filePath = $file->store('subsections/' . $type, 'public');

        $fileModel = SubsectionFile::create([
            'path' => $filePath,
            'type' => $type,
        ]);

        return $fileModel;
    }

    public function toggleFile(int|string $id, array $fileDatas)
    {
        foreach ($fileDatas as $fileData) {
            if (!isset($fileData['file_id']) || !is_int($fileData['file_id'])) {
                abort(422, 'Invalid file ID provided.');
            }

            $file = SubsectionFile::find($fileData['file_id']);
            if (!$file) {
                abort(404, 'File not found.');
            }

            $file->update([
                'subsection_id' => $id,
                'content' => $fileData['content'] ?? null,
            ]);
        }

        return true;
    }
}
