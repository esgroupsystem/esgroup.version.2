<?php

declare(strict_types=1);

namespace App\Http\Resources\Maintenance;

use App\Enums\JobOrderRepairType;
use App\Models\JobOrderMaintenance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One row of Maintenance Job Orders (`maintenance/job-orders/index`). summary() is the header block
 * shared with the detail, edit-status and edit-number pages.
 *
 * @mixin JobOrderMaintenance
 */
final class JobOrderMaintenanceRowResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...self::summary($this->resource),
            'work' => $this->description_of_work,
            'repair_types' => self::repairTypes($this->resource),
            'mechanics' => $this->mechanic_names_list !== [] ? $this->mechanic_names_label : null,
            'odometer' => $this->odometer_reading !== null ? number_format($this->odometer_reading).' km' : null,
            'odometer_note' => $this->odometer_comparison_label,
            'odometer_lower' => (bool) $this->is_odometer_lower_than_last,
            'downtime' => $this->total_downtime_label,
            'downtime_running' => (bool) $this->is_downtime_running,
            'created_date' => $this->created_at->format('M d, Y'),
            'created_time' => $this->created_at->format('h:i A'),
            'show_url' => route('maintenance.job-orders.show', $this->resource),
            'edit_status_url' => route('maintenance.job-orders.edit-status', $this->resource),
            'destroy_url' => route('maintenance.job-orders.destroy', $this->resource),
        ];
    }

    /** @return array<string, mixed> bus details fall back to the snapshot taken when the job order was made */
    public static function summary(JobOrderMaintenance $jobOrder): array
    {
        return [
            'id' => $jobOrder->id,
            'job_order_no' => $jobOrder->job_order_no,
            'creator' => $jobOrder->creator?->name ?? 'System',
            'bus_no' => $jobOrder->bus?->bus_no ?? ($jobOrder->bus_no_snapshot ?? 'N/A'),
            'plate_no' => $jobOrder->bus?->plate_no ?? ($jobOrder->plate_no_snapshot ?? 'N/A'),
            'company' => $jobOrder->bus?->company ?? ($jobOrder->company_snapshot ?? 'N/A'),
            'garage' => $jobOrder->bus?->garage ?? ($jobOrder->garage_snapshot ?? 'N/A'),
            'requester' => $jobOrder->full_name ?: 'Not specified',
            'status' => [
                'value' => $jobOrder->status?->value,
                'label' => $jobOrder->status_label,
                'description' => $jobOrder->status_description,
            ],
        ];
    }

    /** @return list<array{value: string, label: string}> */
    public static function repairTypes(JobOrderMaintenance $jobOrder): array
    {
        return $jobOrder->repair_type_enums
            ->map(fn (JobOrderRepairType $type): array => ['value' => $type->value, 'label' => $type->label()])
            ->values()
            ->all();
    }
}
