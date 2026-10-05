<?php

declare(strict_types=1);

namespace App\Http\Resources\Maintenance;

use App\Models\PartsOut;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One row of Parts Issuance (`inventory/parts-out/index`).
 *
 * @mixin PartsOut
 */
final class PartsOutRowResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $date = $this->issued_date ? Carbon::parse($this->issued_date) : null;

        return [
            'id' => $this->id,
            'number' => $this->parts_out_number,
            'vehicle' => $this->vehicle ? [
                'plate_number' => $this->vehicle->plate_number ?? 'N/A',
                'detail' => trim(($this->vehicle->body_number ?? 'No Body No.').($this->vehicle->name ? ' | '.$this->vehicle->name : '')),
            ] : null,
            'location' => $this->location->name ?? 'N/A',
            'mechanic' => $this->mechanic_name ?? '—',
            'date' => $date?->format('M d, Y'),
            'day' => $date?->format('l'),
            'job_order_no' => $this->job_order_no,
            'items_count' => (int) ($this->items_count ?? 0),
            'status' => self::status($this->status),
            'creator' => $this->creator->full_name ?? ($this->creator->name ?? '—'),
            'show_url' => route('parts-out.show', $this->id),
        ];
    }

    /** @return array{key: string, label: string} */
    public static function status(?string $status): array
    {
        $key = strtolower((string) $status);

        return [
            'key' => $key,
            'label' => match ($key) {
                'posted' => 'Posted',
                'cancelled' => 'Cancelled',
                'rolled_back' => 'Rolled Back',
                default => ucfirst(str_replace('_', ' ', (string) ($status ?? 'N/A'))),
            },
        ];
    }
}
