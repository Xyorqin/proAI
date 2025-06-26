<?php

namespace App\Enums;

enum UserStateLevelEnum: string
{
    case MENU_LEVEL = 'menu';
    case SECTION_LEVEL = 'section';
    case FILE_LEVEL = 'file';
    case AI_LEVEL = 'ai';
}
