<?php

namespace App\Models\Structure;

use App\Models\BaseModel;

class Subsection extends BaseModel
{
    public function files()
    {
        return $this->hasMany(SubsectionFile::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
