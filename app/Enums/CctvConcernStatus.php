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

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = ['' => 'All'];

        foreach (self::cases() as $status) {
            $options[$status->value] = $status->value;
        }

        return $options;
    }
}
