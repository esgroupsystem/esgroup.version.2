<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\PayrollAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PayrollAuditLogController extends Controller
{
    public function index(Request $request): Response
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

        $json = fn ($value): ?string => $value === null || $value === []
            ? null
            : json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $headline = fn (?string $value): string => ucwords(str_replace('_', ' ', (string) $value));

        return Inertia::render('payroll/audit-logs/index', [
            'logs' => $logs->through(fn (PayrollAuditLog $log): array => [
                'id' => $log->id,
                'date' => $log->created_at?->timezone('Asia/Manila')->format('M d, Y'),
                'time' => $log->created_at?->timezone('Asia/Manila')->format('h:i:s A'),
                'user_name' => $log->user?->full_name ?: ($log->user?->username ?: 'System / Console'),
                'user_email' => $log->user?->email,
                'module' => $headline($log->module),
                'action' => $log->action,
                'action_label' => $headline($log->action),
                'payroll_number' => $log->payroll?->payroll_number,
                'employee_name' => $log->employeeBiometric?->payroll_display_name,
                'employee_no' => $log->employeeBiometric
                    ? ($log->employeeBiometric->effective_employee_no ?? 'Bio ID: '.$log->employee_biometric_id)
                    : ($log->employee_biometric_id ? 'Bio ID: '.$log->employee_biometric_id : null),
                'garage_group' => $log->garage_group ?: 'N/A',
                'description' => $log->description ?: 'Payroll-related change',
                'request_id' => $log->request_id,
                'ip_address' => $log->ip_address ?: 'System / Console',
                'user_agent' => $log->user_agent ?: 'N/A',
                'old_values' => $json($log->old_values),
                'new_values' => $json($log->new_values),
                'context' => $json($log->context),
            ]),
            'modules' => $modules->mapWithKeys(fn ($module): array => [(string) $module => $headline($module)]),
            'actions' => $actions->mapWithKeys(fn ($action): array => [(string) $action => $headline($action)]),
            'users' => $users->mapWithKeys(fn (User $user): array => [(string) $user->id => $user->full_name ?: $user->username]),
            'filters' => [
                'search' => (string) ($filters['search'] ?? ''),
                'module' => (string) ($filters['module'] ?? ''),
                'action' => (string) ($filters['action'] ?? ''),
                'user_id' => isset($filters['user_id']) ? (string) $filters['user_id'] : '',
                'date_from' => (string) ($filters['date_from'] ?? ''),
                'date_to' => (string) ($filters['date_to'] ?? ''),
            ],
            'urls' => ['index' => route('payroll-audit-logs.index')],
        ]);
    }
}
