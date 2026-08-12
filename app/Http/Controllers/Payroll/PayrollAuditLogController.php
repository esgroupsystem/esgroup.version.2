<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\PayrollAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'module' => ['nullable', 'string', 'max:50'],
            'action' => ['nullable', 'string', 'max:50'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $query = PayrollAuditLog::query()
            ->with([
                'user:id,full_name,username,email',
                'payroll:id,payroll_number,garage_group',
                'employeeBiometric:id,display_name,source_employee_name,display_employee_no,source_employee_no,source_employee_id,group_name',
            ])
            ->forAllowedGroups(session('payroll_allowed_groups'));

        $query
            ->when($filters['module'] ?? null, fn (Builder $query, string $module) => $query->where('module', $module))
            ->when($filters['action'] ?? null, fn (Builder $query, string $action) => $query->where('action', $action))
            ->when($filters['user_id'] ?? null, fn (Builder $query, int $userId) => $query->where('user_id', $userId))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->when(trim((string) ($filters['search'] ?? '')) !== '', function (Builder $query) use ($filters): void {
                $search = trim((string) $filters['search']);

                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('description', 'like', "%{$search}%")
                        ->orWhere('request_id', 'like', "%{$search}%")
                        ->orWhere('auditable_type', 'like', "%{$search}%")
                        ->orWhereHas('payroll', fn (Builder $payroll) => $payroll->where('payroll_number', 'like', "%{$search}%"))
                        ->orWhereHas('employeeBiometric', function (Builder $employee) use ($search): void {
                            $employee->where('display_name', 'like', "%{$search}%")
                                ->orWhere('source_employee_name', 'like', "%{$search}%")
                                ->orWhere('display_employee_no', 'like', "%{$search}%")
                                ->orWhere('source_employee_no', 'like', "%{$search}%")
                                ->orWhere('source_employee_id', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function (Builder $user) use ($search): void {
                            $user->where('full_name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            });

        $logs = $query
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        $modules = PayrollAuditLog::query()
            ->forAllowedGroups(session('payroll_allowed_groups'))
            ->select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $actions = PayrollAuditLog::query()
            ->forAllowedGroups(session('payroll_allowed_groups'))
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $users = User::query()
            ->whereIn('id', PayrollAuditLog::query()
                ->forAllowedGroups(session('payroll_allowed_groups'))
                ->whereNotNull('user_id')
                ->select('user_id'))
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'username', 'email']);

        return view('payroll.audit_logs.index', compact(
            'logs',
            'modules',
            'actions',
            'users',
            'filters'
        ));
    }
}
