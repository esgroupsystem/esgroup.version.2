<?php

declare(strict_types=1);

namespace App\Repositories\Fleet;

use App\Models\BusDetail;
use App\Repositories\Contracts\Fleet\BusDetailRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class BusDetailRepository implements BusDetailRepositoryInterface
{
    public function options(): Collection
    {
        return BusDetail::query()
            ->orderBy('body_number')
            ->orderBy('plate_number')
            ->get(['id', 'garage', 'name', 'body_number', 'plate_number']);
    }

    public function allByPlate(): Collection
    {
        return BusDetail::query()->orderBy('plate_number')->get();
    }

    public function lockForUpdate(int $id): ?BusDetail
    {
        return BusDetail::query()->whereKey($id)->lockForUpdate()->first();
    }

    public function paginate(string $search, string $order, int $perPage = 10): LengthAwarePaginator
    {
        return BusDetail::query()
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('garage', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('body_number', 'like', "%{$search}%")
                ->orWhere('plate_number', 'like', "%{$search}%")))
            ->when($order === 'plate', fn (Builder $query) => $query->orderBy('plate_number'), fn (Builder $query) => $query->latest())
            ->paginate($perPage)
            ->withQueryString();
    }

    public function garages(): Collection
    {
        return BusDetail::query()->whereNotNull('garage')->where('garage', '!=', '')->distinct()->orderBy('garage')->pluck('garage');
    }

    public function create(array $attributes): BusDetail
    {
        return BusDetail::query()->create($attributes);
    }

    public function update(BusDetail $bus, array $attributes): void
    {
        $bus->update($attributes);
    }

    public function delete(BusDetail $bus): void
    {
        $bus->delete();
    }

    public function findByBodyNumberOrFail(string $bodyNumber): BusDetail
    {
        return BusDetail::query()->where('body_number', $bodyNumber)->firstOrFail();
    }

    public function paginateByActiveCctvConcerns(string $search, array $statuses, array $issueTypes, int $perPage = 20): LengthAwarePaginator
    {
        return BusDetail::query()
            ->withCount(['cctvConcerns as active_concerns_count' => fn (Builder $query) => $query
                ->whereIn('status', $statuses)
                ->whereIn('issue_type', $issueTypes)])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('body_number', 'like', "%{$search}%")
                        ->orWhere('plate_number', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('garage', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('active_concerns_count')
            ->orderBy('body_number')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }
}
