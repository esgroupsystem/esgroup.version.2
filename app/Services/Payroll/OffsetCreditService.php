<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Models\PayrollAttendanceAdjustment;
use App\Repositories\Contracts\Payroll\AttendanceAdjustmentRepositoryInterface;
use App\Repositories\Contracts\Payroll\AttendanceSummaryRepositoryInterface;
use App\Repositories\Contracts\Scheduling\PlottingScheduleRepositoryInterface;
use Carbon\Carbon;
use DateTimeInterface;

/**
 * Offset (company compensatory time): excess work time from one or more earlier source dates
 * covers the attendance shortage of a later target date. Every source needs biometric proof and
 * enough unallocated excess; the pooled total may not exceed the target's shortage.
 */
final class OffsetCreditService
{
    public function __construct(
        private readonly AttendanceAdjustmentRepositoryInterface $adjustments,
        private readonly AttendanceSummaryRepositoryInterface $summaries,
        private readonly PlottingScheduleRepositoryInterface $schedules,
        private readonly BiometricsProofService $proofs,
        private readonly PayrollPremiumService $premiums,
    ) {}

    /**
     * @param  array<int, array{date: string, hours?: mixed, minutes?: mixed}>  $sourceRows
     * @return array{
     *     error: array{field: string, message: string, status?: int}|null,
     *     sources: array<int, array{date: string, requested_minutes: int, available_minutes: int, proof: ?array, error: ?string}>,
     *     requested_minutes: int,
     *     available_minutes: int,
     *     target_capacity_minutes: ?int
     * }
     */
    public function evaluate(int $employeeBiometricId, ?string $biometricEmployeeId, ?string $employeeNo, string $employeeName, string $targetDate, array $sourceRows, ?int $ignoreAdjustmentId = null): array
    {
        $result = ['error' => null, 'sources' => [], 'requested_minutes' => 0, 'available_minutes' => 0, 'target_capacity_minutes' => null];
        $fail = function (string $field, string $message, int $status = 422) use (&$result): void {
            $result['error'] ??= ['field' => $field, 'message' => $message, 'status' => $status];
        };

        $target = Carbon::parse($targetDate, 'Asia/Manila')->startOfDay();
        $targetSchedule = $this->schedules->scheduleOn($employeeBiometricId, $target->toDateString());

        if (! $targetSchedule) {
            $fail('work_date', 'Offset target date has no plotted work schedule. Plot the employee schedule first before applying compensatory time.');

            return $result;
        }
        if ($targetSchedule->isDayOffOn($target)) {
            $fail('work_date', "Offset cannot be targeted to the employee's weekly day off. Select a scheduled working date with an attendance shortage.");

            return $result;
        }

        $rows = collect($sourceRows)
            ->filter(fn (mixed $row): bool => is_array($row) && filled($row['date'] ?? null))
            ->map(fn (array $row): array => [
                'date' => Carbon::parse($row['date'], 'Asia/Manila')->toDateString(),
                'minutes' => array_key_exists('minutes', $row) ? max(0, (int) $row['minutes']) : max(0, (int) round(((float) ($row['hours'] ?? 0)) * 60)),
            ])
            ->sortBy('date')
            ->values();

        if ($rows->isEmpty()) {
            $fail('offset_sources', 'Please add at least one earlier source date containing excess work time for this Offset.');

            return $result;
        }
        if ($rows->pluck('date')->duplicates()->isNotEmpty()) {
            $fail('offset_sources', 'The same Offset source date is listed more than once.');

            return $result;
        }

        foreach ($rows as $row) {
            $entry = $this->evaluateSource($employeeBiometricId, $biometricEmployeeId, $employeeNo, $employeeName, $target, $row['date'], $row['minutes'], $ignoreAdjustmentId);
            if ($entry['error'] !== null) {
                $fail('offset_sources', $entry['error'], $entry['status']);
            }
            unset($entry['status']);

            $result['sources'][] = $entry;
            $result['requested_minutes'] += $entry['requested_minutes'];
            $result['available_minutes'] += $entry['available_minutes'];
        }

        $capacity = $this->targetCapacityMinutes($employeeBiometricId, $target->toDateString(), $ignoreAdjustmentId);
        $result['target_capacity_minutes'] = $capacity;

        if ($capacity !== null && $capacity <= 0) {
            $fail('work_date', 'The selected Offset target date already has a complete payable day with no attendance shortage to cover.');
        } elseif ($capacity !== null && $result['requested_minutes'] > $capacity) {
            $fail('offset_sources', sprintf(
                'The target date needs only %.2f hour(s) of Offset credit, but %.2f hour(s) were requested in total. Reduce the hours to avoid over-allocation.',
                $capacity / 60,
                $result['requested_minutes'] / 60,
            ));
        }

        return $result;
    }

    /**
     * The form's "Check available offset credit" answer and its HTTP status (200, 404 or 422).
     *
     * @param  array<string, mixed>  $data  validated offset-proof request
     * @return array{0: array<string, mixed>, 1: int}
     */
    public function report(array $data): array
    {
        $sources = ! empty($data['offset_sources'])
            ? $data['offset_sources']
            : [['date' => $data['offset_source_date'], 'hours' => $data['offset_hours']]];

        $result = $this->evaluate(
            (int) $data['employee_biometric_id'],
            $data['biometric_employee_id'] ?? null,
            $data['employee_no'] ?? null,
            $data['employee_name'],
            $data['work_date'],
            $sources,
            isset($data['adjustment_id']) ? (int) $data['adjustment_id'] : null,
        );

        $hours = fn (?int $minutes): ?float => $minutes === null ? null : round($minutes / 60, 2);
        $firstProof = $result['sources'][0]['proof'] ?? null;
        $proof = is_array($firstProof) ? $firstProof : [];
        $proof['requested_minutes'] = $result['requested_minutes'];
        $proof['requested_hours'] = $hours($result['requested_minutes']);
        $proof['available_minutes'] = $result['available_minutes'];
        $proof['available_hours'] = $hours($result['available_minutes']);
        $proof['approved_minutes'] = $result['available_minutes']; // older UI key
        $proof['approved_hours'] = $proof['available_hours'];
        $proof['target_capacity_minutes'] = $result['target_capacity_minutes'];
        $proof['target_capacity_hours'] = $hours($result['target_capacity_minutes']);
        $proof['target_date'] = Carbon::parse($data['work_date'], 'Asia/Manila')->toDateString();

        $payload = [
            'found' => $result['error'] === null,
            'message' => $result['error']['message'] ?? (count($result['sources']) > 1
                ? sprintf('Offset is valid for review. %d source dates provide %.2f hour(s) of credit for the target date.', count($result['sources']), $result['requested_minutes'] / 60)
                : 'Offset is valid for review. Source excess, requested hours, and target attendance capacity are within the allowed limits.'),
            'proof' => $proof,
            'sources' => array_map(fn (array $source): array => [
                'date' => $source['date'],
                'requested_minutes' => $source['requested_minutes'],
                'requested_hours' => $hours($source['requested_minutes']),
                'available_minutes' => $source['available_minutes'],
                'available_hours' => $hours($source['available_minutes']),
                'time_in' => $source['proof']['time_in'] ?? null,
                'time_out' => $source['proof']['time_out'] ?? null,
                'has_proof' => $source['proof'] !== null,
                'error' => $source['error'],
            ], $result['sources']),
        ];

        return [$payload, $result['error'] === null ? 200 : ($result['error']['status'] ?? 422)];
    }

    /** Why a stored Offset cannot be approved now, or null when it can. */
    public function approvalProblem(PayrollAttendanceAdjustment $offset): ?string
    {
        $employeeBiometricId = (int) ($offset->employee_biometric_id ?? 0);
        $targetDate = $this->date($offset->work_date);
        $sources = $offset->resolvedOffsetSources();

        if ($employeeBiometricId <= 0 || $sources === [] || ! $targetDate) {
            return 'Offset cannot be approved because the employee, source date, or target date is incomplete. Edit and revalidate the Offset request first.';
        }
        if ((int) ($offset->approved_minutes ?? 0) <= 0 || collect($sources)->sum('minutes') <= 0) {
            return 'Offset cannot be approved because no compensatory hours are stored. Edit the request, enter the hours to transfer, and run Check Available Offset Credit again.';
        }

        $result = $this->evaluate($employeeBiometricId, $offset->biometric_employee_id, $offset->employee_no, (string) $offset->employee_name, $targetDate, $sources, (int) $offset->id);

        return $result['error'] === null ? null : 'Offset cannot be approved. '.$result['error']['message'].' Edit/revalidate the request first.';
    }

    /**
     * The proof columns saved with a valid Offset (earliest source first).
     *
     * @param  array{date: string, requested_minutes: int, proof: ?array}[]  $sources  evaluate()['sources']
     * @return array<string, mixed>
     */
    public function proofAttributes(array $sources, int $requestedMinutes): array
    {
        $earliest = $sources[0];

        return [
            'offset_source_date' => $earliest['date'],
            'offset_source_time_in' => $earliest['proof']['time_in'] ?? null,
            'offset_source_time_out' => $earliest['proof']['time_out'] ?? null,
            'offset_source_logs' => $earliest['proof']['logs'] ?? null,
            'offset_sources' => array_map(fn (array $source): array => [
                'date' => $source['date'],
                'minutes' => $source['requested_minutes'],
                'time_in' => $source['proof']['time_in'] ?? null,
                'time_out' => $source['proof']['time_out'] ?? null,
            ], $sources),
            'approved_minutes' => $requestedMinutes,
        ];
    }

    /** @return array{date: string, requested_minutes: int, available_minutes: int, proof: ?array, error: ?string, status: int} */
    private function evaluateSource(int $employeeBiometricId, ?string $biometricEmployeeId, ?string $employeeNo, string $employeeName, Carbon $target, string $sourceDate, int $requestedMinutes, ?int $ignoreAdjustmentId): array
    {
        $label = Carbon::parse($sourceDate, 'Asia/Manila')->format('M d, Y');
        $entry = ['date' => $sourceDate, 'requested_minutes' => $requestedMinutes, 'available_minutes' => 0, 'proof' => null, 'error' => null, 'status' => 422];

        if ($requestedMinutes <= 0) {
            return ['error' => "Enter the hours to transfer from {$label}."] + $entry;
        }
        if (Carbon::parse($sourceDate, 'Asia/Manila')->greaterThanOrEqualTo($target)) {
            return ['error' => "Offset source date {$label} must be earlier than the target attendance date."] + $entry;
        }

        $proof = $this->proofs->findOffsetProof($employeeBiometricId, $biometricEmployeeId, $employeeNo, $employeeName, $sourceDate);
        $entry['proof'] = $proof;
        if (! $proof) {
            return ['error' => "No biometric logs found for the selected employee on {$label}.", 'status' => 404] + $entry;
        }

        $available = $this->availableMinutes($employeeBiometricId, $sourceDate, $proof['time_in'] ?? null, $proof['time_out'] ?? null, $ignoreAdjustmentId);
        $entry['available_minutes'] = $available;

        if ($available <= 0) {
            $entry['error'] = "{$label} has biometric proof but no unused excess work time. Only time beyond the required shift can be transferred, and minutes already allocated to another Offset request are excluded.";
        } elseif ($requestedMinutes > $available) {
            $entry['error'] = sprintf('Requested %.2f hour(s) from %s, but only %.2f unused excess hour(s) are available on that date.', $requestedMinutes / 60, $label, $available / 60);
        }

        return $entry;
    }

    /**
     * Excess minutes on a source date not yet given to another Offset. Offset never replaces
     * statutory OT pay: the same excess may also be paid as approved OT.
     */
    private function availableMinutes(int $employeeBiometricId, string $date, ?string $timeIn, ?string $timeOut, ?int $ignoreAdjustmentId): int
    {
        $summary = $this->summaries->forEmployeeOn($employeeBiometricId, $date);
        $requiredClockMinutes = $this->schedules->scheduleOn($employeeBiometricId, $date)?->requiredClockMinutes() ?? 0;

        $excess = $summary
            ? max(0, (int) ($summary->overtime_minutes ?? 0))
            : ($requiredClockMinutes > 0 ? $this->premiums->offsetCreditMinutes($date, $timeIn, $timeOut, $requiredClockMinutes) : 0);

        if ($excess <= 0) {
            return 0;
        }

        $allocated = (int) $this->adjustments->offsetsUsingSourceDate($employeeBiometricId, $date, $ignoreAdjustmentId)
            ->sum(fn (PayrollAttendanceAdjustment $offset): int => (int) collect($offset->resolvedOffsetSources())->where('date', $date)->sum('minutes'));

        return max(0, $excess - $allocated);
    }

    /** Shortage on the target date (plus what this Offset already applied there); null when not built yet. */
    private function targetCapacityMinutes(int $employeeBiometricId, string $date, ?int $ignoreAdjustmentId): ?int
    {
        $summary = $this->summaries->forEmployeeOn($employeeBiometricId, $date);
        if (! $summary) {
            // Future / not-yet-built target: actual consumption is capped by the shortage later.
            return null;
        }

        $scheduledPaid = $this->schedules->scheduleOn($employeeBiometricId, $date)?->paidWorkMinutes() ?? 480;
        $paidPerDay = max(1, (int) data_get($summary->meta, 'paid_minutes_per_day', $scheduledPaid));
        $payable = min($paidPerDay, max(0, (int) round(((float) ($summary->payable_hours ?? 0)) * 60)));
        $own = $ignoreAdjustmentId && (int) data_get($summary->meta, 'offset_adjustment_id', 0) === $ignoreAdjustmentId
            ? max(0, (int) data_get($summary->meta, 'offset_applied_minutes', 0))
            : 0;

        return min($paidPerDay, max(0, $paidPerDay - $payable) + $own);
    }

    private function date(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value instanceof DateTimeInterface ? Carbon::instance($value)->toDateString() : Carbon::parse((string) $value, 'Asia/Manila')->toDateString();
    }
}
