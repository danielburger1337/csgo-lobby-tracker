<?php declare(strict_types=1);

namespace App\Enum;

enum PersonaState: int
{
    case Offline = 0;
    case Online = 1;
    case Snooze = 2;
    case Away = 3;
}
