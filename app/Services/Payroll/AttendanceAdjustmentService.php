<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Models\EmployeeBiometric;
use App\Models\PayrollAttendanceAdjustment as Adjustment;
use App\Models\PayrollItem;
use App\Repositories\Contracts\Biometrics\EmployeeBiometricRepositoryInterface;
use App\Repositories\Contracts\Payroll\AttendanceAdjustmentRepositoryInterface;
use App\Repositories\Contracts\Payroll\PayrollRepositoryInterface;
use App\Services\Biometrics\EmployeeBiometricIdentityService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Payroll → Adjustment: leave, offset, OT, schedule change, official business, holiday work,
 * typhoon / disaster and salary adjustments. Saving rebuilds the attendance summary of the
 * affected dates; OT and offsets need manager approval before they count.
 */
final class AttendanceAdjustmentService
{
    public const FILTERS = ['search', 'type', 'group_name', 'date_from', 'date_to'];

    private const DISK = 'local';

    public function __construct(
        private readonly AttendanceAdjustmentRepositoryInterface $adjustments,
        private readonly EmployeeBiometricRepositoryInterface $biometrics,
        private readonly PayrollRepositoryInterface $payrolls,
        private readonly EmployeeBiometricIdentityService $identity,
        private readonly OffsetCreditService $offsets,
        private readonly DailyAttendanceSummaryService $attendance,
        private readonly PayrollComputationService $computation,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array{search: string, type: string, group_name: string, date_from: string, date_to: string}
     */
    public function filters(array $input): array
    {
        $filters = [];
        foreach (self::FILTERS as $key) {
            $filters[$key] = trim((string) ($input[$key] ?? ''));
        }

        /** @var array{search: string, type: string, group_name: string, date_from: string, date_to: string} $filters */
        return $filters;
    }

    /**
     * @param  array{search: string, type: string, group_name: string, date_from: string, date_to: string}  $filters
     * @return LengthAwarePaginator<int, Adjustment>
     */
    public function paginate(array $filters, string $status): LengthAwarePaginator
    {
        return $this->adjustments->paginate($filters, $status);
    }

    /**
     * Count cards. The status cards count every status under the other filters.
     *
     * @param  array{search: string, type: string, group_name: string, date_from: string, date_to: string}  $filters
     * @return array<string, int>
     */
    public function stats(array $filters, string $status): array
    {
        $byStatus = $this->adjustments->countByStatus($filters);
        $count = fn (?array $types = null, ?string $only = null): int => $this->adjustments->count($filters, $status, $types, $only);

        return [
            'total' => $count(),
            'leaves' => $count([Adjustment::TYPE_SICK_LEAVE, Adjustment::TYPE_MEDICAL_LEAVE]),
            'offsets' => $count([Adjustment::TYPE_OFFSET]),
            'manual_time' => $count([Adjustment::TYPE_CHANGE_SCHEDULE, Adjustment::TYPE_OFFICIAL_BUSINESS, Adjustment::TYPE_HOLIDAY_WORK, Adjustment::TYPE_OVERTIME]),
            'disasters' => $count(Adjustment::TYPHOON_DISASTER_TYPES),
            'pending' => $count(null, Adjustment::STATUS_PENDING),
            'status_pending' => (int) ($byStatus[Adjustment::STATUS_PENDING] ?? 0),
            'status_approved' => (int) ($byStatus[Adjustment::STATUS_APPROVED] ?? 0),
            'status_rejected' => (int) ($byStatus[Adjustment::STATUS_REJECTED] ?? 0),
        ];
    }

    /** @return Collection<int, array<string, mixed>> payroll-active people for the form */
    public function people(): Collection
    {
        return $this->biometrics->payrollActive()->map(function (EmployeeBiometric $person): array {
            $snapshot = $this->identity->snapshot($person);

            return [
                'employee_biometric_id' => (int) $person->id,
                'biometric_employee_id' => $snapshot['biometric_employee_id'],
                'employee_no' => $snapshot['employee_no'],
                'employee_name' => $snapshot['employee_name'],
                'crosschex_id' => $snapshot['crosschex_id'],
                'group_name' => $person->group_name !== null ? (string) $person->group_name : null,
            ];
        })->values();
    }

    /**
     * Saves a new adjustment. With $payrollItemId (the "File adjustment" dialog on a payroll item),
     * the date must fall in that payroll's cutoff and the item is recomputed afterwards.
     *
     * @param  array<string, mixed>  $data  validated PayrollAttendanceAdjustmentRequest
     * @return array{adjustment: Adjustment, message: string, item: ?PayrollItem}
     *
     * @throws ValidationException
     */
    public function create(array $data, ?bool $isPaid, ?UploadedFile $otForm, int $payrollItemId, bool $canRecompute, ?int $userId): array
    {
        $this->assertNoDuplicate($data, null);

        $item = $payrollItemId > 0 ? $this->payrolls->findItemWithPayroll($payrollItemId) : null;
        if ($item?->payroll) {
            $this->assertInPayrollPeriod($data, $item);
        }

        // Check offset credit before storing the upload, so a refused save leaves no orphan file.
        $offset = $data['adjustment_type'] === Adjustment::TYPE_OFFSET ? $this->offsetAttributes($data, null) : [];
        $payload = array_merge($this->payload($data, $isPaid, null, $userId) + $this->storeOtForm($otForm, null), $offset);

        $adjustment = $this->adjustments->create($payload);
        $this->rebuildSummary($adjustment);
        $message = $this->successMessage($adjustment, 'saved');

        // Recompute only that employee's item in the draft; nobody else is touched.
        if ($item?->payroll && $canRecompute && (int) $item->employee_biometric_id === (int) $adjustment->employee_biometric_id) {
            try {
                $this->computation->recomputeItem($item->payroll, $item, $userId);
                $message .= ' '.$item->payroll_display_name.'\'s payroll computation was automatically recomputed. Other employees in this payroll were not affected.';
            } catch (Throwable $exception) {
                $message .= ' However, automatic payroll recompute failed: '.$exception->getMessage().' You can retry from the payroll item page.';
            }

            return ['adjustment' => $adjustment, 'message' => $message, 'item' => $item];
        }

        return ['adjustment' => $adjustment, 'message' => $message, 'item' => null];
    }

    /**
     * @param  array<string, mixed>  $data  validated PayrollAttendanceAdjustmentRequest
     * @return string the success message
     *
     * @throws ValidationException
     */
    public function update(Adjustment $adjustment, array $data, ?bool $isPaid, ?UploadedFile $otForm, ?int $userId): string
    {
        if ($adjustment->paid_payroll_id) {
            throw ValidationException::withMessages(['adjustment_type' => 'This adjustment is already linked to a generated payroll and can no longer be edited. Delete/regenerate the affected draft payroll first if a correction is required.']);
        }

        $this->assertNoDuplicate($data, $adjustment->id, 'Another Typhoon / Disaster adjustment already exists for this work date.');

        $oldRange = $this->dateRange($adjustment);
        $offset = $data['adjustment_type'] === Adjustment::TYPE_OFFSET
            ? $this->offsetAttributes($data, $adjustment->id) + ['paid_payroll_id' => null, 'paid_payroll_item_id' => null]
            : [];
        $payload = array_merge($this->payload($data, $isPaid, $adjustment, $userId) + $this->storeOtForm($otForm, $adjustment), $offset);

        $this->adjustments->update($adjustment, $payload);
        $adjustment->refresh();
        $this->rebuildDates($oldRange);
        $this->rebuildSummary($adjustment);

        return $this->successMessage($adjustment, 'updated');
    }

    /**
     * @return string the success message
     *
     * @throws ValidationException (key "approval")
     */
    public function approve(Adjustment $adjustment, ?int $userId): string
    {
        if (! $adjustment->isApprovalRequired()) {
            return 'This adjustment type does not require separate manager approval.';
        }
        if ($adjustment->paid_payroll_id) {
            throw ValidationException::withMessages(['approval' => 'This adjustment is already linked to a payroll and cannot be re-approved.']);
        }
        if ($adjustment->adjustment_type === Adjustment::TYPE_OFFSET && ($problem = $this->offsets->approvalProblem($adjustment)) !== null) {
            throw ValidationException::withMessages(['approval' => $problem]);
        }

        $this->adjustments->update($adjustment, [
            'status' => Adjustment::STATUS_APPROVED,
            'approved_by' => $userId,
            'approved_at' => now('Asia/Manila'),
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        $isOvertime = $adjustment->adjustment_type === Adjustment::TYPE_OVERTIME;
        if (! $isOvertime) {
            $this->rebuildSummary($adjustment);
        }

        return $isOvertime
            ? 'Overtime adjustment approved. It will now be included when the affected draft payroll is generated/regenerated.'
            : 'Offset adjustment approved. The compensatory-time credit has been applied to the target attendance date. Regenerate any affected draft payroll.';
    }

    /**
     * @return string the success message
     *
     * @throws ValidationException (key "approval")
     */
    public function reject(Adjustment $adjustment, ?string $reason, ?int $userId): string
    {
        if (! $adjustment->isApprovalRequired()) {
            throw ValidationException::withMessages(['approval' => 'Only adjustment types that require manager approval can be rejected through this workflow.']);
        }
        if ($adjustment->paid_payroll_id) {
            throw ValidationException::withMessages(['approval' => 'This adjustment is already linked to a payroll and cannot be rejected.']);
        }

        $this->adjustments->update($adjustment, [
            'status' => Adjustment::STATUS_REJECTED,
            'rejected_by' => $userId,
            'rejected_at' => now('Asia/Manila'),
            'rejection_reason' => trim((string) ($reason ?? 'Rejected by Head Manager / authorized approver.')),
            'approved_by' => null,
            'approved_at' => null,
        ]);

        $isOvertime = $adjustment->adjustment_type === Adjustment::TYPE_OVERTIME;
        if (! $isOvertime) {
            $this->rebuildSummary($adjustment);
        }

        return $isOvertime
            ? 'Overtime adjustment rejected. It will not be paid.'
            : 'Offset adjustment rejected. No compensatory-time credit will be applied.';
    }

    /** @throws ValidationException (key "adjustment") when it is already paid */
    public function delete(Adjustment $adjustment): void
    {
        if ($adjustment->paid_payroll_id) {
            throw ValidationException::withMessages(['adjustment' => 'This adjustment is already linked to a generated payroll and cannot be deleted until the affected draft payroll is deleted/regenerated.']);
        }

        $range = $this->dateRange($adjustment);
        $this->adjustments->delete($adjustment);
        $this->rebuildDates($range);
    }

    /** @return array{path: string, name: string, mime: string} the uploaded OT form; 404 when missing */
    public function attachment(Adjustment $adjustment): array
    {
        $path = (string) $adjustment->attachment_path;
        abort_if($path === '' || ! Storage::disk(self::DISK)->exists($path), 404);

        return [
            'path' => Storage::disk(self::DISK)->path($path),
            'name' => $adjustment->attachment_name ?: basename($path),
            'mime' => $adjustment->attachment_mime ?: 'application/octet-stream',
        ];
    }

    /**
     * The offset proof columns, after checking the sources.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    private function offsetAttributes(array $data, ?int $ignoreId): array
    {
        $result = $this->offsets->evaluate(
            (int) $data['employee_biometric_id'],
            $data['biometric_employee_id'] ?? null,
            $data['employee_no'] ?? null,
            $data['employee_name'],
            $data['work_date'],
            $data['offset_sources'] ?? [],
            $ignoreId,
        );

        if ($result['error'] !== null) {
            throw ValidationException::withMessages([$result['error']['field'] => $result['error']['message']]);
        }

        return $this->offsets->proofAttributes($result['sources'], $result['requested_minutes']);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function payload(array $data, ?bool $isPaidInput, ?Adjustment $existing, ?int $userId): array
    {
        $type = $data['adjustment_type'];
        $rules = Adjustment::rulesFor($type);
        $isLeave = ($rules['date_mode'] ?? 'single') === 'range';
        $manualTime = in_array((string) ($rules['manual_time_mode'] ?? 'none'), ['schedule', 'actual', 'overtime'], true);

        $snapshot = [
            'employee_biometric_id' => null,
            'biometric_employee_id' => Adjustment::GLOBAL_DISASTER_BIOMETRIC_ID,
            'employee_no' => null,
            'employee_name' => Adjustment::GLOBAL_DISASTER_EMPLOYEE_NAME,
            'crosschex_id' => null,
        ];
        if (! Adjustment::isTyphoonDisasterType($type)) {
            $snapshot = $this->identity->snapshot($this->biometrics->findPayrollActiveOrFail((int) $data['employee_biometric_id']));
        }

        $workDate = $isLeave ? $data['date_from'] : $data['work_date'];
        $needsApproval = (bool) ($rules['approval_required'] ?? false);
        $status = $needsApproval ? Adjustment::STATUS_PENDING : Adjustment::STATUS_APPROVED;

        // An approved OT / offset / salary adjustment stays approved only when its critical fields did not change.
        if ($existing && $needsApproval && $existing->status === Adjustment::STATUS_APPROVED) {
            $status = $this->criticalFieldsChanged($existing, $data, $type, $snapshot, $workDate) ? Adjustment::STATUS_PENDING : Adjustment::STATUS_APPROVED;
        }

        $isPaid = (bool) ($rules['default_paid'] ?? false);
        $ignoreLate = (bool) ($rules['default_ignore_late'] ?? false);
        $ignoreUndertime = (bool) ($rules['default_ignore_undertime'] ?? false);

        // Leave pay can be switched off (unpaid leave) without changing the type.
        if ($isLeave && $isPaidInput !== null) {
            $isPaid = $isPaidInput;
        }

        // Offset is a compensatory-time credit applied to the target date's shortage: never cash, never deferred.
        if ($type === Adjustment::TYPE_OFFSET) {
            [$isPaid, $ignoreLate, $ignoreUndertime] = [false, false, false];
        }

        return [
            'employee_biometric_id' => $snapshot['employee_biometric_id'],
            'biometric_employee_id' => $snapshot['biometric_employee_id'],
            'employee_no' => $snapshot['employee_no'],
            'employee_name' => $snapshot['employee_name'],
            'crosschex_id' => $snapshot['crosschex_id'],
            'work_date' => $workDate,
            'date_from' => $isLeave ? $data['date_from'] : null,
            'date_to' => $isLeave ? $data['date_to'] : null,
            'adjustment_type' => $type,
            'adjusted_time_in' => $manualTime ? ($data['adjusted_time_in'] ?? null) : null,
            'adjusted_time_out' => $manualTime ? ($data['adjusted_time_out'] ?? null) : null,
            'adjusted_day_type' => $this->dayType($type),
            'offset_source_date' => $type === Adjustment::TYPE_OFFSET ? $data['offset_source_date'] : null,
            'offset_source_time_in' => null,
            'offset_source_time_out' => null,
            'offset_source_logs' => null,
            'offset_sources' => null,
            'approved_minutes' => null,
            'amount' => $type === Adjustment::TYPE_CASH_ADJUSTMENT ? round((float) ($data['amount'] ?? 0), 2) : null,
            'defer_to_next_payroll' => false,
            'payroll_effective_date' => null,
            'is_paid' => $isPaid,
            'ignore_late' => $ignoreLate,
            'ignore_undertime' => $ignoreUndertime,
            'status' => $status,
            'approved_by' => $status === Adjustment::STATUS_APPROVED ? ($userId ?: $existing?->approved_by) : null,
            'approved_at' => $status === Adjustment::STATUS_APPROVED ? ($existing?->approved_at ?: now('Asia/Manila')) : null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            'reason' => $data['reason'],
            'remarks' => $data['remarks'] ?? null,
            'encoded_by' => $existing?->encoded_by ?: $userId,
            'encoded_at' => $existing?->encoded_at ?: now('Asia/Manila'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $snapshot
     */
    private function criticalFieldsChanged(Adjustment $existing, array $data, string $type, array $snapshot, string $workDate): bool
    {
        $isOffset = $type === Adjustment::TYPE_OFFSET;
        $requestedOffsetMinutes = $isOffset ? max(1, (int) round(((float) ($data['offset_hours'] ?? 0)) * 60)) : null;

        return (string) $existing->adjustment_type !== (string) $type
            || (int) ($existing->employee_biometric_id ?? 0) !== (int) ($snapshot['employee_biometric_id'] ?? 0)
            || (string) $this->date($existing->work_date) !== (string) $workDate
            || (string) $existing->adjusted_time_in !== (string) ($data['adjusted_time_in'] ?? '')
            || (string) $existing->adjusted_time_out !== (string) ($data['adjusted_time_out'] ?? '')
            || ($isOffset && (
                (string) $this->date($existing->offset_source_date) !== (string) ($data['offset_source_date'] ?? '')
                || (int) ($existing->approved_minutes ?? 0) !== (int) $requestedOffsetMinutes
                || $this->sourceSignature($existing->resolvedOffsetSources()) !== $this->sourceSignature($data['offset_sources'] ?? [])
            ))
            || ($type === Adjustment::TYPE_CASH_ADJUSTMENT && round((float) ($existing->amount ?? 0), 2) !== round((float) ($data['amount'] ?? 0), 2));
    }

    /** Order-independent "date=minutes" list, to detect edited offset sources. */
    private function sourceSignature(array $sources): string
    {
        return collect($sources)
            ->filter(fn (mixed $row): bool => is_array($row) && filled($row['date'] ?? null))
            ->map(fn (array $row): string => Carbon::parse($row['date'], 'Asia/Manila')->toDateString().'='.(
                array_key_exists('minutes', $row) ? (int) $row['minutes'] : (int) round(((float) ($row['hours'] ?? 0)) * 60)
            ))
            ->sort()
            ->implode(',');
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    private function assertNoDuplicate(array $data, ?int $ignoreId, string $disasterMessage = 'A Typhoon / Disaster adjustment already exists for this work date.'): void
    {
        $isDisaster = Adjustment::isTyphoonDisasterType($data['adjustment_type'] ?? null);
        $exists = $isDisaster
            ? $this->adjustments->overlapping(Adjustment::TYPHOON_DISASTER_TYPES, null, (string) $data['work_date'], (string) $data['work_date'], $ignoreId)
            : $this->adjustments->overlapping(
                [(string) $data['adjustment_type']],
                (int) $data['employee_biometric_id'],
                (string) ($data['date_from'] ?? $data['work_date']),
                (string) ($data['date_to'] ?? $data['work_date']),
                $ignoreId,
            );

        if ($exists) {
            throw ValidationException::withMessages(['work_date' => $isDisaster
                ? $disasterMessage
                : 'The same adjustment type already exists for this employee within the selected date range.']);
        }
    }

    /**
     * The "File adjustment" dialog limits dates to the payroll's cutoff in the browser; enforce it here.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    private function assertInPayrollPeriod(array $data, PayrollItem $item): void
    {
        $start = $this->date($item->payroll->period_start);
        $end = $this->date($item->payroll->period_end);
        $isLeave = (Adjustment::rulesFor((string) ($data['adjustment_type'] ?? ''))['date_mode'] ?? 'single') === 'range';
        $from = $isLeave ? ($data['date_from'] ?? null) : ($data['work_date'] ?? null);
        $to = $isLeave ? ($data['date_to'] ?? null) : $from;

        if (! $start || ! $end || ! $from || ! $to || ($from <= $end && $to >= $start)) {
            return;
        }

        throw ValidationException::withMessages(['work_date' => sprintf(
            'The selected date must fall within this payroll\'s cutoff (%s - %s).',
            Carbon::parse($start)->format('M d, Y'),
            Carbon::parse($end)->format('M d, Y'),
        )]);
    }

    /** @return array<string, mixed> the stored OT form columns (replacing the previous file), or [] */
    private function storeOtForm(?UploadedFile $file, ?Adjustment $existing): array
    {
        if (! $file) {
            return [];
        }

        $path = $file->store('payroll/ot-forms', self::DISK);
        if ($existing?->attachment_path && $existing->attachment_path !== $path) {
            Storage::disk(self::DISK)->delete($existing->attachment_path);
        }

        return [
            'attachment_path' => $path,
            'attachment_name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'attachment_mime' => $file->getMimeType(),
            'attachment_size' => (int) $file->getSize(),
        ];
    }

    /** Rebuilds the summary of the adjustment's dates (OT and salary adjustments do not change attendance). */
    private function rebuildSummary(Adjustment $adjustment): void
    {
        if (in_array($adjustment->adjustment_type, [Adjustment::TYPE_OVERTIME, Adjustment::TYPE_CASH_ADJUSTMENT], true)) {
            return;
        }

        $this->rebuildDates($this->dateRange($adjustment));
    }

    /** @return list<string> [from, to] */
    private function dateRange(Adjustment $adjustment): array
    {
        $from = $this->date($adjustment->date_from) ?? $this->date($adjustment->work_date);
        $to = $this->date($adjustment->date_to) ?? $from;

        return array_values(array_filter([$from, $to]));
    }

    /** @param list<string> $range */
    private function rebuildDates(array $range): void
    {
        if ($range === []) {
            return;
        }

        foreach (CarbonPeriod::create(Carbon::parse($range[0], 'Asia/Manila'), Carbon::parse($range[1] ?? $range[0], 'Asia/Manila')) as $date) {
            $this->attendance->buildForDate($date->toDateString());
        }
    }

    private function dayType(string $type): string
    {
        if (Adjustment::isTyphoonDisasterType($type)) {
            return 'typhoon_disaster';
        }

        return match ($type) {
            Adjustment::TYPE_SICK_LEAVE => 'sick_leave',
            Adjustment::TYPE_MEDICAL_LEAVE => 'medical_leave',
            Adjustment::TYPE_CHANGE_SCHEDULE => 'change_schedule',
            Adjustment::TYPE_OFFSET => 'offset',
            Adjustment::TYPE_OFFICIAL_BUSINESS => 'official_business',
            Adjustment::TYPE_HOLIDAY_WORK => 'holiday_work',
            Adjustment::TYPE_OVERTIME => 'overtime_approved_interval',
            Adjustment::TYPE_CASH_ADJUSTMENT => 'cash_adjustment',
            default => 'adjustment',
        };
    }

    private function successMessage(Adjustment $adjustment, string $action): string
    {
        if ($adjustment->isGlobalDisasterAdjustment()) {
            $hours = Adjustment::typhoonDisasterRequiredHours($adjustment->adjustment_type) ?? 3;

            return sprintf(
                'Typhoon / Disaster %dhrs adjustment %s. Employees with a valid biometric time-in/time-out pair and at least %d completed paid work hour(s) are paid a full day; employees below the threshold remain on normal attendance computation.',
                $hours,
                $action,
                $hours,
            );
        }

        $pending = $adjustment->status === Adjustment::STATUS_PENDING;

        return match ($adjustment->adjustment_type) {
            Adjustment::TYPE_OFFSET => $pending
                ? 'Offset '.$action.' and is PENDING approval. The source excess time will not affect payroll until approved.'
                : 'Offset '.$action.'. Approved compensatory minutes will cover attendance shortage on the target date. No separate cash Offset payment is created.',
            Adjustment::TYPE_OVERTIME => $pending
                ? 'Overtime adjustment '.$action.' and is PENDING Head Manager approval. Payroll will not pay this OT until it is approved.'
                : 'Overtime adjustment '.$action.' successfully.',
            Adjustment::TYPE_CASH_ADJUSTMENT => sprintf(
                'Salary Adjustment of %s %s. It will be applied to this employee\'s pay for the cutoff containing %s.',
                $adjustment->adjusted_time_label,
                $action,
                optional($adjustment->work_date)->format('M d, Y') ?? 'the selected date',
            ),
            default => 'Payroll attendance adjustment '.$action.' successfully.',
        };
    }

    private function date(mixed $value): ?string
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return null;
        }

        return $value instanceof DateTimeInterface ? Carbon::instance($value)->toDateString() : Carbon::parse(trim((string) $value), 'Asia/Manila')->toDateString();
    }
}
