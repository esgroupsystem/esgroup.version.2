<?php

declare(strict_types=1);

namespace App\Enums;

enum CctvConcernStatus: string
{
    case Open = 'Open';
    case InProgress = 'In Progress';
    case Fixed = 'Fixed';
    case Closed = 'Closed';

    public function isCompleted(): bool
    {
        return in_array($this, [self::Fixed, self::Closed], true);
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }

    /** @return list<string> Open and In Progress */
    public static function activeValues(): array
    {
        return [self::Open->value, self::InProgress->value];
    }

    /** @return list<string> Fixed and Closed */
    public static function completedValues(): array
    {
        return [self::Fixed->value, self::Closed->value];
    }
}
