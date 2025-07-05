<?php

namespace App\Models\AI;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserConversation extends BaseModel
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
