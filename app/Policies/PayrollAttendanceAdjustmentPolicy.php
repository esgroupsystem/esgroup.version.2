<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PayrollAttendanceAdjustment;
use App\Models\User;

class PayrollAttendanceAdjustmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('payroll-attendance-adjustments.view');
    }

    public function view(User $user, PayrollAttendanceAdjustment $payrollAttendanceAdjustment): bool
    {
        return $user->can('payroll-attendance-adjustments.view');
    }

    public function create(User $user): bool
    {
        return $user->can('payroll-attendance-adjustments.create');
    }

    public function update(User $user, PayrollAttendanceAdjustment $payrollAttendanceAdjustment): bool
    {
        return $user->can('payroll-attendance-adjustments.update');
    }

    public function delete(User $user, PayrollAttendanceAdjustment $payrollAttendanceAdjustment): bool
    {
        return $user->can('payroll-attendance-adjustments.delete');
    }

    public function approve(User $user, PayrollAttendanceAdjustment $payrollAttendanceAdjustment): bool
    {
        return $user->can('payroll.finalize');
    }

    public function reject(User $user, PayrollAttendanceAdjustment $payrollAttendanceAdjustment): bool
    {
        return $user->can('payroll.finalize');
    }

    public function offsetProof(User $user): bool
    {
        return $user->can('payroll-attendance-adjustments.view')
            || $user->can('payroll-attendance-adjustments.create')
            || $user->can('payroll-attendance-adjustments.update');
    }
}
