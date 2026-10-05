<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Models\PayrollAuditLog;
use App\Models\User;
use App\Repositories\Contracts\Payroll\PayrollAuditLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** Payroll → Payroll Transaction Logs (read only), within the user's payroll groups. */
final class PayrollAuditLogService
{
    public function __construct(
        private readonly PayrollAuditLogRepositoryInterface $logs,
        private readonly PayrollGroupAccessService $groups,
    ) {}

    /**
     * @param  array{search: string, module: string, action: string, user_id: string, date_from: string, date_to: string}  $filters
     * @return LengthAwarePaginator<int, PayrollAuditLog>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->logs->paginate($filters, $this->groups->allowed());
    }

    /** @return Collection<int, string> */
    public function modules(): Collection
    {
        return $this->logs->distinctValues('module', $this->groups->allowed());
    }

    /** @return Collection<int, string> */
    public function actions(): Collection
    {
        return $this->logs->distinctValues('action', $this->groups->allowed());
    }

    /** @return Collection<int, User> */
    public function users(): Collection
    {
        return $this->logs->users($this->groups->allowed());
    }
}
