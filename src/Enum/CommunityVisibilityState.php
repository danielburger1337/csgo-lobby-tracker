<?php declare(strict_types=1);

namespace App\Enum;

enum CommunityVisibilityState: int
{
    case Private = 1;
    case Public = 3;
}
