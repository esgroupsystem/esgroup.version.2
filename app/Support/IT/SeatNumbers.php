<?php

declare(strict_types=1);

namespace App\Support\IT;

use Closure;

/**
 * Seat numbers on a ticket job order. The bus seat map lets IT pick several
 * seats, stored in the existing varchar column as a sorted list: "12, 13, 20".
 * A single seat ("12") stays exactly as before.
 */
final class SeatNumbers
{
    /** Highest seat number accepted (the seat map has 1-52; older records allowed up to 60). */
    public const MAX = 60;

    /**
     * "13,12 , 20,12" -> "12, 13, 20". Input with other characters is returned
     * unchanged so validation can reject it with a clear message.
     */
    public static function normalize(mixed $value): mixed
    {
        if ($value === null || is_int($value)) {
            return $value === null ? null : (string) $value;
        }

        if (! is_string($value)) {
            return $value;
        }

        if (trim($value) === '') {
            return null;
        }

        if (preg_match('/^[\d,\s]+$/', $value) !== 1) {
            return $value;
        }

        $numbers = array_values(array_unique(array_map('intval', preg_split('/[\s,]+/', trim($value, " ,\t\n")) ?: [])));
        sort($numbers);

        return implode(', ', $numbers);
    }

    /** @return list<mixed> */
    public static function rules(): array
    {
        return [
            'nullable',
            'string',
            'max:191',
            'regex:/^\d{1,2}(, \d{1,2})*$/',
            static function (string $attribute, mixed $value, Closure $fail): void {
                foreach (explode(', ', (string) $value) as $seat) {
                    if ((int) $seat < 1 || (int) $seat > self::MAX) {
                        $fail('Seat numbers must be between 1 and '.self::MAX.'.');

                        return;
                    }
                }
            },
        ];
    }
}
