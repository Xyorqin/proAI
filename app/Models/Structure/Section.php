<?php

namespace App\Models\Structure;

use App\Models\BaseModel;

class Section extends BaseModel
{
    public function subsections()
    {
        return $this->hasMany(Subsection::class);
    }
    
}
