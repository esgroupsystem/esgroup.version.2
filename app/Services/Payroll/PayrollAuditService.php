<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Models\BenefitContributionRecord;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Models\Holiday;
use App\Models\MirasolBiometricsLog;
use App\Models\PaymentLog;
use App\Models\Payroll;
use App\Models\PayrollAttendanceAdjustment;
use App\Models\PayrollAuditLog;
use App\Models\PayrollBenefitSettlement;
use App\Models\PayrollEmployeeSalary;
use App\Models\PayrollEmployeeSalaryOtherDeduction;
use App\Models\PayrollItem;
use App\Models\PayrollReportLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PayrollAuditService
{
    public function recordModelChange(
        Model $model,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        array $context = []
    ): ?PayrollAuditLog {
        if (! $this->shouldAudit($model)) {
            return null;
        }

        $module = $this->moduleFor($model);
        $requestId = $this->requestId();

        // The `deleted` Eloquent event runs after the row itself has already
        // been removed. Never write a foreign key that points back to that
        // just-deleted row, otherwise MySQL correctly rejects the audit insert.
        //
        // We still retain the immutable identity in auditable_type /
        // auditable_id and in old_values, so the deleted record remains fully
        // traceable without violating referential integrity.
        $payrollId = $this->payrollId($model);
        $payrollItemId = $this->payrollItemId($model);
        $employeeBiometricId = $this->employeeBiometricId($model);

        if ($action === 'deleted') {
            if ($model instanceof Payroll) {
                $payrollId = null;
            }

            if ($model instanceof PayrollItem) {
                $payrollItemId = null;
            }

            if ($model instanceof EmployeeBiometric) {
                $employeeBiometricId = null;
            }
        }

        return PayrollAuditLog::query()->create([
            'request_id' => $requestId,
            'user_id' => auth()->id(),
            'garage_group' => $this->garageGroup($model),
            'module' => $module,
            'action' => $action,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'payroll_id' => $payrollId,
            'payroll_item_id' => $payrollItemId,
            'employee_biometric_id' => $employeeBiometricId,
            'employee_id' => $this->employeeId($model),
            'description' => $description ?: $this->description($module, $action, $model),
            'old_values' => $this->sanitize($oldValues),
            'new_values' => $this->sanitize($newValues),
            'context' => $this->sanitize($context),
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
            'user_agent' => app()->runningInConsole() ? null : Str::limit((string) request()->userAgent(), 1000, ''),
            'created_at' => now('Asia/Manila'),
        ]);
    }

    public function record(
        string $module,
        string $action,
        ?string $description = null,
        array $context = [],
        ?Payroll $payroll = null,
        ?PayrollItem $item = null,
        ?int $employeeBiometricId = null,
        ?int $employeeId = null
    ): PayrollAuditLog {
        return PayrollAuditLog::query()->create([
            'request_id' => $this->requestId(),
            'user_id' => auth()->id(),
            'garage_group' => $payroll->garage_group ?? ($item !== null ? $item->payroll->garage_group : null),
            'module' => $module,
            'action' => $action,
            'auditable_type' => null,
            'auditable_id' => null,
            'payroll_id' => $payroll->id ?? ($item !== null ? $item->payroll_id : null),
            'payroll_item_id' => $item?->id,
            'employee_biometric_id' => $employeeBiometricId ?? $item?->employee_biometric_id,
            'employee_id' => $employeeId ?? $item?->employee_id,
            'description' => $description,
            'old_values' => null,
            'new_values' => null,
            'context' => $this->sanitize($context),
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
            'user_agent' => app()->runningInConsole() ? null : Str::limit((string) request()->userAgent(), 1000, ''),
            'created_at' => now('Asia/Manila'),
        ]);
    }

    private function shouldAudit(Model $model): bool
    {
        if ($model instanceof PayrollAuditLog) {
            return false;
        }

        if ($model instanceof MirasolBiometricsLog) {
            // Raw API sync already has its own immutable biometric log table.
            // Duplicating every machine punch here would double high-volume data.
            // Manual WFH encodings are user actions, so those are audited.
            return strtoupper((string) $model->device_sn) === 'WFH-MANUAL';
        }

        return in_array($model::class, [
            Payroll::class,
            PayrollItem::class,
            PayrollAttendanceAdjustment::class,
            BenefitContributionRecord::class,
            EmployeeBiometric::class,
            PayrollEmployeeSalary::class,
            PayrollEmployeeSalaryOtherDeduction::class,
            EmployeePlottingSchedule::class,
            PayrollBenefitSettlement::class,
            PaymentLog::class,
            PayrollReportLog::class,
            Holiday::class,
        ], true);
    }

    private function moduleFor(Model $model): string
    {
        return match (true) {
            $model instanceof Payroll,
            $model instanceof PayrollItem,
            $model instanceof PaymentLog,
            $model instanceof PayrollReportLog => 'payroll',
            $model instanceof PayrollAttendanceAdjustment => 'adjustment',
            $model instanceof BenefitContributionRecord, $model instanceof PayrollBenefitSettlement => 'benefits',
            $model instanceof EmployeeBiometric, $model instanceof MirasolBiometricsLog => 'biometrics',
            $model instanceof PayrollEmployeeSalary,
            $model instanceof PayrollEmployeeSalaryOtherDeduction => 'employee_rate',
            $model instanceof EmployeePlottingSchedule => 'schedule',
            $model instanceof Holiday => 'holiday',
            default => 'payroll',
        };
    }

    private function requestId(): string
    {
        if (app()->runningInConsole()) {
            return (string) Str::uuid();
        }

        $existing = request()->attributes->get('payroll_audit_request_id');

        if ($existing) {
            return (string) $existing;
        }

        $requestId = (string) Str::uuid();
        request()->attributes->set('payroll_audit_request_id', $requestId);

        return $requestId;
    }

    private function sanitize(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        $values = Arr::except($values, [
            'password',
            'remember_token',
            'api_key',
            'api_secret',
            'raw',
        ]);

        return collect($values)
            ->map(function ($value) {
                if (is_string($value)) {
                    return Str::limit($value, 5000, '...');
                }

                if (is_array($value)) {
                    return $this->sanitize($value);
                }

                return $value;
            })
            ->all();
    }

    private function payrollId(Model $model): ?int
    {
        if ($model instanceof Payroll) {
            return (int) $model->id;
        }

        $value = data_get($model, 'payroll_id')
            ?? data_get($model, 'paid_payroll_id');

        return $value ? (int) $value : null;
    }

    private function payrollItemId(Model $model): ?int
    {
        if ($model instanceof PayrollItem) {
            return (int) $model->id;
        }

        $value = data_get($model, 'payroll_item_id')
            ?? data_get($model, 'paid_payroll_item_id');

        return $value ? (int) $value : null;
    }

    private function employeeBiometricId(Model $model): ?int
    {
        if ($model instanceof EmployeeBiometric) {
            return (int) $model->id;
        }

        $value = data_get($model, 'employee_biometric_id');

        if (! $value && $model instanceof PayrollEmployeeSalaryOtherDeduction) {
            $value = data_get($model, 'salary.employee_biometric_id');
        }

        return $value ? (int) $value : null;
    }

    private function employeeId(Model $model): ?int
    {
        $value = data_get($model, 'employee_id');

        if (! $value && $model instanceof PayrollEmployeeSalaryOtherDeduction) {
            $value = data_get($model, 'salary.employee_id');
        }

        return $value ? (int) $value : null;
    }

    private function garageGroup(Model $model): ?int
    {
        if ($model instanceof Payroll) {
            return $model->garage_group ? (int) $model->garage_group : null;
        }

        if (
            $model instanceof PayrollItem
            || $model instanceof PayrollBenefitSettlement
            || $model instanceof BenefitContributionRecord
            || $model instanceof PaymentLog
            || $model instanceof PayrollReportLog
        ) {
            $group = data_get($model, 'garage_group')
                ?? data_get($model, 'payroll.garage_group');

            return $group ? (int) $group : null;
        }

        if ($model instanceof EmployeeBiometric) {
            return $model->group_name ? (int) $model->group_name : null;
        }

        $group = data_get($model, 'employeeBiometric.group_name')
            ?? data_get($model, 'salary.employeeBiometric.group_name');

        return $group ? (int) $group : null;
    }

    private function description(string $module, string $action, Model $model): string
    {
        $label = match ($module) {
            'payroll' => data_get($model, 'payroll_number') ?: data_get($model, 'employee_name'),
            'adjustment' => data_get($model, 'employee_name'),
            'benefits' => data_get($model, 'employee_name') ?: 'Benefit settlement',
            'biometrics' => data_get($model, 'employee_name') ?: data_get($model, 'display_name'),
            'employee_rate' => data_get($model, 'employee_name')
                ?: data_get($model, 'salary.employeeBiometric.display_name')
                ?: data_get($model, 'name'),
            'schedule' => data_get($model, 'employee_name'),
            'holiday' => data_get($model, 'name'),
            default => null,
        };

        return trim(sprintf('%s %s%s', ucfirst($module), $action, $label ? ': '.$label : ''));
    }
}
