<?php

declare(strict_types=1);

namespace App\Http\Resources\HR;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The employee on a leave row, or (with `value`) an option in the leave form. Load `position` first.
 *
 * @mixin Employee
 */
final class LeaveEmployeeResource extends JsonResource
{
    /** @return array<string, string> */
    public function toArray(Request $request): array
    {
        return [
            'name' => (string) ($this->full_name ?? ''),
            'employee_no' => (string) ($this->employee_id_permanent ?: ($this->employee_id ?: 'No Employee ID')),
            'position' => (string) ($this->position?->title ?? 'No position'),
            'garage' => (string) ($this->garage ?: 'No Garage Assigned'),
            'company' => (string) ($this->company ?: 'No Company'),
            'status' => (string) ($this->status ?? 'No Status'),
        ];
    }

    /** @return array<string, string> */
    public function asOption(Request $request): array
    {
        return ['value' => (string) $this->id, ...$this->toArray($request)];
    }
}
