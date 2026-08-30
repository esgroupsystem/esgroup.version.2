<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use App\Models\BusDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BusDetail */
final class BusResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'body_number' => $this->body_number,
            'plate_number' => $this->plate_number,
        ];
    }
}
