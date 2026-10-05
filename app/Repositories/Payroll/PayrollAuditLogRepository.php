<?php

declare(strict_types=1);

namespace App\Repositories\Payroll;

use App\Models\PayrollAuditLog;
use App\Models\User;
use App\Repositories\Contracts\Payroll\PayrollAuditLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class PayrollAuditLogRepository implements PayrollAuditLogRepositoryInterface
{
    public function paginate(array $filters, string|array|null $allowedGroups, int $perPage = 50): LengthAwarePaginator
    {
        $search = $filters['search'];

        return PayrollAuditLog::query()
            ->with([
                'user:id,full_name,username,email',
                'payroll:id,payroll_number,garage_group',
                'employeeBiometric:id,display_name,source_employee_name,display_employee_no,source_employee_no,source_employee_id,group_name',
            ])
            ->forAllowedGroups($allowedGroups)
            ->when($filters['module'] !== '', fn (Builder $query) => $query->where('module', $filters['module']))
            ->when($filters['action'] !== '', fn (Builder $query) => $query->where('action', $filters['action']))
            ->when($filters['user_id'] !== '', fn (Builder $query) => $query->where('user_id', (int) $filters['user_id']))
            ->when($filters['date_from'] !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $filters['date_to']))
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('description', 'like', "%{$search}%")
                ->orWhere('request_id', 'like', "%{$search}%")
                ->orWhere('auditable_type', 'like', "%{$search}%")
                ->orWhereHas('payroll', fn (Builder $payroll) => $payroll->where('payroll_number', 'like', "%{$search}%"))
                ->orWhereHas('employeeBiometric', fn (Builder $person) => $person
                    ->where('display_name', 'like', "%{$search}%")
                    ->orWhere('source_employee_name', 'like', "%{$search}%")
                    ->orWhere('display_employee_no', 'like', "%{$search}%")
                    ->orWhere('source_employee_no', 'like', "%{$search}%")
                    ->orWhere('source_employee_id', 'like', "%{$search}%"))
                ->orWhereHas('user', fn (Builder $user) => $user
                    ->where('full_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"))))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function distinctValues(string $column, string|array|null $allowedGroups): Collection
    {
        return PayrollAuditLog::query()
            ->forAllowedGroups($allowedGroups)
            ->select($column)
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->map(fn ($value): string => (string) $value);
    }

    public function users(string|array|null $allowedGroups): Collection
    {
        return User::query()
            ->whereIn('id', PayrollAuditLog::query()->forAllowedGroups($allowedGroups)->whereNotNull('user_id')->select('user_id'))
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'username', 'email']);
    }
}
