<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Models\Receiving;
use App\Repositories\Contracts\Maintenance\ProductRepositoryInterface;
use App\Repositories\Contracts\Maintenance\ReceivingRepositoryInterface;
use App\Repositories\Contracts\Maintenance\StockRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Receiving Area: deliveries add stock to a stockroom; each delivered item can be rolled back
 * partly or fully while the stockroom still holds that quantity. The proof photo is private
 * (`local` disk, `receiving_proofs/`).
 */
final class ReceivingService
{
    public function __construct(
        private readonly ReceivingRepositoryInterface $receivings,
        private readonly ProductRepositoryInterface $products,
        private readonly StockRepositoryInterface $stocks,
    ) {}

    /** @return LengthAwarePaginator<int, Receiving> */
    public function paginate(string $search, ?int $locationId): LengthAwarePaginator
    {
        return $this->receivings->paginate(trim($search), $locationId);
    }

    public function findForShow(int $id): Receiving
    {
        return $this->receivings->findForShow($id);
    }

    /** @return Collection<int, int> product id => current quantity at the record's stockroom */
    public function currentStock(Receiving $receiving): Collection
    {
        return $this->stocks->quantities((int) $receiving->location_id, $receiving->items->pluck('product_id'));
    }

    /** @param array<string, mixed> $data validated StoreReceivingRequest (product_id[], qty_delivered[]) */
    public function create(array $data, ?UploadedFile $proof, ?int $userId): Receiving
    {
        $proofPath = null;

        try {
            return DB::transaction(function () use ($data, $proof, $userId, &$proofPath): Receiving {
                if ($proof !== null) {
                    $proofPath = $proof->store('receiving_proofs', 'local');
                }

                $receiving = $this->receivings->create([
                    'receiving_number' => 'PENDING-'.Str::uuid(),
                    'location_id' => $data['location_id'],
                    'delivered_by' => $data['delivered_by'],
                    'delivery_date' => $data['delivery_date'],
                    'remarks' => $data['remarks'] ?? null,
                    'proof_image' => $proofPath ?: null,
                    'received_by' => $userId,
                ]);
                $this->receivings->update($receiving, [
                    'receiving_number' => 'RCV-'.now()->format('Y').'-'.str_pad((string) $receiving->id, 5, '0', STR_PAD_LEFT),
                ]);

                foreach ($data['product_id'] as $index => $productId) {
                    $qty = (int) $data['qty_delivered'][$index];
                    $product = $this->products->findForUpdate((int) $productId);
                    $this->receivings->addItem($receiving, [
                        'product_id' => $product->id,
                        'qty_delivered' => $qty,
                        'qty_rolled_back' => 0,
                    ]);
                    $this->stocks->add($this->stocks->lockOrCreateRow($product->id, (int) $data['location_id']), $qty);
                    $this->stocks->syncProductTotal($product->id);
                }

                return $receiving->fresh(['location', 'receiver', 'items.product']);
            }, 3);
        } catch (Throwable $e) {
            if (is_string($proofPath) && Storage::disk('local')->exists($proofPath)) {
                Storage::disk('local')->delete($proofPath);
            }

            throw $e;
        }
    }

    /**
     * Takes $qty of one delivered item back out of the stockroom. Returns the product name.
     *
     * @throws ValidationException when the user is tied to another stockroom, or the quantity is not available
     */
    public function rollbackItem(int $receivingId, int $itemId, int $qty, ?int $userLocationId): string
    {
        return DB::transaction(function () use ($receivingId, $itemId, $qty, $userLocationId): string {
            $receiving = $this->receivings->findForUpdate($receivingId);
            if ($userLocationId && (int) $receiving->location_id !== $userLocationId) {
                throw ValidationException::withMessages(['rollback_qty' => 'You are not allowed to rollback this receiving record.']);
            }

            $item = $this->receivings->findItemForUpdate($receiving, $itemId);
            if (! $item->product) {
                throw ValidationException::withMessages(['rollback_qty' => 'The selected product no longer exists.']);
            }

            $name = $item->product->product_name ?? 'Selected product';
            $already = (int) ($item->qty_rolled_back ?? 0);
            $remaining = (int) $item->qty_delivered - $already;
            if ($remaining <= 0) {
                throw ValidationException::withMessages(['rollback_qty' => "{$name} is already fully rolled back."]);
            }
            if ($qty > $remaining) {
                throw ValidationException::withMessages(['rollback_qty' => "Rollback quantity exceeds remaining quantity for {$name}. Remaining quantity: {$remaining}."]);
            }

            $stock = $this->stocks->lockRow((int) $item->product_id, (int) $receiving->location_id);
            $available = $stock ? (int) $stock->qty : 0;
            if ($stock === null || $available < $qty) {
                throw ValidationException::withMessages(['rollback_qty' => "Current stock is lower than rollback quantity for {$name}. Available stock: {$available}."]);
            }

            $this->stocks->remove($stock, $qty);
            $this->receivings->updateItem($item, ['qty_rolled_back' => $already + $qty, 'last_rolled_back_at' => now()]);
            $this->stocks->syncProductTotal((int) $item->product_id);

            return $name;
        }, 3);
    }

    /** Absolute path of the delivery proof photo; 404 when there is none or the file is gone. */
    public function proofPath(Receiving $receiving): string
    {
        $path = (string) $receiving->proof_image;
        abort_unless($path !== '' && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->path($path);
    }
}
