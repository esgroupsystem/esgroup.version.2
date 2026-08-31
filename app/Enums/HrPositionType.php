<?php

declare(strict_types=1);

namespace App\Enums;

enum HrPositionType: string
{
    case Driver = 'Driver';
    case Conductor = 'Conductor';
}
