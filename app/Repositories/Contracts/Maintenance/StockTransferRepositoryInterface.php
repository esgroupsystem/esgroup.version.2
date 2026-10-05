<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Maintenance;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** Stock Transfer: stock moved between two locations. */
interface StockTransferRepositoryInterface
{
    /**
     * Newest first; search on number, requester, receiver and remarks.
     *
     * @return LengthAwarePaginator<int, StockTransfer>
     */
    public function paginate(string $search, int $perPage = 10): LengthAwarePaginator;

    /** Locations, creator, rollback user and items (product, category, rollback user). */
    public function loadForShow(StockTransfer $transfer): StockTransfer;

    /** Locked transfer with its locations and items. */
    public function findForUpdate(int $id): StockTransfer;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): StockTransfer;

    /** @param array<string, mixed> $attributes */
    public function update(StockTransfer $transfer, array $attributes): void;

    /** @param array<string, mixed> $attributes */
    public function addItem(StockTransfer $transfer, array $attributes): StockTransferItem;

    /** @param array<string, mixed> $attributes */
    public function updateItem(StockTransferItem $item, array $attributes): void;
}
