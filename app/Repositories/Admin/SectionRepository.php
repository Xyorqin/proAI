<?php

namespace App\Repositories\Admin;

use App\Models\Structure\Section;
use App\Repositories\BaseRepository;

class SectionRepository extends BaseRepository
{
    public function __construct(Section $model)
    {
        parent::__construct($model);
    }

    public function findBySlug(string $slug): ?Section
    {
        return $this->model->where('slug', $slug)->first();
    }
}
