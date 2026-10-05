<?php

declare(strict_types=1);

namespace App\Http\Resources\IT;

use App\Http\Resources\Concerns\FormatsDates;
use App\Models\JobOrder;
use App\Models\JobOrderFile;
use App\Models\JobOrderNote;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The job order detail page (`it/job-orders/show`): `job`, edit-form `values`, `files` and `notes`.
 * Load `bus`, `files` and `notes.user` first.
 *
 * @mixin JobOrder
 */
final class JobOrderDetailResource extends JsonResource
{
    use FormatsDates;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'job' => [
                'id' => $this->id,
                'number' => str_pad((string) $this->id, 5, '0', STR_PAD_LEFT),
                'status' => (string) $this->job_status,
                'job_type' => $this->job_type,
                'creator' => $this->job_creator ?: 'System',
                'created_at' => $this->created_at?->format('M d, Y h:i A'),
                'direction' => $this->direction,
                'date_label' => $this->formatDate($this->job_datestart, 'F d, Y') ?? 'N/A',
                'time_start_label' => $this->formatDate($this->job_time_start, 'h:i A') ?? 'N/A',
                'time_end_label' => $this->formatDate($this->job_time_end, 'h:i A') ?? 'N/A',
                'seat' => $this->job_sitNumber,
                'assigned_to' => $this->job_assign_person,
                'remarks' => $this->job_remarks,
                'driver_name' => $this->driver_name,
                'conductor_name' => $this->conductor_name,
                'bus' => $this->bus ? [
                    'name' => $this->bus->name,
                    'body_number' => $this->bus->body_number,
                    'plate_number' => $this->bus->plate_number,
                    'garage' => $this->bus->garage,
                ] : null,
            ],
            'values' => [
                'job_type' => (string) ($this->job_type ?? ''),
                'job_datestart' => $this->formatDate($this->job_datestart, 'Y-m-d') ?? '',
                'job_time_start' => $this->formatDate($this->job_time_start, 'H:i') ?? '',
                'job_time_end' => $this->formatDate($this->job_time_end, 'H:i') ?? '',
                'direction' => (string) ($this->direction ?? ''),
                'job_sitNumber' => $this->job_sitNumber !== null ? (string) $this->job_sitNumber : '',
                'job_remarks' => (string) ($this->job_remarks ?? ''),
                'driver_name' => (string) ($this->driver_name ?? ''),
                'conductor_name' => (string) ($this->conductor_name ?? ''),
            ],
            'files' => $this->files->map(function (JobOrderFile $file): array {
                $name = $file->file_name ?: basename((string) $file->file_path);

                return [
                    'id' => $file->id,
                    'name' => $name,
                    'extension' => strtolower(pathinfo($name, PATHINFO_EXTENSION)),
                    'url' => route('tickets.joborder.file.download', [$this->id, $file->id]),
                ];
            })->values(),
            'notes' => $this->notes->map(fn (JobOrderNote $note): array => [
                'id' => $note->id,
                'reason' => $note->reason,
                'details' => $note->details,
                'user' => $note->user?->full_name ?? 'System',
                'date' => $note->created_at?->format('M d, Y'),
                'time' => $note->created_at?->format('h:i A'),
            ])->values(),
        ];
    }
}
