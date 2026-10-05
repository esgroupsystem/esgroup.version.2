<?php

declare(strict_types=1);

namespace App\Services\IT;

use App\Models\ItInventoryItem;
use App\Repositories\Contracts\IT\ItInventoryItemRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * IT Inventory: parts and supplies that CCTV concerns draw stock from.
 */
final class ItInventoryService
{
    public function __construct(
        private readonly ItInventoryItemRepositoryInterface $items,
    ) {}

    /** @return LengthAwarePaginator<int, ItInventoryItem> */
    public function paginate(string $search = '', string $category = ''): LengthAwarePaginator
    {
        return $this->items->paginate($search, $category);
    }

    /** @return Collection<int, string> */
    public function categories(): Collection
    {
        return $this->items->categories();
    }

    public function find(int $id): ItInventoryItem
    {
        return $this->items->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): ItInventoryItem
    {
        $data['minimum_stock'] = $data['minimum_stock'] ?? 0;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $this->items->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(ItInventoryItem $item, array $data): ItInventoryItem
    {
        $this->items->update($item, $data);

        return $item->refresh();
    }

    public function delete(ItInventoryItem $item): void
    {
        $this->items->delete($item);
    }
}
