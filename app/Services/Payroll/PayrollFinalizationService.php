<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Models\Payroll;
use App\Models\PayrollAttendanceAdjustment;
use App\Models\PayrollItem;
use App\Repositories\Contracts\Payroll\AttendanceAdjustmentRepositoryInterface;
use App\Repositories\Contracts\Payroll\PayrollRepositoryInterface;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Finalizing a draft payroll. Refuses while attendance coverage is missing, old deferred-offset
 * items remain, OT / offsets are pending, or approved adjustments changed after generation.
 * Then, in one transaction: reconcile the month's government contributions (closing cutoff),
 * lock the payroll, and post the monthly Benefits Records.
 */
final class PayrollFinalizationService
{
    public function __construct(
        private readonly PayrollRepositoryInterface $payrolls,
        private readonly AttendanceAdjustmentRepositoryInterface $adjustments,
        private readonly MonthlyGovernmentReconciliationService $reconciliation,
        private readonly BenefitContributionPostingService $benefits,
    ) {}

    /**
     * @return string|null the success message, or null when it was already finalized
     *
     * @throws ValidationException (key "payroll") when the draft may not be finalized
     */
    public function finalize(Payroll $payroll, ?int $userId): ?string
    {
        if ($payroll->status === 'finalized') {
            return null;
        }

        $this->assertReady($payroll->loadMissing('items'));

        $posted = 0;
        DB::transaction(function () use ($payroll, $userId, &$posted): void {
            $locked = $this->payrolls->findForUpdate($payroll->id);
            if ($locked->status === 'finalized') {
                return;
            }

            $finalizedAt = now('Asia/Manila');

            // The business 2nd cutoff (11-25 / key `first`) closes the contribution month: reconcile it
            // against the finalized 26-10 cutoff so SSS / MPF use the whole monthly gross.
            $this->reconciliation->reconcileClosingCutoff($locked, false, 'payroll_finalize');
            $this->payrolls->update($locked, ['status' => 'finalized', 'finalized_by' => $userId, 'finalized_at' => $finalizedAt]);

            // The opening 26-10 cutoff posts 0 records: the contribution month is not complete yet.
            $posted = $this->benefits->postForPayroll($locked->fresh(), $userId, $finalizedAt);
        }, 3);

        return (string) $payroll->cutoff_type === 'second'
            ? 'Payroll finalized successfully. This is the 1st cutoff (26-10); monthly Benefits Records will be posted after the 2nd cutoff (11-25) is finalized.'
            : sprintf('Payroll finalized successfully. Exact monthly government contributions were reconciled from both cutoffs and %d Benefits Record(s) were posted.', $posted);
    }

    private function assertReady(Payroll $payroll): void
    {
        $incomplete = $payroll->items->filter(fn (PayrollItem $item): bool => (bool) data_get($item->meta, 'safe_zero_pay', false)
            || (int) data_get($item->meta, 'attendance_summary_coverage.missing_days', 0) > 0);
        if ($incomplete->isNotEmpty()) {
            $this->refuse(sprintf('Cannot finalize payroll. %d employee(s) have missing Attendance Summary coverage. Rebuild the cutoff and regenerate this draft payroll first.', $incomplete->count()));
        }

        $legacyOffsets = $payroll->items->filter(fn (PayrollItem $item): bool => collect(data_get($item->meta, 'manual_adjustments.details', []))
            ->contains(fn ($detail): bool => is_array($detail)
                && (string) ($detail['type'] ?? '') === PayrollAttendanceAdjustment::TYPE_OFFSET
                && ((string) ($detail['effect'] ?? '') === 'Deferred offset payment' || (bool) ($detail['paid_this_cutoff'] ?? false))));
        if ($legacyOffsets->isNotEmpty()) {
            $this->refuse(sprintf('Cannot finalize payroll. %d employee item(s) still contain the old deferred-cash Offset computation. Delete/regenerate this draft payroll under the new normal Offset policy first.', $legacyOffsets->count()));
        }

        $employeeIds = $payroll->items->pluck('employee_biometric_id')->filter()->map(fn ($id): int => (int) $id)->unique()->values()->all();
        $start = $this->date($payroll->period_start);
        $end = $this->date($payroll->period_end);

        $pending = $this->adjustments->countPendingApproval($start, $end, $employeeIds);
        if ($pending > 0) {
            $this->refuse(sprintf('Cannot finalize payroll. %d OT/Offset adjustment(s) in this cutoff are still pending Head Manager approval/rejection. Resolve them, rebuild Attendance Summary when Offset is involved, then regenerate the draft payroll.', $pending));
        }

        // A draft is a financial snapshot: approved adjustments edited after generation make it stale.
        if ($payroll->generated_at) {
            $changed = $this->adjustments->countApprovedChangedSince($payroll, $start, $end, $employeeIds);
            if ($changed > 0) {
                $this->refuse(sprintf('Cannot finalize payroll. %d approved adjustment(s) changed after this draft was generated. Regenerate the draft so OT, offset, leave, holiday-work, and attendance effects are recalculated before finalization.', $changed));
            }
        }
    }

    private function refuse(string $message): never
    {
        throw ValidationException::withMessages(['payroll' => $message]);
    }

    private function date(mixed $value): string
    {
        return $value instanceof DateTimeInterface
            ? Carbon::instance($value)->toDateString()
            : Carbon::parse((string) $value, 'Asia/Manila')->toDateString();
    }
}
