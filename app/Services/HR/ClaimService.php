<?php

declare(strict_types=1);

namespace App\Services\HR;

use App\Models\Claim;
use App\Models\Employee;
use App\Repositories\Contracts\HR\ClaimRepositoryInterface;
use App\Repositories\Contracts\HR\EmployeeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** Benefits → SSS / Maternity / Paternity claims. */
final class ClaimService
{
    private const DEFAULT_DATE_FIELD = 'date_filed';

    public function __construct(
        private readonly ClaimRepositoryInterface $claims,
        private readonly EmployeeRepositoryInterface $employees,
    ) {}

    /**
     * Cleans the list filters; an unknown date field falls back to "date_filed".
     *
     * @param  array<string, mixed>  $input
     * @return array{q: string, employee_id: string, claim_type: string, status: string, date_field: string, date_from: string, date_to: string}
     */
    public function filters(array $input): array
    {
        $dateField = (string) ($input['date_field'] ?? self::DEFAULT_DATE_FIELD);

        return [
            'q' => trim((string) ($input['q'] ?? '')),
            'employee_id' => (string) ($input['employee_id'] ?? ''),
            'claim_type' => (string) ($input['claim_type'] ?? ''),
            'status' => (string) ($input['status'] ?? ''),
            'date_field' => array_key_exists($dateField, Claim::DATE_FIELDS) ? $dateField : self::DEFAULT_DATE_FIELD,
            'date_from' => (string) ($input['date_from'] ?? ''),
            'date_to' => (string) ($input['date_to'] ?? ''),
        ];
    }

    /**
     * @param  array{q: string, employee_id: string, claim_type: string, status: string, date_field: string, date_from: string, date_to: string}  $filters
     * @return LengthAwarePaginator<int, Claim>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->claims->paginate($filters);
    }

    /**
     * @param  array{q: string, employee_id: string, claim_type: string, status: string, date_field: string, date_from: string, date_to: string}  $filters
     * @return Collection<string, int>
     */
    public function statusCounts(array $filters): Collection
    {
        return $this->claims->countByStatus($filters);
    }

    /** @return Collection<int, Employee> */
    public function employeeOptions(): Collection
    {
        return $this->employees->options();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, ?int $userId): Claim
    {
        return $this->claims->create($data + ['created_by' => $userId, 'updated_by' => $userId]);
    }

    /** @param array<string, mixed> $data */
    public function update(Claim $claim, array $data, ?int $userId): void
    {
        $this->claims->update($claim, ['updated_by' => $userId] + $data);
    }

    public function delete(Claim $claim): void
    {
        $this->claims->delete($claim);
    }
}
