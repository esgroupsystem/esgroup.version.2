<?php

declare(strict_types=1);

namespace App\Http\Resources\HR;

use App\Models\HrOffense;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * An offense: a row on the HR Offenses page, or (`asOption()`) a choice in the violation form.
 *
 * @mixin HrOffense
 */
final class HrOffenseResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'section' => $this->section,
            'offense_description' => $this->offense_description,
            'offense_type' => $this->offense_type,
            'offense_gravity' => $this->offense_gravity,
        ];
    }

    /** @return array{value: string, label: string, hint: string, description: string} */
    public function asOption(): array
    {
        return [
            'value' => (string) $this->id,
            'label' => (string) $this->section,
            'hint' => Str::limit((string) $this->offense_description, 90),
            'description' => (string) $this->offense_description,
        ];
    }
}
