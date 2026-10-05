<?php

declare(strict_types=1);

namespace App\Http\Resources\Concerns;

use Carbon\Carbon;
use Throwable;

trait FormatsDates
{
    /** Null when blank; the raw text when it is not a date. */
    protected function formatDate(mixed $value, string $pattern): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->format($pattern);
        } catch (Throwable) {
            return is_string($value) ? $value : null;
        }
    }
}
