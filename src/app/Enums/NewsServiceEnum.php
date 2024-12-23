<?php

namespace App\Enums;

enum NewsServiceEnum: string
{
    case NEWS_API = 'newsapi';
    case NEW_YORK_TIMES = 'nyt';
    case GUARDIAN = 'guardian';
}
