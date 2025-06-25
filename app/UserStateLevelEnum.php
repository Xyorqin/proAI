<?php

namespace App;

enum UserStateLevelEnum: string
{
    case MENU_LEVEL = 'menu';
    case SECTION_LEVEL = 'section';
    case AI_LEVEL = 'ai';
}
