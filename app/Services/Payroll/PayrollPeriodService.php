<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use Carbon\Carbon;

class PayrollPeriodService
{
    /**
     * Return associative period array.
     *
     * Useful if another part of the system expects:
     * $period['start']
     * $period['end']
     */
    public function getPeriod(int $month, int $year, string $cutoffType): array
    {
        [$startDate, $endDate] = $this->resolveCutoffRange($month, $year, $cutoffType);

        return [
            'start' => $startDate,
            'end' => $endDate,
        ];
    }

    /**
     * Return payroll cutoff range.
     *
     * Legacy internal mapping (do not rename without a data migration):
     * - `first`  = 11 to 25 = BUSINESS 2ND CUTOFF
     * - `second` = 26 to 10 next month = BUSINESS 1ST CUTOFF
     */
    public function resolveCutoffRange(int $month, int $year, string $cutoffType): array
    {
        /*
         * The selected month is now the PAYROLL CYCLE MONTH for both cutoffs.
         *
         * Example when July 2026 is selected:
         * - business 1st cutoff (`second`) = June 26 - July 10
         * - business 2nd cutoff (`first`)  = July 11 - July 25
         *
         * Legacy internal keys stay unchanged to protect existing database/API
         * compatibility; only the meaning of cutoff_month/cutoff_year selection
         * is standardized to the cycle/contribution month.
         */
        $cycleMonth = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila');

        if ($cutoffType === 'first') {
            return [
                $cycleMonth->copy()->day(11)->startOfDay(),
                $cycleMonth->copy()->day(25)->endOfDay(),
            ];
        }

        return [
            $cycleMonth->copy()->subMonthNoOverflow()->day(26)->startOfDay(),
            $cycleMonth->copy()->day(10)->endOfDay(),
        ];
    }

    /**
     * Resolve the payroll cutoff that contains a specific calendar date.
     *
     * Business labels:
     * - 26-10 = 1st cutoff (legacy key: second)
     * - 11-25 = 2nd cutoff (legacy key: first)
     */
    public function cutoffContainingDate(string|Carbon $date): array
    {
        $date = $date instanceof Carbon
            ? $date->copy()->timezone('Asia/Manila')
            : Carbon::parse($date, 'Asia/Manila');

        $day = (int) $date->day;

        if ($day >= 11 && $day <= 25) {
            [$start, $end] = $this->resolveCutoffRange((int) $date->month, (int) $date->year, 'first');

            return [
                'month' => (int) $date->month,
                'year' => (int) $date->year,
                'type' => 'first',
                'start' => $start,
                'end' => $end,
            ];
        }

        if ($day >= 26) {
            $nextCycleMonth = $date->copy()->addMonthNoOverflow();
            [$start, $end] = $this->resolveCutoffRange(
                (int) $nextCycleMonth->month,
                (int) $nextCycleMonth->year,
                'second'
            );

            return [
                'month' => (int) $nextCycleMonth->month,
                'year' => (int) $nextCycleMonth->year,
                'type' => 'second',
                'start' => $start,
                'end' => $end,
            ];
        }

        [$start, $end] = $this->resolveCutoffRange((int) $date->month, (int) $date->year, 'second');

        return [
            'month' => (int) $date->month,
            'year' => (int) $date->year,
            'type' => 'second',
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * Return the cutoff immediately after the cutoff containing the given date.
     * Used by Offset adjustments because company policy pays transferred time in
     * the NEXT payroll, never in the cutoff where the offset target date falls.
     */
    public function nextCutoffAfterDate(string|Carbon $date): array
    {
        $current = $this->cutoffContainingDate($date);
        $nextDate = $current['end']->copy()->addDay()->startOfDay();

        return $this->cutoffContainingDate($nextDate);
    }

    /**
     * Government contribution month.
     *
     * Company display rule:
     * Jan 26 - Feb 10 = BUSINESS 1ST CUTOFF (legacy key: `second`)
     * Feb 11 - Feb 25 = BUSINESS 2ND CUTOFF (legacy key: `first`)
     * Both belong to the February contribution cycle.
     */
    public function contributionMonth(int $month, int $year, string $cutoffType): array
    {
        $cycleMonth = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila');

        return [
            'month' => (int) $cycleMonth->month,
            'year' => (int) $cycleMonth->year,
            'label' => $cycleMonth->format('F Y'),

            // Example for July contribution:
            // cycle_start = June 26
            // cycle_end = July 25
            'cycle_start' => $cycleMonth
                ->copy()
                ->subMonthNoOverflow()
                ->day(26)
                ->startOfDay(),

            'cycle_end' => $cycleMonth
                ->copy()
                ->day(25)
                ->endOfDay(),
        ];
    }

    /**
     * Legacy helper name retained for compatibility. Internally, `first` means
     * 11-25 (business 2nd cutoff) and it checks the prior `second` record, which
     * means 26-10 (business 1st cutoff), for the monthly contribution basis.
     */
    public function previousSecondCutoffForFirst(int $month, int $year): array
    {
        // Both business cutoffs now share the same selected cycle month/year.
        return [
            'month' => $month,
            'year' => $year,
            'type' => 'second',
        ];
    }

    /**
     * Default cutoff for create form.
     *
     * Returns numeric array because PayrollController uses:
     * [$month, $year, $type] = getDefaultCutoff();
     */
    public function getDefaultCutoff(): array
    {
        $today = now('Asia/Manila');

        if ((int) $today->day >= 11 && (int) $today->day <= 25) {
            return [
                (int) $today->month,
                (int) $today->year,
                'first',
            ];
        }

        if ((int) $today->day >= 26) {
            $nextCycleMonth = $today->copy()->addMonthNoOverflow();

            return [
                (int) $nextCycleMonth->month,
                (int) $nextCycleMonth->year,
                'second',
            ];
        }

        return [
            (int) $today->month,
            (int) $today->year,
            'second',
        ];
    }

    /**
     * Generate unique payroll number.
     *
     * Payroll-number suffixes are legacy internal identifiers and are unchanged.
     * Display names are resolved separately through config/payroll.php.
     */
    public function generatePayrollNumber(
        int $year,
        int $month,
        string $cutoffType,
        callable $existsCallback
    ): string {
        $prefix = $cutoffType === 'first' ? '1' : '2';

        $base = sprintf('PR-%04d%02d-%s', $year, $month, $prefix);

        if (! $existsCallback($base)) {
            return $base;
        }

        $counter = 2;

        do {
            $number = $base.'-'.$counter;
            $counter++;
        } while ($existsCallback($number));

        return $number;
    }
}
