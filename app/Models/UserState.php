<?php

namespace App\Models;

use App\UserStateLevelEnum;

class UserState extends BaseModel
{
    protected $casts = [
        'level' => UserStateLevelEnum::class,
    ];
}
