<?php

declare(strict_types=1);

namespace App\Http\Resources\Scheduling;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The Holiday Calendar form `values`. Wrap null for a new holiday (regular, standard
 * multipliers, active). A saved holiday whose multipliers differ from its type's standard
 * ones shows "override" switched on.
 *
 * @property-read Holiday|null $resource
 */
final class HolidayFormResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $holiday = $this->resource;
        $type = $holiday->holiday_type ?? Holiday::TYPE_REGULAR;
        $standard = Holiday::standardMultipliers($type);
        $notWorked = $holiday ? (float) $holiday->not_worked_multiplier : (float) $standard['not_worked_multiplier'];
        $worked = $holiday ? (float) $holiday->worked_multiplier : (float) $standard['worked_multiplier'];

        return [
            'name' => (string) ($holiday->name ?? ''),
            'holiday_type' => $type,
            'source_proclamation' => (string) ($holiday->source_proclamation ?? ''),
            'actual_date' => $holiday?->actual_date?->format('Y-m-d') ?? '',
            'observed_date' => $holiday?->observed_date?->format('Y-m-d') ?? '',
            'override_multipliers' => $holiday !== null
                && (abs($notWorked - (float) $standard['not_worked_multiplier']) > 0.001
                    || abs($worked - (float) $standard['worked_multiplier']) > 0.001),
            'not_worked_multiplier' => number_format($notWorked, 2, '.', ''),
            'worked_multiplier' => number_format($worked, 2, '.', ''),
            'notes' => (string) ($holiday->notes ?? ''),
            'is_moved' => (bool) ($holiday->is_moved ?? false),
            'is_active' => (bool) ($holiday->is_active ?? true),
        ];
    }
}
