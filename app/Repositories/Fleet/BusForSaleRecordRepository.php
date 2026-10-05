<?php

declare(strict_types=1);

namespace App\Repositories\Fleet;

use App\Models\BusForSaleRecord;
use App\Repositories\Contracts\Fleet\BusForSaleRecordRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class BusForSaleRecordRepository implements BusForSaleRecordRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        $search = $filters['search'];

        return BusForSaleRecord::query()
            ->with('bus')
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('bus_no', 'like', "%{$search}%")
                ->orWhere('plate_no', 'like', "%{$search}%")
                ->orWhere('company', 'like', "%{$search}%")
                ->orWhere('garage', 'like', "%{$search}%")
                ->orWhere('storage_area', 'like', "%{$search}%")
                ->orWhere('unit_location', 'like', "%{$search}%")
                ->orWhere('progress', 'like', "%{$search}%")
                ->orWhere('remarks', 'like', "%{$search}%")))
            ->when($filters['company'] !== '', fn (Builder $query) => $query->where('company', $filters['company']))
            ->when($filters['garage'] !== '', fn (Builder $query) => $query->where('garage', $filters['garage']))
            ->when($filters['status'] !== '', fn (Builder $query) => $query->where('status', $filters['status']))
            ->orderByDesc('days_in_breakdown')
            ->orderBy('company')
            ->orderBy('garage')
            ->orderBy('bus_no')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function folderRows(array $filters): Collection
    {
        $search = $filters['search'];

        return BusForSaleRecord::query()
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('bus_no', 'like', "%{$search}%")
                ->orWhere('plate_no', 'like', "%{$search}%")
                ->orWhere('company', 'like', "%{$search}%")
                ->orWhere('garage', 'like', "%{$search}%")
                ->orWhere('remarks', 'like', "%{$search}%")))
            ->when($filters['company'] !== '', fn (Builder $query) => $query->where('company', $filters['company']))
            ->orderBy('company')
            ->orderByRaw('CAST(bus_no AS UNSIGNED), bus_no')
            ->get();
    }

    public function count(): int
    {
        return BusForSaleRecord::query()->count();
    }

    public function countByStatus(): Collection
    {
        return BusForSaleRecord::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total): int => (int) $total);
    }

    public function countByCompanyAndStatus(): Collection
    {
        return BusForSaleRecord::query()
            ->selectRaw("COALESCE(NULLIF(company, ''), 'UNKNOWN') as company_name")
            ->selectRaw('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('company', 'status')
            ->orderBy('company')
            ->toBase()
            ->get();
    }

    public function companies(): Collection
    {
        return $this->distinct('company');
    }

    public function garages(): Collection
    {
        return $this->distinct('garage');
    }

    public function forBus(int $busId): ?BusForSaleRecord
    {
        return BusForSaleRecord::query()->where('bus_id', $busId)->first();
    }

    public function existsForBus(int $busId): bool
    {
        return BusForSaleRecord::query()->where('bus_id', $busId)->exists();
    }

    public function deleteForBus(int $busId, ?int $keepId = null): void
    {
        BusForSaleRecord::query()
            ->where('bus_id', $busId)
            ->when($keepId !== null, fn (Builder $query) => $query->whereKeyNot($keepId))
            ->delete();
    }

    public function save(BusForSaleRecord $record): void
    {
        $record->save();
    }

    public function linkBus(BusForSaleRecord $record, int $busId): void
    {
        $record->updateQuietly(['bus_id' => $busId]);
    }

    public function delete(BusForSaleRecord $record): void
    {
        $record->delete();
    }

    /** @return Collection<int, string> */
    private function distinct(string $column): Collection
    {
        return BusForSaleRecord::query()->whereNotNull($column)->where($column, '!=', '')->distinct()->orderBy($column)->pluck($column);
    }
}
