<?php

declare(strict_types=1);

namespace App\Enums;

enum ItTicketStatus: string
{
    case Approval = 'Approval';
    case Pending = 'Pending';
    case InProgress = 'In Progress';
    case Completed = 'Completed';
    case Disapproved = 'Disapproved';

    public function canBeDeleted(): bool
    {
        return ! in_array($this, [self::InProgress, self::Completed], true);
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }
}
