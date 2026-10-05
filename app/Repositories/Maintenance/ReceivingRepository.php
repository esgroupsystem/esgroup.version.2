<?php

declare(strict_types=1);

namespace App\Repositories\Maintenance;

use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Repositories\Contracts\Maintenance\ReceivingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ReceivingRepository implements ReceivingRepositoryInterface
{
    public function paginate(string $search, ?int $locationId, int $perPage = 10): LengthAwarePaginator
    {
        return Receiving::query()
            ->with(['location', 'receiver'])
            ->withCount('items')
            ->when($locationId !== null, fn (Builder $query) => $query->where('location_id', $locationId))
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('receiving_number', 'like', "%{$search}%")
                ->orWhere('delivered_by', 'like', "%{$search}%")
                ->orWhere('remarks', 'like', "%{$search}%")
                ->orWhereHas('location', fn (Builder $location) => $location->where('name', 'like', "%{$search}%"))))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findForShow(int $id): Receiving
    {
        return Receiving::query()->with(['receiver', 'items.product', 'location'])->findOrFail($id);
    }

    public function findForUpdate(int $id): Receiving
    {
        return Receiving::query()->with('location')->whereKey($id)->lockForUpdate()->firstOrFail();
    }

    public function findItemForUpdate(Receiving $receiving, int $itemId): ReceivingItem
    {
        return ReceivingItem::query()->with('product')->where('receiving_id', $receiving->id)->whereKey($itemId)->lockForUpdate()->firstOrFail();
    }

    public function create(array $attributes): Receiving
    {
        return Receiving::query()->create($attributes);
    }

    public function update(Receiving $receiving, array $attributes): void
    {
        $receiving->update($attributes);
    }

    public function addItem(Receiving $receiving, array $attributes): ReceivingItem
    {
        return ReceivingItem::query()->create(['receiving_id' => $receiving->id] + $attributes);
    }

    public function updateItem(ReceivingItem $item, array $attributes): void
    {
        $item->update($attributes);
    }
}
