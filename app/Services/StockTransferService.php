<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockTransferService
{
    public function transfer(array $data): StockTransfer
    {
        return DB::transaction(function () use ($data) {
            $transfer = StockTransfer::create([
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
                'transfer_number' => 'ST-'.now()->format('Y').'-'.str_pad($transfer->id, 5, '0', STR_PAD_LEFT),
            ]);

            foreach ($data['items'] as $item) {
                $productId = $item['product_id'];
                $qty = (int) $item['qty'];

                $fromStock = ProductStock::firstOrCreate(
                    [
                        'product_id' => $productId,
                        'location_id' => $data['from_location_id'],
                    ],
                    ['qty' => 0]
                );

                if ($fromStock->qty < $qty) {
                    $product = Product::find($productId);
                    throw new \Exception("Insufficient stock for product: {$product->product_name}");
                }

                $toStock = ProductStock::firstOrCreate(
                    [
                        'product_id' => $productId,
                        'location_id' => $data['to_location_id'],
                    ],
                    ['qty' => 0]
                );

                $fromStock->decrement('qty', $qty);
                $toStock->increment('qty', $qty);

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $productId,
                    'qty' => $qty,
                ]);
            }

            return $transfer;
        });
    }
}
