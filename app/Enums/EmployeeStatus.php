<?php

declare(strict_types=1);

namespace App\Enums;

enum EmployeeStatus: string
{
    case Active = 'Active';
    case ActiveReEntry = 'Active(Re-Entry)';
    case Inactive = 'Inactive';
    case Suspended = 'Suspended';
    case Terminated = 'Terminated';
    case TerminatedAwol = 'Terminated(due to AWOL)';
    case EndOfContract = 'End of Contract';
    case Retrench = 'Retrench';
    case Retired = 'Retired';
    case Resigned = 'Resigned';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** @return list<string> */
    public static function activeValues(): array
    {
        return [self::Active->value, self::ActiveReEntry->value];
    }

    /** @return list<string> */
    public static function inactiveValues(): array
    {
        return [
            self::Inactive->value,
            self::Resigned->value,
            self::Terminated->value,
            self::TerminatedAwol->value,
            self::EndOfContract->value,
            self::Retrench->value,
            self::Retired->value,
        ];
    }
}
