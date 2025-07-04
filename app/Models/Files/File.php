<?php

namespace App\Models\Files;

use App\Models\BaseModel;
use App\Models\Structure\Subsection;

class File extends BaseModel
{
    public function subsection()
    {
        return $this->belongsTo(Subsection::class, 'subsection_id');
    }
}
