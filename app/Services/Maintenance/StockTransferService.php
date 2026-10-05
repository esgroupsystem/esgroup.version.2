<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Enums\InventoryTransactionStatus;
use App\Models\StockTransfer;
use App\Repositories\Contracts\Maintenance\LocationRepositoryInterface;
use App\Repositories\Contracts\Maintenance\ProductRepositoryInterface;
use App\Repositories\Contracts\Maintenance\StockRepositoryInterface;
use App\Repositories\Contracts\Maintenance\StockTransferRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Stock Transfer: moves stock between two active stockrooms in one transaction. A rollback
 * moves every item back while the destination still holds it, and keeps the record.
 */
final class StockTransferService
{
    public function __construct(
        private readonly StockTransferRepositoryInterface $transfers,
        private readonly ProductRepositoryInterface $products,
        private readonly LocationRepositoryInterface $locations,
        private readonly StockRepositoryInterface $stocks,
    ) {}

    /** @return LengthAwarePaginator<int, StockTransfer> */
    public function paginate(string $search): LengthAwarePaginator
    {
        return $this->transfers->paginate(trim($search));
    }

    public function loadForShow(StockTransfer $transfer): StockTransfer
    {
        return $this->transfers->loadForShow($transfer);
    }

    public function isActiveLocation(int $locationId): bool
    {
        return $this->locations->isActive($locationId);
    }

    /**
     * @param  array<string, mixed>  $data  validated StoreStockTransferRequest (product_id[], qty[])
     *
     * @throws ValidationException when a stockroom is inactive or the source has too little stock
     */
    public function create(array $data, ?int $userId): StockTransfer
    {
        return DB::transaction(function () use ($data, $userId): StockTransfer {
            $from = $this->locations->findActiveForUpdate((int) $data['from_location_id']);
            $to = $this->locations->findActiveForUpdate((int) $data['to_location_id']);
            if ($from === null) {
                throw ValidationException::withMessages(['from_location_id' => 'Source location is inactive or not found.']);
            }
            if ($to === null) {
                throw ValidationException::withMessages(['to_location_id' => 'Destination location is inactive or not found.']);
            }

            $transfer = $this->transfers->create([
                'transfer_number' => 'TEMP',
                'from_location_id' => $from->id,
                'to_location_id' => $to->id,
                'transfer_date' => $data['transfer_date'],
                'requested_by' => $data['requested_by'] ?? null,
                'received_by' => $data['received_by'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'status' => InventoryTransactionStatus::Completed->value,
                'created_by' => $userId,
            ]);
            $this->transfers->update($transfer, [
                'transfer_number' => 'ST-'.now()->format('Y').'-'.str_pad((string) $transfer->id, 5, '0', STR_PAD_LEFT),
            ]);

            foreach ($data['product_id'] as $index => $productId) {
                $qty = (int) $data['qty'][$index];
                $product = $this->products->findForUpdate((int) $productId);
                $fromStock = $this->stocks->lockRow($product->id, $from->id);
                $available = $fromStock ? (int) $fromStock->qty : 0;
                if ($fromStock === null || $available < $qty) {
                    throw ValidationException::withMessages(['product_id' => "Insufficient stock for product: {$product->product_name}. Available in {$from->name}: {$available}, Requested: {$qty}."]);
                }

                $toStock = $this->stocks->lockOrCreateRow($product->id, $to->id);
                $this->stocks->remove($fromStock, $qty);
                $this->stocks->add($toStock, $qty);
                $this->transfers->addItem($transfer, [
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'status' => InventoryTransactionStatus::Completed->value,
                ]);
                $this->stocks->syncProductTotal($product->id);
            }

            return $transfer->fresh(['fromLocation', 'toLocation', 'items.product']);
        }, 3);
    }

    /**
     * Moves every item back from the destination to the source stockroom.
     *
     * @throws ValidationException when already rolled back, empty, or the destination no longer holds an item
     */
    public function rollback(int $transferId, int $userId, ?string $reason): StockTransfer
    {
        return DB::transaction(function () use ($transferId, $userId, $reason): StockTransfer {
            $transfer = $this->transfers->findForUpdate($transferId);

            if ((string) $transfer->getRawOriginal('status') === InventoryTransactionStatus::RolledBack->value || $transfer->rolled_back_at) {
                throw ValidationException::withMessages(['rollback_reason' => 'This stock transfer has already been rolled back.']);
            }
            if ($transfer->items->isEmpty()) {
                throw ValidationException::withMessages(['rollback_reason' => 'Cannot rollback because this transfer has no items.']);
            }

            foreach ($transfer->items as $item) {
                $qty = (int) $item->qty;
                if ($qty <= 0) {
                    throw ValidationException::withMessages(['rollback_reason' => 'Invalid rollback quantity detected.']);
                }

                $product = $this->products->findForUpdate((int) $item->product_id);
                $toStock = $this->stocks->lockRow((int) $item->product_id, (int) $transfer->to_location_id);
                if ($toStock === null || (int) $toStock->qty < $qty) {
                    $productName = $product->product_name ?? 'Selected product';
                    $toLocation = $transfer->toLocation?->name ?: 'destination location';
                    $available = $toStock ? (int) $toStock->qty : 0;

                    throw ValidationException::withMessages(['rollback_reason' => "Cannot rollback {$productName}. {$toLocation} only has {$available} available, but rollback needs {$qty}."]);
                }

                $fromStock = $this->stocks->lockOrCreateRow((int) $item->product_id, (int) $transfer->from_location_id);
                $this->stocks->remove($toStock, $qty);
                $this->stocks->add($fromStock, $qty);
                $this->transfers->updateItem($item, [
                    'status' => InventoryTransactionStatus::RolledBack->value,
                    'rolled_back_at' => now(),
                    'rolled_back_by' => $userId,
                ]);
                $this->stocks->syncProductTotal((int) $item->product_id);
            }

            $this->transfers->update($transfer, [
                'status' => InventoryTransactionStatus::RolledBack->value,
                'rolled_back_at' => now(),
                'rolled_back_by' => $userId,
                'rollback_reason' => $reason,
            ]);

            return $transfer->fresh(['fromLocation', 'toLocation', 'items.product', 'rollbackUser']);
        });
    }
}
