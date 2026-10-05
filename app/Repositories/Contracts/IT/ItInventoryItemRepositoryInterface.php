<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\IT;

use App\Models\ItInventoryItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ItInventoryItemRepositoryInterface
{
    /** @return LengthAwarePaginator<int, ItInventoryItem> */
    public function paginate(string $search, string $category, int $perPage = 10): LengthAwarePaginator;

    /** @return Collection<int, string> distinct, sorted, no blanks */
    public function categories(): Collection;

    /** @return Collection<int, ItInventoryItem> active items by name */
    public function activeItems(): Collection;

    public function findOrFail(int $id): ItInventoryItem;

    public function findForUpdate(int $id): ?ItInventoryItem;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): ItInventoryItem;

    /** @param array<string, mixed> $attributes */
    public function update(ItInventoryItem $item, array $attributes): void;

    public function delete(ItInventoryItem $item): void;

    public function addStock(ItInventoryItem $item, int $quantity): void;

    public function removeStock(ItInventoryItem $item, int $quantity): void;
}
