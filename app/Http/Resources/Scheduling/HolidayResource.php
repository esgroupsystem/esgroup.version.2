<?php

declare(strict_types=1);

namespace App\Http\Resources\Scheduling;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One row of the Holiday Calendar list (`payroll/holidays/index`).
 *
 * @mixin Holiday
 */
final class HolidayResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->holiday_type,
            'actual_date' => $this->actual_date->format('M d, Y'),
            'observed_date' => $this->observed_date->format('M d, Y'),
            'is_moved' => (bool) $this->is_moved,
            'is_active' => (bool) $this->is_active,
            'not_worked_multiplier' => (float) $this->not_worked_multiplier,
            'worked_multiplier' => (float) $this->worked_multiplier,
            'source' => $this->source_proclamation,
            'urls' => [
                'edit' => route('holidays.edit', $this->resource),
                'destroy' => route('holidays.destroy', $this->resource),
            ],
        ];
    }
}
