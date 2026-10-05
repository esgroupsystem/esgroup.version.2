<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Payroll;

use App\Models\PayrollAuditLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** Payroll Transaction Logs, always limited to the user's payroll groups. */
interface PayrollAuditLogRepositoryInterface
{
    /**
     * @param  array{search: string, module: string, action: string, user_id: string, date_from: string, date_to: string}  $filters
     * @param  string|list<int|string>|null  $allowedGroups
     * @return LengthAwarePaginator<int, PayrollAuditLog> newest first
     */
    public function paginate(array $filters, string|array|null $allowedGroups, int $perPage = 50): LengthAwarePaginator;

    /**
     * @param  'module'|'action'  $column
     * @param  string|list<int|string>|null  $allowedGroups
     * @return Collection<int, string> distinct values, sorted
     */
    public function distinctValues(string $column, string|array|null $allowedGroups): Collection;

    /**
     * @param  string|list<int|string>|null  $allowedGroups
     * @return Collection<int, User> users who wrote a visible log, by name
     */
    public function users(string|array|null $allowedGroups): Collection;
}
