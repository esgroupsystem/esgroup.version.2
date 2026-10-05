<?php

declare(strict_types=1);

namespace App\Http\Resources\IT;

use App\Models\JobOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One row of the Tickets Job Order list (`it/job-orders/index`).
 *
 * @mixin JobOrder
 */
final class JobOrderRowResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $user = $user instanceof User ? $user : null;
        $itHead = $user !== null && ($user->isDeveloper() || $user->hasAnyRole(['IT Head', 'Developer']));
        $status = strtolower(trim(str_replace(['_', '-'], ' ', (string) ($this->job_status ?? 'Pending'))));
        $date = $this->job_date_filled ?? $this->created_at;

        return [
            'id' => $this->id,
            'bus' => $this->busLabel(),
            'requester' => $this->job_creator ?: 'System',
            'issue' => strtoupper((string) ($this->job_type ?: 'General')),
            'seat' => filled($this->job_sitNumber) ? (string) $this->job_sitNumber : null,
            'status' => $status,
            'status_label' => match (true) {
                in_array($status, ['approval', 'pending'], true) => 'Pending',
                in_array($status, ['disapproved', 'rejected', 'reject'], true) => 'Rejected',
                $status === 'in progress' => 'In Progress',
                $status === 'completed' => 'Completed',
                default => ucwords((string) $this->job_status),
            },
            'date' => $date ? Carbon::parse($date)->format('Y-m-d') : '-',
            'actions' => [
                'approve' => $status === 'approval' && $itHead && $user->can('tickets.approve'),
                'view' => in_array($status, ['pending', 'in progress', 'completed'], true) && (bool) $user?->can('tickets.view'),
                'delete' => in_array($status, ['pending', 'disapproved', 'rejected', 'reject'], true)
                    && $itHead && $user->can('tickets.delete'),
            ],
            'urls' => [
                'view' => route('tickets.joborder.view', $this->id),
                'approve' => route('tickets.approve', $this->id),
                'disapprove' => route('tickets.disapprove', $this->id),
                'delete' => route('tickets.joborder.delete', $this->id),
            ],
        ];
    }
}
