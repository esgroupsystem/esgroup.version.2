<?php

declare(strict_types=1);

namespace App\Http\Resources\Maintenance;

use App\Models\Receiving;
use App\Models\ReceivingItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * A Receiving record with its items (`inventory/receivings/show`). Each item's rollback limit is
 * the smaller of what is left of the delivery and what the stockroom holds now.
 *
 * @mixin Receiving
 */
final class ReceivingDetailResource extends JsonResource
{
    /** @param Collection<int, int> $stocks product id => current quantity at the stockroom */
    public function __construct(Receiving $resource, private readonly Collection $stocks)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->receiving_number,
            'location' => $this->location->name ?? 'N/A',
            'delivered_by' => $this->delivered_by ?? 'N/A',
            'delivery_date' => $this->delivery_date ? Carbon::parse($this->delivery_date)->format('F d, Y') : 'N/A',
            'receiver' => $this->receiver->full_name ?? ($this->receiver->name ?? 'System'),
            'created' => $this->created_at?->format('M d, Y h:i A') ?? 'N/A',
            'remarks' => $this->remarks ?: 'No remarks provided.',
            'proof_url' => $this->proof_image ? route('receivings.proof', $this->resource) : null,
            'items' => $this->items->values()->map(function (ReceivingItem $item): array {
                $delivered = (int) $item->qty_delivered;
                $rolledBack = (int) ($item->qty_rolled_back ?? 0);
                $remaining = max(0, $delivered - $rolledBack);
                $stock = (int) ($this->stocks[$item->product_id] ?? 0);

                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product->product_name ?? 'N/A',
                    'details' => $item->product->details ?? 'No details available.',
                    'delivered' => $delivered,
                    'rolled_back' => $rolledBack,
                    'remaining' => $remaining,
                    'stock' => $stock,
                    'rollback_limit' => min($remaining, $stock),
                    'rollback_url' => route('receivings.rollback', [$this->id, $item->id]),
                ];
            }),
            'total_delivered' => (int) $this->items->sum('qty_delivered'),
        ];
    }
}
