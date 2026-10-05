<?php

declare(strict_types=1);

namespace App\Repositories\IT;

use App\Models\ItInventoryItem;
use App\Repositories\Contracts\IT\ItInventoryItemRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class ItInventoryItemRepository implements ItInventoryItemRepositoryInterface
{
    public function paginate(string $search, string $category, int $perPage = 10): LengthAwarePaginator
    {
        return ItInventoryItem::query()
            ->search($search)
            ->category($category)
            ->orderBy('item_name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function categories(): Collection
    {
        return ItInventoryItem::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
    }

    public function activeItems(): Collection
    {
        return ItInventoryItem::query()
            ->active()
            ->orderBy('item_name')
            ->get(['id', 'item_name', 'category', 'brand', 'model', 'unit', 'stock_qty', 'location']);
    }

    public function findOrFail(int $id): ItInventoryItem
    {
        return ItInventoryItem::query()->findOrFail($id);
    }

    public function findForUpdate(int $id): ?ItInventoryItem
    {
        return ItInventoryItem::query()->lockForUpdate()->find($id);
    }

    public function create(array $attributes): ItInventoryItem
    {
        return ItInventoryItem::query()->create($attributes);
    }

    public function update(ItInventoryItem $item, array $attributes): void
    {
        $item->update($attributes);
    }

    public function delete(ItInventoryItem $item): void
    {
        $item->delete();
    }

    public function addStock(ItInventoryItem $item, int $quantity): void
    {
        $item->increment('stock_qty', $quantity);
    }

    public function removeStock(ItInventoryItem $item, int $quantity): void
    {
        $item->decrement('stock_qty', $quantity);
    }
}
