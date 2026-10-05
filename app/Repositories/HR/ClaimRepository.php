<?php

declare(strict_types=1);

namespace App\Repositories\HR;

use App\Models\Claim;
use App\Repositories\Contracts\HR\ClaimRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ClaimRepository implements ClaimRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters)->with('employee')->latest()->paginate($perPage)->withQueryString();
    }

    public function countByStatus(array $filters): Collection
    {
        return $this->filtered($filters)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total): int => (int) $total);
    }

    public function create(array $attributes): Claim
    {
        return Claim::query()->create($attributes);
    }

    public function update(Claim $claim, array $attributes): void
    {
        $claim->update($attributes);
    }

    public function delete(Claim $claim): void
    {
        $claim->delete();
    }

    /**
     * @param  array{q: string, employee_id: string, claim_type: string, status: string, date_field: string, date_from: string, date_to: string}  $filters
     * @return Builder<Claim>
     */
    private function filtered(array $filters): Builder
    {
        $dateField = $filters['date_field'];

        return Claim::query()
            ->when($filters['q'] !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('reference_no', 'like', "%{$filters['q']}%")
                ->orWhereHas('employee', fn (Builder $employee) => $employee->where('full_name', 'like', "%{$filters['q']}%"))))
            ->when($filters['employee_id'] !== '', fn (Builder $query) => $query->where('employee_id', $filters['employee_id']))
            ->when($filters['claim_type'] !== '', fn (Builder $query) => $query->where('claim_type', $filters['claim_type']))
            ->when($filters['status'] !== '', fn (Builder $query) => $query->where('status', $filters['status']))
            ->when($filters['date_from'] !== '', fn (Builder $query) => $query->whereDate($dateField, '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn (Builder $query) => $query->whereDate($dateField, '<=', $filters['date_to']));
    }
}
