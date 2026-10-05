<?php

declare(strict_types=1);

namespace App\Http\Resources\Maintenance;

use App\Models\PartsOut;
use App\Models\PartsOutItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One posted Parts Issuance in a vehicle's history (`fleet/vehicle-history/show`).
 *
 * @mixin PartsOut
 */
final class VehicleHistoryRecordResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->issued_date ? Carbon::parse($this->issued_date)->format('M d, Y') : 'N/A',
            'number' => $this->parts_out_number ?? 'N/A',
            'mechanic' => $this->mechanic_name ?? 'N/A',
            'requested_by' => $this->requested_by ?? 'N/A',
            'job_order_no' => $this->job_order_no ?? 'N/A',
            'odometer' => $this->odometer ?? 'N/A',
            'purpose' => $this->purpose ?? 'N/A',
            'remarks' => $this->remarks ?? 'N/A',
            'creator' => $this->creator->full_name ?? ($this->creator->name ?? 'N/A'),
            'items' => $this->items->map(fn (PartsOutItem $item): array => [
                'name' => $item->product->product_name ?? 'N/A',
                'qty' => (int) ($item->qty_used ?? 0),
                'unit' => $item->product->unit ?? '',
                'part_number' => $item->product->part_number ?? null,
                'remarks' => $item->remarks,
            ])->values(),
            'show_url' => route('parts-out.show', $this->id),
        ];
    }
}
