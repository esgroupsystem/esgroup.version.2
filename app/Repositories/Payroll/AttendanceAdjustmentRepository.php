<?php

declare(strict_types=1);

namespace App\Repositories\Payroll;

use App\Models\Payroll;
use App\Models\PayrollAttendanceAdjustment;
use App\Repositories\Contracts\Payroll\AttendanceAdjustmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class AttendanceAdjustmentRepository implements AttendanceAdjustmentRepositoryInterface
{
    private const STATUSES = [
        PayrollAttendanceAdjustment::STATUS_PENDING,
        PayrollAttendanceAdjustment::STATUS_APPROVED,
        PayrollAttendanceAdjustment::STATUS_REJECTED,
    ];

    public function paginate(array $filters, string $status, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $status)
            ->with(['encoder', 'employeeBiometric', 'approver', 'rejector', 'paidPayroll'])
            ->orderByRaw('COALESCE(date_from, work_date) DESC')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function countByStatus(array $filters): Collection
    {
        return $this->filtered($filters, '')
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn ($count): int => (int) $count);
    }

    public function count(array $filters, string $status, ?array $types = null, ?string $onlyStatus = null): int
    {
        return $this->filtered($filters, $status)
            ->when($types !== null, fn (Builder $query) => $query->whereIn('adjustment_type', $types))
            ->when($onlyStatus !== null, fn (Builder $query) => $query->where('status', $onlyStatus))
            ->count();
    }

    public function overlapping(array $types, ?int $employeeBiometricId, string $dateFrom, string $dateTo, ?int $ignoreId): bool
    {
        return PayrollAttendanceAdjustment::query()
            ->whereIn('adjustment_type', $types)
            ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->when(
                $employeeBiometricId === null,
                fn (Builder $query) => $query->whereDate('work_date', $dateFrom),
                fn (Builder $query) => $query
                    ->where('employee_biometric_id', $employeeBiometricId)
                    ->whereRaw('COALESCE(date_from, work_date) <= ?', [$dateTo])
                    ->whereRaw('COALESCE(date_to, work_date) >= ?', [$dateFrom]),
            )
            ->exists();
    }

    public function offsetsUsingSourceDate(int $employeeBiometricId, string $date, ?int $ignoreId): Collection
    {
        return PayrollAttendanceAdjustment::query()
            ->where('employee_biometric_id', $employeeBiometricId)
            ->where('adjustment_type', PayrollAttendanceAdjustment::TYPE_OFFSET)
            ->where('status', '!=', PayrollAttendanceAdjustment::STATUS_REJECTED)
            // offset_source_date holds the earliest source; the target is always later than every source.
            ->whereDate('offset_source_date', '<=', $date)
            ->whereDate('work_date', '>', $date)
            ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->get();
    }

    public function overtimeFilingsOn(int $employeeBiometricId, string $date, ?int $ignoreId): Collection
    {
        return PayrollAttendanceAdjustment::query()
            ->where('adjustment_type', PayrollAttendanceAdjustment::TYPE_OVERTIME)
            ->where('employee_biometric_id', $employeeBiometricId)
            ->whereDate('work_date', $date)
            ->whereIn('status', [PayrollAttendanceAdjustment::STATUS_PENDING, PayrollAttendanceAdjustment::STATUS_APPROVED])
            ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->get(['id', 'adjusted_time_in', 'adjusted_time_out', 'status']);
    }

    public function countPendingApproval(string $start, string $end, array $employeeBiometricIds): int
    {
        return PayrollAttendanceAdjustment::query()
            ->pending()
            ->whereIn('adjustment_type', [PayrollAttendanceAdjustment::TYPE_OVERTIME, PayrollAttendanceAdjustment::TYPE_OFFSET])
            ->whereBetween('work_date', [$start, $end])
            ->whereIn('employee_biometric_id', $employeeBiometricIds)
            ->count();
    }

    public function countApprovedChangedSince(Payroll $payroll, string $start, string $end, array $employeeBiometricIds): int
    {
        return PayrollAttendanceAdjustment::query()
            ->approved()
            ->where('updated_at', '>', $payroll->generated_at)
            ->where(fn (Builder $query) => $query->whereNull('paid_payroll_id')->orWhere('paid_payroll_id', '!=', $payroll->id))
            ->where(fn (Builder $query) => $query
                ->whereBetween('work_date', [$start, $end])
                ->orWhere(fn (Builder $range) => $range
                    ->whereNotNull('date_from')
                    ->whereRaw('COALESCE(date_from, work_date) <= ?', [$end])
                    ->whereRaw('COALESCE(date_to, work_date) >= ?', [$start]))
                ->orWhereBetween('payroll_effective_date', [$start, $end]))
            ->where(fn (Builder $query) => $query
                ->whereIn('employee_biometric_id', $employeeBiometricIds)
                ->orWhereIn('adjustment_type', PayrollAttendanceAdjustment::TYPHOON_DISASTER_TYPES))
            ->count();
    }

    public function create(array $attributes): PayrollAttendanceAdjustment
    {
        return DB::transaction(fn () => PayrollAttendanceAdjustment::query()->create($attributes));
    }

    public function update(PayrollAttendanceAdjustment $adjustment, array $attributes): void
    {
        DB::transaction(fn () => $adjustment->update($attributes));
    }

    public function delete(PayrollAttendanceAdjustment $adjustment): void
    {
        $adjustment->delete();
    }

    /**
     * @param  array{search: string, type: string, group_name: string, date_from: string, date_to: string}  $filters
     * @return Builder<PayrollAttendanceAdjustment>
     */
    private function filtered(array $filters, string $status): Builder
    {
        $search = $filters['search'];

        return PayrollAttendanceAdjustment::query()
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('employee_name', 'like', "%{$search}%")
                ->orWhere('employee_no', 'like', "%{$search}%")
                ->orWhere('biometric_employee_id', 'like', "%{$search}%")
                ->orWhere('adjustment_type', 'like', "%{$search}%")
                ->orWhere('reason', 'like', "%{$search}%")
                ->orWhere('remarks', 'like', "%{$search}%")
                ->orWhereHas('employeeBiometric', fn (Builder $person) => $person
                    ->where('display_name', 'like', "%{$search}%")
                    ->orWhere('display_employee_no', 'like', "%{$search}%")
                    ->orWhere('group_name', 'like', "%{$search}%"))))
            ->when($filters['type'] !== '', fn (Builder $query) => $query->where('adjustment_type', $filters['type']))
            ->when($filters['group_name'] !== '', fn (Builder $query) => $query->whereHas('employeeBiometric', fn (Builder $person) => $person->where('group_name', $filters['group_name'])))
            ->when($filters['date_from'] !== '', fn (Builder $query) => $query->whereDate(DB::raw('COALESCE(date_from, work_date)'), '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn (Builder $query) => $query->whereDate(DB::raw('COALESCE(date_to, work_date)'), '<=', $filters['date_to']))
            ->when(in_array($status, self::STATUSES, true), fn (Builder $query) => $query->where('status', $status));
    }
}
