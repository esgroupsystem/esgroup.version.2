<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Maintenance;

use App\Models\Receiving;
use App\Models\ReceivingItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** Receiving Area: deliveries that add stock to a location. */
interface ReceivingRepositoryInterface
{
    /**
     * Newest first; search on number, deliverer, remarks and location.
     *
     * @return LengthAwarePaginator<int, Receiving>
     */
    public function paginate(string $search, ?int $locationId, int $perPage = 10): LengthAwarePaginator;

    /** With receiver, location and items with their product. */
    public function findForShow(int $id): Receiving;

    /** Locked delivery with its location. */
    public function findForUpdate(int $id): Receiving;

    /** Locked item of the delivery, with its product. */
    public function findItemForUpdate(Receiving $receiving, int $itemId): ReceivingItem;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Receiving;

    /** @param array<string, mixed> $attributes */
    public function update(Receiving $receiving, array $attributes): void;

    /** @param array<string, mixed> $attributes */
    public function addItem(Receiving $receiving, array $attributes): ReceivingItem;

    /** @param array<string, mixed> $attributes */
    public function updateItem(ReceivingItem $item, array $attributes): void;
}
