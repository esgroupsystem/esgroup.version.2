<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\PayrollAuditLogIndexRequest;
use App\Http\Resources\Payroll\PayrollAuditLogResource;
use App\Models\PayrollAuditLog;
use App\Models\User;
use App\Services\Payroll\PayrollAuditLogService;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Payroll → Payroll Transaction Logs.
 */
final class PayrollAuditLogController extends Controller
{
    public function __construct(
        private readonly PayrollAuditLogService $logs,
    ) {}

    public function index(PayrollAuditLogIndexRequest $request): Response
    {
        $filters = $request->filters();
        $labels = fn ($values) => $values->mapWithKeys(fn (string $value): array => [$value => PayrollAuditLogResource::headline($value)]);

        return Inertia::render('payroll/audit-logs/index', [
            'logs' => $this->logs->paginate($filters)
                ->through(fn (PayrollAuditLog $log): array => PayrollAuditLogResource::make($log)->resolve($request)),
            'modules' => $labels($this->logs->modules()),
            'actions' => $labels($this->logs->actions()),
            'users' => $this->logs->users()->mapWithKeys(fn (User $user): array => [(string) $user->id => $user->full_name ?: $user->username]),
            'filters' => $filters,
            'urls' => ['index' => route('payroll-audit-logs.index')],
        ]);
    }
}
