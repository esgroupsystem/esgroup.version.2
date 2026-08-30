<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use App\Models\OdometerSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OdometerSubmission */
final class OdometerSubmissionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'bus_detail_id' => $this->bus_detail_id,
            'new_odometer' => $this->new_odometer,
            'diesel_consumption' => $this->diesel_consumption,
            'driver_name' => $this->driver_name,
            'date_bus_deployed' => $this->date_bus_deployed,
            'date' => $this->date,
            'time' => $this->time,
            'created_at' => $this->created_at,
        ];
    }
}
