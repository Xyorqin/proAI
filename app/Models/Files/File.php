<?php

namespace App\Models\Files;

use App\Models\AI\Prompt;
use App\Models\BaseModel;
use App\Models\Structure\Subsection;

class File extends BaseModel
{
    public function subsection()
    {
        return $this->belongsTo(Subsection::class, 'subsection_id');
    }

    public function prompt()
    {
        return $this->hasOne(Prompt::class, 'file_id');
    }
}
