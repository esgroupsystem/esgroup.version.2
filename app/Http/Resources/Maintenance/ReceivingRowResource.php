<?php

declare(strict_types=1);

namespace App\Http\Resources\Maintenance;

use App\Models\Receiving;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One row of Receiving Area (`inventory/receivings/index`).
 *
 * @mixin Receiving
 */
final class ReceivingRowResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $date = $this->delivery_date ? Carbon::parse($this->delivery_date) : null;

        return [
            'id' => $this->id,
            'number' => $this->receiving_number,
            'location' => $this->location->name ?? 'N/A',
            'delivered_by' => $this->delivered_by ?: 'Not specified',
            'delivery_date' => $date?->format('M d, Y'),
            'delivery_day' => $date?->format('l'),
            'items_count' => (int) ($this->items_count ?? 0),
            'remarks' => $this->remarks,
            'receiver' => $this->receiver->full_name ?? ($this->receiver->name ?? 'System'),
            'created_date' => $this->created_at?->format('M d, Y'),
            'created_time' => $this->created_at?->format('h:i A'),
            'show_url' => route('receivings.show', $this->id),
        ];
    }
}
