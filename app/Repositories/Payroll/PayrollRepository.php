<?php

declare(strict_types=1);

namespace App\Repositories\Payroll;

use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Repositories\Contracts\Payroll\PayrollRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class PayrollRepository implements PayrollRepositoryInterface
{
    public function paginate(string $search, string $status, string $cutoffType, string $group, string|array|null $allowedGroups, int $perPage = 15): LengthAwarePaginator
    {
        return Payroll::query()
            ->with(['generator', 'finalizer'])
            ->withCount('items')
            ->when($allowedGroups !== 'all', fn (Builder $query) => empty($allowedGroups)
                ? $query->whereRaw('1 = 0')
                : $query->whereIn('garage_group', array_map('strval', (array) $allowedGroups)))
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('payroll_number', 'like', "%{$search}%")
                ->orWhere('cutoff_type', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->orWhere('remarks', 'like', "%{$search}%")))
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($cutoffType !== '', fn (Builder $query) => $query->where('cutoff_type', $cutoffType))
            ->when($group !== '', fn (Builder $query) => $query->where('garage_group', $group))
            // Actual period dates keep both historical records and the cycle-month cutoff
            // convention in chronological order.
            ->orderByDesc('period_end')
            ->orderByDesc('period_start')
            ->orderByRaw("CASE WHEN cutoff_type = 'first' THEN 2 WHEN cutoff_type = 'second' THEN 1 ELSE 0 END DESC")
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function loadForShow(Payroll $payroll): Payroll
    {
        return $payroll->load(['items.employeeBiometric.company', 'items.paymentLogs', 'generator', 'finalizer']);
    }

    public function loadForExport(Payroll $payroll): Payroll
    {
        return $payroll->load('items.employeeBiometric');
    }

    public function findForUpdate(int $id): Payroll
    {
        return Payroll::query()->lockForUpdate()->findOrFail($id);
    }

    public function finalizedOpeningFor(Payroll $closing): ?Payroll
    {
        return Payroll::query()
            ->where('contribution_month', (int) $closing->contribution_month)
            ->where('contribution_year', (int) $closing->contribution_year)
            ->where('garage_group', (string) $closing->garage_group)
            ->where('cutoff_type', 'second')
            ->where('status', 'finalized')
            ->latest('id')
            ->first();
    }

    public function update(Payroll $payroll, array $attributes): void
    {
        $payroll->update($attributes);
    }

    public function delete(Payroll $payroll): void
    {
        $payroll->delete();
    }

    public function findItemWithPayroll(int $itemId): ?PayrollItem
    {
        return PayrollItem::query()->with('payroll')->find($itemId);
    }

    public function matchingItemIn(Payroll $payroll, PayrollItem $item): ?PayrollItem
    {
        return PayrollItem::query()
            ->where('payroll_id', $payroll->id)
            ->where(function (Builder $query) use ($item): void {
                if ($item->employee_biometric_id) {
                    $query->where('employee_biometric_id', $item->employee_biometric_id);

                    return;
                }
                if ($item->employee_id) {
                    $query->where('employee_id', $item->employee_id);

                    return;
                }
                $query->where('employee_no', $item->employee_no);
            })
            ->first();
    }

    public function loadItemForShow(PayrollItem $item): PayrollItem
    {
        return $item->load(['employeeBiometric.company', 'paymentLogs', 'benefitSettlement']);
    }
}
