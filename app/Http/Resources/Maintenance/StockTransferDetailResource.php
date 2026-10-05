<?php

declare(strict_types=1);

namespace App\Http\Resources\Maintenance;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A stock transfer with its items and rollback details (`inventory/stock-transfers/show`).
 *
 * @mixin StockTransfer
 */
final class StockTransferDetailResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $rolledBack = self::isRolledBack($this->resource);

        return [
            'id' => $this->id,
            'number' => $this->transfer_number,
            'date' => $this->transfer_date?->format('F d, Y') ?? '—',
            'from' => $this->fromLocation->name ?? 'N/A',
            'to' => $this->toLocation->name ?? 'N/A',
            'requested_by' => $this->requested_by ?? '—',
            'received_by' => $this->received_by ?? '—',
            'creator' => $this->creator->full_name ?? ($this->creator->name ?? '—'),
            'remarks' => $this->remarks,
            'rolled_back' => $rolledBack,
            'rollback' => $rolledBack ? [
                'by' => $this->rollbackUser->full_name ?? ($this->rollbackUser->name ?? 'System'),
                'at' => $this->rolled_back_at?->format('F d, Y h:i A'),
                'reason' => $this->rollback_reason,
            ] : null,
            'items' => $this->items->values()->map(fn (StockTransferItem $item): array => [
                'id' => $item->id,
                'name' => $item->product->product_name ?? 'N/A',
                'category' => $item->product?->category?->name,
                'part_number' => $item->product->part_number ?? '—',
                'unit' => $item->product->unit ?? '—',
                'qty' => (int) $item->qty,
                'rolled_back' => ($item->status ?? 'completed') === 'rolled_back',
            ]),
        ];
    }

    public static function isRolledBack(StockTransfer $transfer): bool
    {
        return ($transfer->status ?? 'completed') === 'rolled_back';
    }
}
