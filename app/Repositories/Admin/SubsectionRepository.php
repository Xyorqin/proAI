<?php

namespace App\Repositories\Admin;

use App\Models\Structure\Subsection;
use App\Repositories\BaseRepository;

class SubsectionRepository extends BaseRepository
{
    public function __construct(Subsection $model)
    {
        parent::__construct($model);
    }

    public function getMaxOrder(): ?int
    {
        return $this->model->max('order');
    }

    public function findBySlug(string $slug): ?Subsection
    {
        return $this->model->where('slug', $slug)->first();
    }
}
