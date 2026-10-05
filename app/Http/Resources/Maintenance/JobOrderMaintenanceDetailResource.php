<?php

declare(strict_types=1);

namespace App\Http\Resources\Maintenance;

use App\Enums\JobOrderStatus;
use App\Models\JobOrderMaintenance;
use App\Models\JobOrderMaintenanceHistory;
use App\Models\JobOrderMaintenanceStatusPeriod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A Maintenance Job Order with downtime, status periods, odometer and history (`maintenance/job-orders/show`).
 *
 * @mixin JobOrderMaintenance
 */
final class JobOrderMaintenanceDetailResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $breakdown = $this->downtime_breakdown;
        $km = static fn (?int $value): ?string => $value !== null ? number_format($value).' km' : null;

        return [
            ...JobOrderMaintenanceRowResource::summary($this->resource),
            'created' => $this->created_at->format('M d, Y h:i A'),
            'created_date' => $this->created_at->format('M d, Y'),
            'created_time' => $this->created_at->format('h:i A'),
            'updated' => $this->updated_at?->format('M d, Y h:i A') ?? 'N/A',
            'work' => $this->description_of_work,
            'mechanics' => $this->mechanic_names_list,
            'mechanics_label' => $this->mechanic_names_label,
            'repair_types' => JobOrderMaintenanceRowResource::repairTypes($this->resource),
            'repair_types_label' => $this->repair_types_label,
            'downtime' => $this->total_downtime_label,
            'downtime_running' => (bool) $this->is_downtime_running,
            'downtime_breakdown' => collect(JobOrderStatus::downtimeStatuses())->map(fn (JobOrderStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
                'duration' => $breakdown[$status->value]['label'] ?? '—',
                'current' => $this->status === $status,
            ])->values(),
            'periods' => $this->statusPeriods->map(fn (JobOrderMaintenanceStatusPeriod $period): array => [
                'id' => $period->id,
                'status' => $period->status->value,
                'label' => $period->status->label(),
                'started' => $period->started_at?->format('M d, Y h:i A') ?? 'N/A',
                'ended' => $period->ended_at?->format('M d, Y h:i A'),
                'duration' => $period->duration_label,
                'by' => $period->changedBy?->name ?? 'System',
            ])->values(),
            'odometer' => $km($this->odometer_reading),
            'last_odometer' => $km($this->last_odometer_reading),
            'odometer_difference' => $km($this->odometer_difference),
            'odometer_lower' => (bool) $this->is_odometer_lower_than_last,
            'odometer_note' => $this->odometer_comparison_label,
            'histories' => $this->histories->map(fn (JobOrderMaintenanceHistory $history): array => [
                'id' => $history->id,
                'action' => $history->action,
                'at' => $history->created_at?->format('M d, Y h:i A'),
                'by' => $history->user?->name ?? 'System',
                'old' => $history->old_value,
                'new' => $history->new_value,
                'remarks' => $history->remarks,
            ])->values(),
        ];
    }
}
