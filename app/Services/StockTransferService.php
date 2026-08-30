<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class StockTransferService
{
    /**
     * @param array{
     *     from_location_id:int,
     *     to_location_id:int,
     *     transfer_date:string,
     *     requested_by?:string|null,
     *     received_by?:string|null,
     *     remarks?:string|null,
     *     items:array<int, array{product_id:int, qty:int|float|string}>
     * } $data
     */
    public function transfer(array $data): StockTransfer
    {
        $this->validateTransferData($data);

        return DB::transaction(function () use ($data): StockTransfer {
            $transfer = StockTransfer::query()->create([
                'transfer_number' => 'TEMP',
                'from_location_id' => $data['from_location_id'],
                'to_location_id' => $data['to_location_id'],
                'transfer_date' => $data['transfer_date'],
                'requested_by' => $data['requested_by'] ?? null,
                'received_by' => $data['received_by'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $transfer->update([
                'transfer_number' => 'ST-'.now()->format('Y').'-'.str_pad(
                    (string) $transfer->id,
                    5,
                    '0',
                    STR_PAD_LEFT,
                ),
            ]);

            foreach ($data['items'] as $item) {
                $productId = (int) $item['product_id'];
                $qty = (int) $item['qty'];

                $fromStock = ProductStock::query()
                    ->where('product_id', $productId)
                    ->where('location_id', $data['from_location_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $fromStock) {
                    throw new InvalidArgumentException(
                        'Source stock record does not exist for the selected product.'
                    );
                }

                if ((int) $fromStock->qty < $qty) {
                    $product = Product::query()->find($productId);
                    $productName = (string) ($product->product_name ?? 'Unknown product');

                    throw new InvalidArgumentException(
                        "Insufficient stock for product: {$productName}"
                    );
                }

                $toStock = ProductStock::query()
                    ->where('product_id', $productId)
                    ->where('location_id', $data['to_location_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $toStock) {
                    $toStock = ProductStock::query()->create([
                        'product_id' => $productId,
                        'location_id' => $data['to_location_id'],
                        'qty' => 0,
                    ]);
                }

                $fromStock->decrement('qty', $qty);
                $toStock->increment('qty', $qty);

                StockTransferItem::query()->create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $productId,
                    'qty' => $qty,
                ]);
            }

            return $transfer;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function validateTransferData(array $data): void
    {
        $fromLocationId = (int) ($data['from_location_id'] ?? 0);
        $toLocationId = (int) ($data['to_location_id'] ?? 0);

        if ($fromLocationId <= 0 || $toLocationId <= 0) {
            throw new InvalidArgumentException(
                'Source and destination locations are required.'
            );
        }

        if ($fromLocationId === $toLocationId) {
            throw new InvalidArgumentException(
                'Source and destination locations must be different.'
            );
        }

        $items = $data['items'] ?? null;

        if (! is_array($items) || $items === []) {
            throw new InvalidArgumentException(
                'At least one stock-transfer item is required.'
            );
        }

        foreach ($items as $item) {
            if (! is_array($item)) {
                throw new InvalidArgumentException(
                    'Each stock-transfer item must be an array.'
                );
            }

            $productId = (int) ($item['product_id'] ?? 0);
            $qty = $item['qty'] ?? null;

            if ($productId <= 0) {
                throw new InvalidArgumentException(
                    'Each stock-transfer item requires a valid product.'
                );
            }

            if (! is_numeric($qty) || (float) $qty <= 0) {
                throw new InvalidArgumentException(
                    'Stock-transfer item quantities must be greater than zero.'
                );
            }
        }
    }
}
