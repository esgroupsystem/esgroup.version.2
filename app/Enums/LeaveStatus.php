<?php

declare(strict_types=1);

namespace App\Enums;

enum LeaveStatus: string
{
    case Active = 'Active';
    case Inactive = 'Inactive';
    case Completed = 'Completed';
    case Cancelled = 'Cancelled';
    case Terminated = 'Terminated';

    public function isClosed(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled, self::Terminated], true);
    }
}
