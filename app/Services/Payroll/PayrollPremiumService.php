<?php

namespace App\Services\Payroll;

use Carbon\Carbon;

class PayrollPremiumService
{
    public function standardDailyHours(): float
    {
        return max(1.0, (float) config('payroll.premiums.standard_daily_hours', 8));
    }

    public function baseHourlyRate(float $dailyRate): float
    {
        return max(0, $dailyRate) / $this->standardDailyHours();
    }

    /**
     * Statutory minimum overtime rate for an approved OT interval.
     * Ordinary day = 125%; premium/rest/holiday day = day rate x 130%.
     */
    public function overtimeHourlyRate(float $dailyRate, float $dayMultiplier = 1.0): float
    {
        $dayMultiplier = max(1.0, $dayMultiplier);
        $otPremium = $dayMultiplier > 1.0
            ? (float) config('payroll.premiums.premium_day_overtime_multiplier', 1.30)
            : (float) config('payroll.premiums.overtime_multiplier', 1.25);

        return round($this->baseHourlyRate($dailyRate) * $dayMultiplier * max(1.0, $otPremium), 6);
    }

    public function nightDifferentialPercent(): float
    {
        return max(0.0, (float) config('payroll.premiums.night_differential_percent', 0.10));
    }

    /**
     * Night differential is at least 10% of the applicable hourly wage.
     */
    public function nightDifferentialHourlyRate(float $dailyRate, float $dayMultiplier = 1.0): float
    {
        return round(
            $this->baseHourlyRate($dailyRate)
            * max(1.0, $dayMultiplier)
            * $this->nightDifferentialPercent(),
            6
        );
    }

    /**
     * When the same hour is approved overtime AND falls inside 10PM-6AM,
     * the 10% night differential is applied to the applicable OT hourly rate.
     */
    public function nightDifferentialOnOvertimeHourlyRate(float $approvedOvertimeHourlyRate): float
    {
        return round(
            max(0.0, $approvedOvertimeHourlyRate) * $this->nightDifferentialPercent(),
            6
        );
    }

    public function interval(string|Carbon $workDate, ?string $timeIn, ?string $timeOut): ?array
    {
        if (! $timeIn || ! $timeOut) {
            return null;
        }

        $date = $workDate instanceof Carbon
            ? $workDate->copy()->startOfDay()
            : Carbon::parse($workDate, 'Asia/Manila')->startOfDay();

        $in = Carbon::parse($date->toDateString().' '.$timeIn, 'Asia/Manila');
        $out = Carbon::parse($date->toDateString().' '.$timeOut, 'Asia/Manila');

        if ($out->lessThanOrEqualTo($in)) {
            $out->addDay();
        }

        return [
            'start' => $in,
            'end' => $out,
            'minutes' => max(0, (int) $in->diffInMinutes($out)),
        ];
    }

    public function nightDifferentialMinutes(Carbon $actualIn, Carbon $actualOut): int
    {
        if ($actualOut->lessThanOrEqualTo($actualIn)) {
            return 0;
        }

        $minutes = 0;
        $cursor = $actualIn->copy()->startOfDay()->subDay();
        $lastWindowDate = $actualOut->copy()->startOfDay();
        $nightStart = trim((string) config('payroll.premiums.night_start', '22:00')) ?: '22:00';
        $nightEnd = trim((string) config('payroll.premiums.night_end', '06:00')) ?: '06:00';

        while ($cursor->lessThanOrEqualTo($lastWindowDate)) {
            $windowStart = Carbon::parse($cursor->toDateString().' '.$nightStart, 'Asia/Manila');
            $windowEnd = Carbon::parse($cursor->toDateString().' '.$nightEnd, 'Asia/Manila');

            if ($windowEnd->lessThanOrEqualTo($windowStart)) {
                $windowEnd->addDay();
            }

            $overlapStart = $actualIn->greaterThan($windowStart) ? $actualIn->copy() : $windowStart;
            $overlapEnd = $actualOut->lessThan($windowEnd) ? $actualOut->copy() : $windowEnd;

            if ($overlapEnd->greaterThan($overlapStart)) {
                $minutes += (int) $overlapStart->diffInMinutes($overlapEnd);
            }

            $cursor->addDay();
        }

        return max(0, $minutes);
    }

    public function nightDifferentialOverlapMinutes(
        Carbon $actualIn,
        Carbon $actualOut,
        Carbon $intervalStart,
        Carbon $intervalEnd
    ): int {
        if ($actualOut->lessThanOrEqualTo($actualIn) || $intervalEnd->lessThanOrEqualTo($intervalStart)) {
            return 0;
        }

        $overlapStart = $actualIn->greaterThan($intervalStart)
            ? $actualIn->copy()
            : $intervalStart->copy();
        $overlapEnd = $actualOut->lessThan($intervalEnd)
            ? $actualOut->copy()
            : $intervalEnd->copy();

        if ($overlapEnd->lessThanOrEqualTo($overlapStart)) {
            return 0;
        }

        return $this->nightDifferentialMinutes($overlapStart, $overlapEnd);
    }


    /**
     * Company Offset / compensatory-leave credit evidence.
     *
     * Only time beyond the employee's required clock span can qualify for the
     * company credit, so ordinary paid working hours are never duplicated.
     * Separately approved overtime remains independently payable; this helper
     * only measures the source excess used to support the company benefit.
     * Example: 8 paid hours + 1 hour lunch = 540 required clock minutes. A
     * 10-hour biometric span creates at most 60 minutes of Offset credit.
     */
    public function offsetCreditMinutes(
        string|Carbon $proofDate,
        ?string $timeIn,
        ?string $timeOut,
        int $requiredClockMinutes
    ): int {
        $interval = $this->interval($proofDate, $timeIn, $timeOut);

        if (! $interval) {
            return 0;
        }

        return max(0, (int) $interval['minutes'] - max(0, $requiredClockMinutes));
    }

    public function payableOffsetMinutes(
        string|Carbon $proofDate,
        ?string $timeIn,
        ?string $timeOut,
        int $paidMinutesCap,
        int $lunchMinutes = 60
    ): int {
        $interval = $this->interval($proofDate, $timeIn, $timeOut);

        if (! $interval) {
            return 0;
        }

        $workedMinutes = (int) $interval['minutes'];

        if ($lunchMinutes > 0) {
            $date = $proofDate instanceof Carbon
                ? $proofDate->copy()->startOfDay()
                : Carbon::parse($proofDate, 'Asia/Manila')->startOfDay();

            $lunchStart = $date->copy()->setTime(12, 0);
            $lunchEnd = $lunchStart->copy()->addMinutes($lunchMinutes);
            $overlapStart = $interval['start']->greaterThan($lunchStart) ? $interval['start'] : $lunchStart;
            $overlapEnd = $interval['end']->lessThan($lunchEnd) ? $interval['end'] : $lunchEnd;

            if ($overlapEnd->greaterThan($overlapStart)) {
                $workedMinutes -= (int) $overlapStart->diffInMinutes($overlapEnd);
            }
        }

        return min(max(0, $workedMinutes), max(0, $paidMinutesCap));
    }
}
