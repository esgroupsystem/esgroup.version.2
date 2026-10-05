<?php

declare(strict_types=1);

namespace App\Http\Resources\Maintenance;

use App\Models\PartsOut;
use App\Models\PartsOutItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A Parts Issuance record with its items (`inventory/parts-out/show`).
 *
 * @mixin PartsOut
 */
final class PartsOutDetailResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->parts_out_number,
            'status' => PartsOutRowResource::status($this->status),
            'date' => $this->issued_date ? Carbon::parse($this->issued_date)->format('M d, Y') : 'N/A',
            'mechanic' => $this->mechanic_name ?: '—',
            'vehicle' => $this->vehicle ? [
                'plate_number' => $this->vehicle->plate_number ?? 'N/A',
                'detail' => collect([
                    'Body No.: '.($this->vehicle->body_number ?? 'N/A'),
                    $this->vehicle->name,
                    $this->vehicle->garage ? 'Garage: '.$this->vehicle->garage : null,
                ])->filter()->implode(' | '),
            ] : null,
            'location' => $this->location->name ?? 'N/A',
            'creator' => $this->creator->full_name ?? ($this->creator->name ?? '—'),
            'requested_by' => $this->requested_by ?: '—',
            'job_order_no' => $this->job_order_no ?: '—',
            'odometer' => $this->odometer ?: '—',
            'purpose' => $this->purpose ?: '—',
            'remarks' => $this->remarks ?: '—',
            'items' => $this->items->map(fn (PartsOutItem $item): array => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->product_name ?? 'N/A',
                'supplier' => $item->product->supplier_name ?? '—',
                'unit' => $item->product->unit ?? '—',
                'part_number' => $item->product->part_number ?? '—',
                'qty_used' => (int) $item->qty_used,
                'stock_before' => (int) $item->stock_before,
                'stock_after' => (int) $item->stock_after,
                'remarks' => $item->remarks ?: '—',
            ])->values(),
            'total_qty' => (int) $this->items->sum('qty_used'),
        ];
    }
}
