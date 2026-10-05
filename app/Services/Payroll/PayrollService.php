<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Models\DailyAttendanceSummary;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Repositories\Contracts\Payroll\AttendanceSummaryRepositoryInterface;
use App\Repositories\Contracts\Payroll\PayrollRepositoryInterface;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Payroll → Payroll: the list, generating a draft (rebuilding the attendance summary first),
 * one employee's recompute, the item detail data, delete and exports. Finalizing lives in
 * PayrollFinalizationService; the pay math in PayrollComputationService.
 */
final class PayrollService
{
    public function __construct(
        private readonly PayrollRepositoryInterface $payrolls,
        private readonly AttendanceSummaryRepositoryInterface $summaries,
        private readonly PayrollGroupAccessService $groups,
        private readonly PayrollPeriodService $periods,
        private readonly PayrollComputationService $computation,
        private readonly DailyAttendanceSummaryService $attendance,
        private readonly MonthlyGovernmentReconciliationService $reconciliation,
        private readonly PayrollPayslipService $payslips,
    ) {}

    /** @return LengthAwarePaginator<int, Payroll> */
    public function paginate(string $search, string $status, string $cutoffType, string $group): LengthAwarePaginator
    {
        return $this->payrolls->paginate($search, $status, $cutoffType, $group, $this->groups->allowed());
    }

    /** @return array<string, string> the user's payroll groups */
    public function groupOptions(): array
    {
        return $this->groups->options();
    }

    /** @return array{cutoff_month: int, cutoff_year: int, cutoff_type: string} */
    public function defaultCutoff(): array
    {
        [$month, $year, $type] = $this->periods->getDefaultCutoff();

        return ['cutoff_month' => (int) $month, 'cutoff_year' => (int) $year, 'cutoff_type' => (string) $type];
    }

    /**
     * Generates a draft. The closing (11-25, `first`) cutoff is reconciled against the month at once,
     * so HR sees the statutory true-up and Auto Cap protection before finalizing.
     *
     * @param  array<string, mixed>  $data  validated GeneratePayrollRequest
     */
    public function generate(array $data, bool $rebuildSummary, ?int $userId): Payroll
    {
        [$start, $end] = $this->periods->resolveCutoffRange((int) $data['cutoff_month'], (int) $data['cutoff_year'], (string) $data['cutoff_type']);

        if ($rebuildSummary) {
            $this->attendance->buildForPeriod($start, $end);
        }

        try {
            $payroll = $this->computation->generate($data, $userId);

            if ((string) $payroll->cutoff_type === 'first') {
                $this->reconciliation->reconcileClosingCutoff($payroll, false, 'payroll_generation_preview');
                $payroll->refresh();
            }

            return $payroll;
        } catch (Throwable $exception) {
            Log::error('Payroll generation failed', ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);

            throw $exception;
        }
    }

    /** Payroll with items: included people first, then by name. */
    public function forShow(Payroll $payroll): Payroll
    {
        $this->payrolls->loadForShow($payroll);
        $payroll->setRelation('items', $payroll->items
            ->sortBy(function (PayrollItem $item): string {
                $person = $item->employeeBiometric;
                $inactive = $person && ($person->employment_status === 'inactive' || $person->is_payroll_active === false);

                return ($inactive ? '1' : '0').'|'.strtolower($item->payroll_display_name);
            })
            ->values());

        return $payroll;
    }

    /** @return array<string, float|int> summed item amounts */
    public function totals(Payroll $payroll): array
    {
        $items = $payroll->items;
        $sum = fn (string $column): float => round((float) $items->sum($column), 2);

        return [
            'employees' => $items->count(),
            'regular_pay' => $sum('regular_pay'),
            'holiday_pay' => $sum('holiday_pay'),
            'rest_day_pay' => $sum('rest_day_pay'),
            'overtime_pay' => $sum('overtime_pay'),
            'night_differential_pay' => $sum('night_differential_pay'),
            'leave_pay' => $sum('leave_pay'),
            'other_additions' => $sum('other_additions'),
            'gross_pay' => $sum('gross_pay'),
            'government' => $sum('total_employee_government_deductions'),
            'other_deductions' => $sum('other_deductions'),
            'net_pay' => $sum('net_pay'),
            'payable_days' => (float) $items->sum('total_payable_days'),
            'payable_hours' => (float) $items->sum('total_payable_hours'),
        ];
    }

    /**
     * The item (with employee, logs and settlement) and its daily attendance rows.
     *
     * @return array{item: PayrollItem, summaries: Collection<int, DailyAttendanceSummary>}
     */
    public function itemDetail(Payroll $payroll, PayrollItem $item): array
    {
        abort_if((int) $item->payroll_id !== (int) $payroll->id, 404);

        return [
            'item' => $this->payrolls->loadItemForShow($item),
            'summaries' => $this->summaries->forPayrollItem($this->date($payroll->period_start), $this->date($payroll->period_end), $item),
        ];
    }

    /** Recomputes one employee's item with the latest attendance and adjustments. */
    public function recomputeItem(Payroll $payroll, PayrollItem $item, ?int $userId): void
    {
        abort_if((int) $item->payroll_id !== (int) $payroll->id, 404);

        try {
            $this->computation->recomputeItem($payroll, $item, $userId);
        } catch (Throwable $exception) {
            Log::error('Payroll item recompute failed', [
                'payroll_id' => $payroll->id,
                'item_id' => $item->id,
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw $exception;
        }
    }

    /** False when the payroll is finalized (it cannot be deleted). */
    public function delete(Payroll $payroll): bool
    {
        if ($payroll->status === 'finalized') {
            return false;
        }

        $this->payrolls->delete($payroll);

        return true;
    }

    public function forExport(Payroll $payroll): Payroll
    {
        return $this->payrolls->loadForExport($payroll);
    }

    /** @return array<string, mixed> data for the payslip PDF template */
    public function payslipData(Payroll $payroll): array
    {
        return $this->payslips->build($payroll);
    }

    private function date(mixed $value): string
    {
        return $value instanceof DateTimeInterface
            ? Carbon::instance($value)->toDateString()
            : Carbon::parse((string) $value, 'Asia/Manila')->toDateString();
    }
}
