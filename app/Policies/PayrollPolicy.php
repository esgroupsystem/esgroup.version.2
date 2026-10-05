<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payroll;
use App\Models\User;
use App\Services\Payroll\PayrollGroupAccessService;

/**
 * Payroll permissions, plus the payroll-group boundary: a user may only open, export,
 * finalize or delete payrolls of the groups they are allowed (see PayrollGroupAccessService).
 */
class PayrollPolicy
{
    public function __construct(
        private readonly PayrollGroupAccessService $groups,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->can('payroll.view');
    }

    public function view(User $user, Payroll $payroll): bool
    {
        return $user->can('payroll.view') && $this->inGroup($payroll);
    }

    public function create(User $user): bool
    {
        return $user->can('payroll.create');
    }

    public function update(User $user, Payroll $payroll): bool
    {
        return $user->can('payroll.create') && $this->inGroup($payroll);
    }

    public function delete(User $user, Payroll $payroll): bool
    {
        return $user->can('payroll.delete') && $this->inGroup($payroll);
    }

    public function finalize(User $user, Payroll $payroll): bool
    {
        return $user->can('payroll.finalize') && $this->inGroup($payroll);
    }

    public function export(User $user, Payroll $payroll): bool
    {
        return $user->can('payroll.export') && $this->inGroup($payroll);
    }

    private function inGroup(Payroll $payroll): bool
    {
        return $this->groups->allows($payroll->garage_group);
    }
}
