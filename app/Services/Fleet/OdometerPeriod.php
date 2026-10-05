<?php

declare(strict_types=1);

namespace App\Services\Fleet;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Throwable;

/**
 * The period picked on Odometer Monitoring: one day, a date range or a month (default: this month).
 * Unreadable dates fall back to the defaults instead of failing with a 500.
 */
final readonly class OdometerPeriod
{
    public function __construct(
        public string $type,
        public string $month,
        public string $date,
        public string $dateFrom,
        public string $dateTo,
        public CarbonInterface $start,
        public CarbonInterface $end,
        public string $label,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $type = (string) $request->input('filter_type', 'month');
        $month = self::valid((string) $request->input('month'), 'Y-m', now()->format('Y-m'));
        $date = self::valid((string) $request->input('date'), 'Y-m-d', now()->toDateString());
        $dateFrom = self::valid((string) $request->input('date_from'), 'Y-m-d', now()->startOfMonth()->toDateString());
        $dateTo = self::valid((string) $request->input('date_to'), 'Y-m-d', now()->endOfMonth()->toDateString());

        if ($type === 'day') {
            $start = Carbon::parse($date)->startOfDay();

            return new self($type, $month, $date, $dateFrom, $dateTo, $start, $start->copy()->endOfDay(), $start->format('F d, Y'));
        }

        if ($type === 'range') {
            $start = Carbon::parse($dateFrom)->startOfDay();
            $end = Carbon::parse($dateTo)->endOfDay();

            return new self($type, $month, $date, $dateFrom, $dateTo, $start, $end, $start->format('M d, Y').' - '.$end->format('M d, Y'));
        }

        $first = Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfMonth();

        return new self($type, $month, $date, $dateFrom, $dateTo, $first, $first->copy()->endOfMonth(), $first->format('F Y'));
    }

    /** `month` for anything that is not day / range, as the page shows it. */
    public function normalizedType(): string
    {
        return in_array($this->type, ['day', 'range'], true) ? $this->type : 'month';
    }

    public function from(): string
    {
        return $this->start->toDateString();
    }

    public function to(): string
    {
        return $this->end->toDateString();
    }

    private static function valid(string $value, string $format, string $default): string
    {
        if ($value === '') {
            return $default;
        }

        try {
            $parsed = Carbon::createFromFormat($format === 'Y-m' ? 'Y-m-d' : $format, $format === 'Y-m' ? $value.'-01' : $value);

            return $parsed !== null && $parsed->format($format) === $value ? $value : $default;
        } catch (Throwable) {
            return $default;
        }
    }
}
