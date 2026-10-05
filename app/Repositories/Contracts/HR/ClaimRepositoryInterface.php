<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\HR;

use App\Models\Claim;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * SSS / Maternity / Paternity claims. Filters: q (reference no. or employee name),
 * employee_id, claim_type, status, and date_field between date_from and date_to.
 */
interface ClaimRepositoryInterface
{
    /**
     * @param  array{q: string, employee_id: string, claim_type: string, status: string, date_field: string, date_from: string, date_to: string}  $filters
     * @return LengthAwarePaginator<int, Claim> newest first, with the employee
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * @param  array{q: string, employee_id: string, claim_type: string, status: string, date_field: string, date_from: string, date_to: string}  $filters
     * @return Collection<string, int> status => count, under the same filters
     */
    public function countByStatus(array $filters): Collection;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Claim;

    /** @param array<string, mixed> $attributes */
    public function update(Claim $claim, array $attributes): void;

    public function delete(Claim $claim): void;
}
