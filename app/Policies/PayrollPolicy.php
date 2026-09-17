<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payroll;
use App\Models\User;

class PayrollPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('payroll.view');
    }

    public function view(User $user, Payroll $payroll): bool
    {
        return $user->can('payroll.view');
    }

    public function create(User $user): bool
    {
        return $user->can('payroll.create');
    }

    public function delete(User $user, Payroll $payroll): bool
    {
        return $user->can('payroll.delete');
    }

    public function finalize(User $user, Payroll $payroll): bool
    {
        return $user->can('payroll.finalize');
    }

    public function export(User $user, Payroll $payroll): bool
    {
        return $user->can('payroll.export');
    }
}
