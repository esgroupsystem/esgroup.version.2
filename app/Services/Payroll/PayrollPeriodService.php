<?php

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
     * 1st cutoff: 11 to 25
     * 2nd cutoff: 26 to 10 next month
     */
    public function resolveCutoffRange(int $month, int $year, string $cutoffType): array
    {
        $baseDate = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila');

        if ($cutoffType === 'first') {
            return [
                $baseDate->copy()->day(11)->startOfDay(),
                $baseDate->copy()->day(25)->endOfDay(),
            ];
        }

        return [
            $baseDate->copy()->day(26)->startOfDay(),
            $baseDate->copy()->addMonthNoOverflow()->day(10)->endOfDay(),
        ];
    }

    /**
     * Government contribution month.
     *
     * Company rule:
     * Jan 26 - Feb 10 = 2nd cutoff
     * Feb 11 - Feb 25 = 1st cutoff
     * Both belong to February contribution cycle.
     */
    public function contributionMonth(int $month, int $year, string $cutoffType): array
    {
        [$startDate, $endDate] = $this->resolveCutoffRange($month, $year, $cutoffType);

        return [
            'month' => (int) $endDate->month,
            'year' => (int) $endDate->year,
            'label' => $endDate->format('F Y'),

            // Example for February contribution:
            // cycle_start = January 26
            // cycle_end = February 25
            'cycle_start' => $endDate
                ->copy()
                ->day(1)
                ->subMonthNoOverflow()
                ->day(26)
                ->startOfDay(),

            'cycle_end' => $endDate
                ->copy()
                ->day(25)
                ->endOfDay(),
        ];
    }

    /**
     * Used when 1st cutoff needs to check the previous 2nd cutoff
     * for whole-month government contribution basis.
     */
    public function previousSecondCutoffForFirst(int $month, int $year): array
    {
        $previousMonth = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila')
            ->subMonthNoOverflow();

        return [
            'month' => (int) $previousMonth->month,
            'year' => (int) $previousMonth->year,
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
            return [
                (int) $today->month,
                (int) $today->year,
                'second',
            ];
        }

        $previousMonth = $today->copy()->subMonthNoOverflow();

        return [
            (int) $previousMonth->month,
            (int) $previousMonth->year,
            'second',
        ];
    }

    /**
     * Generate unique payroll number.
     *
     * Example:
     * PR-202602-1 = first cutoff
     * PR-202601-2 = second cutoff
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
