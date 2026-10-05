<?php

declare(strict_types=1);

namespace App\Http\Resources\IT;

use App\Http\Resources\Concerns\FormatsDates;
use App\Models\JobOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The printable job order (`it/job-orders/print`). Load `bus` first.
 *
 * @mixin JobOrder
 */
final class JobOrderPrintResource extends JsonResource
{
    use FormatsDates;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $created = $this->job_date_filled ?: $this->created_at;

        return [
            'number' => str_pad((string) $this->id, 5, '0', STR_PAD_LEFT),
            'status' => (string) ($this->job_status ?? ''),
            'created_short' => $this->formatDate($created, 'M d, Y') ?? 'N/A',
            'created_long' => $this->formatDate($created, 'F d, Y h:i A') ?? 'N/A',
            'creator' => $this->job_creator,
            'assigned_to' => $this->job_assign_person,
            'job_type' => $this->job_type,
            'direction' => $this->direction,
            'date_start' => $this->formatDate($this->job_datestart, 'F d, Y') ?? 'N/A',
            'time_start' => $this->formatDate($this->job_time_start, 'h:i A') ?? 'N/A',
            'time_end' => $this->formatDate($this->job_time_end, 'h:i A') ?? 'N/A',
            'seat' => filled($this->job_sitNumber) ? (string) $this->job_sitNumber : null,
            'driver' => $this->driver_name,
            'conductor' => $this->conductor_name,
            'remarks' => $this->job_remarks,
            'bus' => [
                'name' => $this->bus?->name,
                'body_number' => $this->bus?->body_number,
                'plate_number' => $this->bus?->plate_number,
                'garage' => $this->bus?->garage,
            ],
        ];
    }
}
