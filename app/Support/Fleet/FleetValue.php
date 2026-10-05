<?php

declare(strict_types=1);

namespace App\Support\Fleet;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/** Small value rules shared by the Fleet services (bus and for-sale forms, folder tabs). */
final class FleetValue
{
    /** Trimmed and upper-cased; blank becomes null. */
    public static function upper(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_strtoupper($value);
    }

    /** Trimmed; blank becomes null. */
    public static function text(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /** Whole days from the breakdown start to its end (or today). 0 without a start or when the end is earlier. */
    public static function breakdownDays(CarbonInterface|string|null $start, CarbonInterface|string|null $end): int
    {
        if ($start === null || $start === '') {
            return 0;
        }

        $from = Carbon::parse($start)->startOfDay();
        $to = $end !== null && $end !== '' ? Carbon::parse($end)->startOfDay() : now()->startOfDay();

        return $to->lessThan($from) ? 0 : (int) $from->diffInDays($to);
    }
}
