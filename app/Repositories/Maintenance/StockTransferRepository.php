<?php

declare(strict_types=1);

namespace App\Repositories\Maintenance;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Repositories\Contracts\Maintenance\StockTransferRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class StockTransferRepository implements StockTransferRepositoryInterface
{
    public function paginate(string $search, int $perPage = 10): LengthAwarePaginator
    {
        return StockTransfer::query()
            ->with(['fromLocation', 'toLocation', 'creator'])
            ->withCount('items')
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('transfer_number', 'like', "%{$search}%")
                ->orWhere('requested_by', 'like', "%{$search}%")
                ->orWhere('received_by', 'like', "%{$search}%")
                ->orWhere('remarks', 'like', "%{$search}%")))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function loadForShow(StockTransfer $transfer): StockTransfer
    {
        return $transfer->load(['fromLocation', 'toLocation', 'creator', 'rollbackUser', 'items.product.category', 'items.rollbackUser']);
    }

    public function findForUpdate(int $id): StockTransfer
    {
        $transfer = StockTransfer::query()->whereKey($id)->lockForUpdate()->firstOrFail();

        return $transfer->load(['fromLocation', 'toLocation', 'items.product']);
    }

    public function create(array $attributes): StockTransfer
    {
        return StockTransfer::query()->create($attributes);
    }

    public function update(StockTransfer $transfer, array $attributes): void
    {
        $transfer->update($attributes);
    }

    public function addItem(StockTransfer $transfer, array $attributes): StockTransferItem
    {
        return StockTransferItem::query()->create(['stock_transfer_id' => $transfer->id] + $attributes);
    }

    public function updateItem(StockTransferItem $item, array $attributes): void
    {
        $item->update($attributes);
    }
}
