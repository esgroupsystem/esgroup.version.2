<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Enums\InventoryTransactionStatus;
use App\Models\PartsOut;
use App\Repositories\Contracts\Maintenance\LocationRepositoryInterface;
use App\Repositories\Contracts\Maintenance\PartsOutRepositoryInterface;
use App\Repositories\Contracts\Maintenance\ProductRepositoryInterface;
use App\Repositories\Contracts\Maintenance\StockRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Parts Issuance (parts-out): issuing parts from a stockroom deducts its stock; a rollback
 * returns the stock and soft-deletes the record. Every stock change writes a stock movement.
 */
final class PartsOutService
{
    public function __construct(
        private readonly PartsOutRepositoryInterface $partsOuts,
        private readonly ProductRepositoryInterface $products,
        private readonly LocationRepositoryInterface $locations,
        private readonly StockRepositoryInterface $stocks,
    ) {}

    /** @return LengthAwarePaginator<int, PartsOut> */
    public function paginate(string $search, ?int $locationId): LengthAwarePaginator
    {
        return $this->partsOuts->paginate(trim($search), $locationId);
    }

    public function loadForShow(PartsOut $partsOut): PartsOut
    {
        return $this->partsOuts->loadForShow($partsOut);
    }

    /**
     * @param  array<string, mixed>  $data  validated StorePartsOutRequest (product_id[], qty_used[], item_remarks[])
     *
     * @throws ValidationException when a product has no stock row or too little stock at the stockroom
     */
    public function create(array $data, ?int $userId): PartsOut
    {
        return DB::transaction(function () use ($data, $userId): PartsOut {
            $location = $this->locations->findForUpdate((int) $data['location_id']);

            $partsOut = $this->partsOuts->create([
                'parts_out_number' => 'TEMP',
                'vehicle_id' => $data['vehicle_id'] ?? null,
                'location_id' => $location->id,
                'mechanic_name' => $data['mechanic_name'],
                'requested_by' => $data['requested_by'] ?? null,
                'issued_date' => $data['issued_date'],
                'job_order_no' => $data['job_order_no'] ?? null,
                'odometer' => $data['odometer'] ?? null,
                'purpose' => $data['purpose'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'status' => InventoryTransactionStatus::Posted->value,
                'created_by' => $userId,
            ]);

            $number = 'POUT-'.now()->format('Y').'-'.str_pad((string) $partsOut->id, 5, '0', STR_PAD_LEFT);
            $this->partsOuts->update($partsOut, ['parts_out_number' => $number]);

            foreach ($data['product_id'] as $index => $productId) {
                $qty = (int) $data['qty_used'][$index];
                $product = $this->products->findForUpdate((int) $productId);
                $stock = $this->stocks->lockRow($product->id, $location->id);

                if ($stock === null) {
                    throw ValidationException::withMessages(['product_id' => "No stock record found for {$product->product_name} at {$location->name}."]);
                }

                $before = (int) $stock->qty;
                if ($before < $qty) {
                    throw ValidationException::withMessages(['product_id' => "Insufficient stock for {$product->product_name} at {$location->name}. Available: {$before}, Requested: {$qty}."]);
                }

                $after = $before - $qty;
                $this->partsOuts->addItem($partsOut, [
                    'product_id' => $product->id,
                    'qty_used' => $qty,
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'remarks' => $data['item_remarks'][$index] ?? null,
                ]);
                $this->stocks->setQuantity($stock, $after);
                $this->stocks->syncProductTotal($product->id);
                $this->stocks->recordMovement([
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'reference_type' => 'parts_out',
                    'reference_id' => $partsOut->id,
                    'movement_type' => 'out',
                    'qty' => $qty,
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'transaction_date' => $data['issued_date'],
                    'remarks' => 'Parts Out #'.$number,
                    'created_by' => $userId,
                ]);
            }

            return $partsOut->fresh(['vehicle', 'location', 'items.product']);
        }, 3);
    }

    /**
     * Returns every issued quantity to the stockroom, marks the record rolled back and soft-deletes it.
     *
     * @throws ValidationException when the record is not posted or has no stockroom
     */
    public function rollback(int $partsOutId, ?string $reason, ?int $userId): void
    {
        DB::transaction(function () use ($partsOutId, $reason, $userId): void {
            $partsOut = $this->partsOuts->findForUpdate($partsOutId);
            $status = (string) $partsOut->getRawOriginal('status');

            if ($status === InventoryTransactionStatus::RolledBack->value) {
                throw ValidationException::withMessages(['rollback_reason' => 'This Parts Out record is already rolled back.']);
            }
            if ($status !== InventoryTransactionStatus::Posted->value) {
                throw ValidationException::withMessages(['rollback_reason' => 'Only posted Parts Out records can be rolled back.']);
            }
            if (! $partsOut->location_id) {
                throw ValidationException::withMessages(['rollback_reason' => 'Parts Out location is missing. Cannot return stock.']);
            }

            foreach ($partsOut->items as $item) {
                $qty = (int) $item->qty_used;
                if ($qty <= 0) {
                    continue;
                }

                $this->products->findForUpdate((int) $item->product_id);
                $stock = $this->stocks->lockOrCreateRow((int) $item->product_id, (int) $partsOut->location_id);
                $before = (int) $stock->qty;

                $this->stocks->setQuantity($stock, $before + $qty);
                $this->stocks->syncProductTotal((int) $item->product_id);
                $this->stocks->recordMovement([
                    'product_id' => $item->product_id,
                    'location_id' => $partsOut->location_id,
                    'reference_type' => 'parts_out_rollback',
                    'reference_id' => $partsOut->id,
                    'movement_type' => 'in',
                    'qty' => $qty,
                    'stock_before' => $before,
                    'stock_after' => $before + $qty,
                    'transaction_date' => now(),
                    'remarks' => 'Rollback of Parts Out #'.$partsOut->parts_out_number,
                    'created_by' => $userId,
                ]);
            }

            $this->partsOuts->update($partsOut, [
                'status' => InventoryTransactionStatus::RolledBack->value,
                'rolled_back_at' => now(),
                'rolled_back_by' => $userId,
                'rollback_reason' => $reason,
            ]);
            $this->partsOuts->delete($partsOut);
        });
    }
}
