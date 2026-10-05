<?php

declare(strict_types=1);

namespace App\Http\Resources\Maintenance;

use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One row of Stock Transfer (`inventory/stock-transfers/index`).
 *
 * @mixin StockTransfer
 */
final class StockTransferRowResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->transfer_number,
            'creator' => $this->creator->full_name ?? ($this->creator->name ?? 'System'),
            'rolled_back' => ($this->status ?? 'completed') === 'rolled_back',
            'rolled_back_at' => $this->rolled_back_at?->format('M d, Y h:i A'),
            'from' => $this->fromLocation->name ?? 'N/A',
            'to' => $this->toLocation->name ?? 'N/A',
            'requested_by' => $this->requested_by ?: '—',
            'received_by' => $this->received_by ?: '—',
            'items_count' => (int) ($this->items_count ?? 0),
            'remarks' => $this->remarks,
            'created_date' => $this->created_at?->format('M d, Y'),
            'created_time' => $this->created_at?->format('h:i A'),
            'show_url' => route('stock-transfers.show', $this->id),
            'rollback_url' => route('stock-transfers.rollback', $this->id),
        ];
    }
}
