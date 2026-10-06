<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Payroll;

use App\Models\Payroll;
use App\Models\PayrollItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** Generated payroll runs and their employee items. */
interface PayrollRepositoryInterface
{
    /**
     * Payroll list, limited to the user's payroll groups. Newest period first.
     *
     * @param  string|list<int|string>|null  $allowedGroups
     * @return LengthAwarePaginator<int, Payroll>
     */
    public function paginate(string $search, string $status, string $cutoffType, string $group, string|array|null $allowedGroups, int $perPage = 15): LengthAwarePaginator;

    /** Payroll with items (employee, company, payment logs), generator and finalizer. */
    public function loadForShow(Payroll $payroll): Payroll;

    /** Payroll with items and their employee, for the Excel export. */
    public function loadForExport(Payroll $payroll): Payroll;

    public function findForUpdate(int $id): Payroll;

    /** The finalized opening (26-10, `second`) payroll of the same contribution month and group. */
    public function finalizedOpeningFor(Payroll $closing): ?Payroll;

    /** @param array<string, mixed> $attributes */
    public function update(Payroll $payroll, array $attributes): void;

    public function delete(Payroll $payroll): void;

    public function findItemWithPayroll(int $itemId): ?PayrollItem;

    /** The same employee's item in another payroll (by biometric id, else employee id, else number). */
    public function matchingItemIn(Payroll $payroll, PayrollItem $item): ?PayrollItem;

    /** Item with employee (company), payment logs and benefit settlement, for the item page. */
    public function loadItemForShow(PayrollItem $item): PayrollItem;

    /**
     * How many payrolls were computed with each Payroll Settings version (meta.settings.version_id).
     *
     * @return array<int, array{total: int, finalized: int}> version id => counts
     */
    public function settingsVersionUsage(): array;
}
