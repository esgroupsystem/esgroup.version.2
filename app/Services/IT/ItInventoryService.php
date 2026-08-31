<?php

declare(strict_types=1);

namespace App\Services\IT;

use App\Models\ItInventoryItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class ItInventoryService
{
    /** @return LengthAwarePaginator<int, ItInventoryItem> */
    public function paginate(string $search = '', string $category = ''): LengthAwarePaginator
    {
        return ItInventoryItem::query()
            ->search($search)
            ->category($category)
            ->orderBy('item_name')
            ->paginate(10)
            ->withQueryString();
    }

    /** @return Collection<int, string> */
    public function categories(): Collection
    {
        return ItInventoryItem::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): ItInventoryItem
    {
        $data['minimum_stock'] = $data['minimum_stock'] ?? 0;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return ItInventoryItem::query()->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(ItInventoryItem $item, array $data): ItInventoryItem
    {
        $item->update($data);

        return $item->refresh();
    }

    public function delete(ItInventoryItem $item): void
    {
        $item->delete();
    }
}
