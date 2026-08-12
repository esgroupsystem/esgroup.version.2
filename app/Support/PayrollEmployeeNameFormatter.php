<?php

namespace App\Support;

class PayrollEmployeeNameFormatter
{
    /**
     * Display employee names surname-first inside Payroll and Biometrics only.
     * Stored/source names are intentionally left untouched so biometric identity
     * matching and historical payroll references remain stable.
     *
     * Example: "Lenberd Arazo Ilaw" => "Ilaw, Lenberd Arazo".
     */
    public static function display(?string $name): string
    {
        $name = self::normalize($name);

        if ($name === '') {
            return 'Unknown Employee';
        }

        // Respect records that are already encoded surname-first.
        if (str_contains($name, ',')) {
            [$surname, $given] = array_pad(array_map('trim', explode(',', $name, 2)), 2, '');

            return trim($surname.($given !== '' ? ', '.$given : ''));
        }

        $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($parts) <= 1) {
            return $name;
        }

        $suffix = '';
        $lastPart = (string) end($parts);
        $suffixKey = strtolower(rtrim($lastPart, '.'));

        if (in_array($suffixKey, ['jr', 'sr', 'ii', 'iii', 'iv', 'v'], true) && count($parts) >= 3) {
            $suffix = array_pop($parts);
        }

        $surname = (string) array_pop($parts);
        $givenMiddle = trim(implode(' ', $parts));
        $surnameWithSuffix = trim($surname.($suffix !== '' ? ' '.$suffix : ''));

        return $givenMiddle !== ''
            ? $surnameWithSuffix.', '.$givenMiddle
            : $surnameWithSuffix;
    }

    public static function normalize(?string $name): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', trim((string) $name)));
    }
}
