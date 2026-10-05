<?php

declare(strict_types=1);

namespace App\Http\Resources\IT;

use App\Models\BusDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One bus on the Bus Dashboard (`it/cctv/bus-status`). Needs the `summary` and
 * `total` attributes that BusDashboardService::paginate() sets.
 *
 * @mixin BusDetail
 */
final class BusDashboardRowResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'body_number' => $this->body_number,
            'detail' => implode(' - ', array_filter([$this->plate_number, $this->name, $this->garage])),
            'summary' => $this->getAttribute('summary'),
            'total' => (int) $this->getAttribute('total'),
            'url' => $this->body_number ? route('concern.bus-status.show', $this->body_number) : null,
        ];
    }
}
