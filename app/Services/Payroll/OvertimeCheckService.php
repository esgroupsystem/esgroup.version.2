<?php

declare(strict_types=1);

namespace App\Support\Payroll;

use App\Models\PayrollAttendanceAdjustment;
use App\Services\Payroll\BiometricsProofService;
use Carbon\Carbon;

/**
 * Checks an Overtime filing against reality before it can be saved:
 *   - the OT window is at most 12 hours;
 *   - the employee's biometric logs on that day cover the whole OT window
 *     (first punch <= OT start, last punch >= OT end, 5-minute grace);
 *   - it does not overlap another pending/approved OT of the same employee.
 * Used by PayrollAttendanceAdjustmentRequest (blocks the save) and by the
 * form's live "OT check" panel.
 */
final class OvertimeCheck
{
    public const MAX_MINUTES = 12 * 60;

    public const GRACE_MINUTES = 5;

    public function __construct(private readonly BiometricsProofService $proofs) {}

    /**
     * @return array{ok: bool, errors: list<string>, minutes: int, punch_in: ?string, punch_out: ?string, window: ?string}
     */
    public function check(
        int $employeeBiometricId,
        ?string $biometricEmployeeId,
        ?string $employeeNo,
        string $employeeName,
        string $workDate,
        string $timeIn,
        string $timeOut,
        ?int $ignoreAdjustmentId = null,
    ): array {
        $errors = [];
        $date = Carbon::parse($workDate, 'Asia/Manila')->startOfDay();
        [$start, $end] = self::interval($date, $timeIn, $timeOut);
        $minutes = (int) $start->diffInMinutes($end);
        $window = $start->format('h:i A').' – '.$end->format('h:i A');

        if ($minutes > self::MAX_MINUTES) {
            $errors[] = sprintf('Overtime of %.2f hours is too long. One OT filing can cover at most %d hours.', $minutes / 60, self::MAX_MINUTES / 60);
        }

        // Biometric proof: the employee must have been in (logged) for the whole OT window.
        $proof = $this->proofs->findOffsetProof($employeeBiometricId, $biometricEmployeeId, $employeeNo, $employeeName, $date->toDateString());
        $punchIn = $proof ? $date->copy()->setTimeFromTimeString($proof['time_in']) : null;
        $punchOut = $proof ? $date->copy()->setTimeFromTimeString($proof['time_out']) : null;

        // OT past midnight: the time out is the first punch of the next morning.
        if ($punchOut && $end->greaterThan($date->copy()->endOfDay())) {
            $next = $this->proofs->findOffsetProof($employeeBiometricId, $biometricEmployeeId, $employeeNo, $employeeName, $date->copy()->addDay()->toDateString());
            if ($next && $next['time_in'] < '12:00') {
                $punchOut = $date->copy()->addDay()->setTimeFromTimeString($next['time_in']);
            }
        }

        if (! $proof) {
            $errors[] = 'No biometric logs were found for this employee on '.$date->format('M d, Y').'. Overtime needs time in / time out proof from biometrics.';
        } else {
            if ($start->copy()->addMinutes(self::GRACE_MINUTES)->lessThan($punchIn)) {
                $errors[] = 'OT starts at '.$start->format('h:i A').', but the first biometric punch that day is '.$punchIn->format('h:i A').'.';
            }
            if ($end->copy()->subMinutes(self::GRACE_MINUTES)->greaterThan($punchOut)) {
                $errors[] = 'OT ends at '.$end->format('h:i A').', but the last biometric punch is '.$punchOut->format('h:i A').'. File only the hours actually logged.';
            }
        }

        // No double filing of the same hours.
        $others = PayrollAttendanceAdjustment::query()
            ->where('adjustment_type', PayrollAttendanceAdjustment::TYPE_OVERTIME)
            ->where('employee_biometric_id', $employeeBiometricId)
            ->whereDate('work_date', $date->toDateString())
            ->whereIn('status', [PayrollAttendanceAdjustment::STATUS_PENDING, PayrollAttendanceAdjustment::STATUS_APPROVED])
            ->when($ignoreAdjustmentId, fn ($query) => $query->whereKeyNot($ignoreAdjustmentId))
            ->get(['id', 'adjusted_time_in', 'adjusted_time_out', 'status']);

        foreach ($others as $other) {
            if (! $other->adjusted_time_in || ! $other->adjusted_time_out) {
                continue;
            }
            [$otherStart, $otherEnd] = self::interval($date, (string) $other->adjusted_time_in, (string) $other->adjusted_time_out);
            if ($start->lessThan($otherEnd) && $otherStart->lessThan($end)) {
                $errors[] = sprintf(
                    'This overlaps another %s OT filing for the same day (%s – %s).',
                    $other->status === PayrollAttendanceAdjustment::STATUS_APPROVED ? 'approved' : 'pending',
                    $otherStart->format('h:i A'),
                    $otherEnd->format('h:i A'),
                );
            }
        }

        return [
            'ok' => $errors === [],
            'errors' => $errors,
            'minutes' => $minutes,
            'punch_in' => $punchIn?->format('h:i A'),
            'punch_out' => $punchOut?->format($punchOut->isSameDay($date) ? 'h:i A' : 'M d h:i A'),
            'window' => $window,
        ];
    }

    /** @return array{0: Carbon, 1: Carbon} OT start/end; an end at or before the start is the next day. */
    public static function interval(Carbon $date, string $timeIn, string $timeOut): array
    {
        $start = $date->copy()->setTimeFromTimeString(substr($timeIn, 0, 5));
        $end = $date->copy()->setTimeFromTimeString(substr($timeOut, 0, 5));

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return [$start, $end];
    }
}
