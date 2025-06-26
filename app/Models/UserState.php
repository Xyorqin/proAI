<?php

namespace App\Models;

use App\Enums\UserStateLevelEnum;

class UserState extends BaseModel
{
    protected $casts = [
        'level' => UserStateLevelEnum::class,
    ];
}
