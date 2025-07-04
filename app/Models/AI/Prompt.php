<?php

namespace App\Models\AI;

use App\Models\BaseModel;
use App\Models\User;

class Prompt extends BaseModel
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
