<?php

declare(strict_types=1);

namespace App\Http\Resources\HR;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A department and its positions for the employee form selects (ids as strings).
 *
 * @mixin Department
 */
final class DepartmentOptionResource extends JsonResource
{
    /** @return array{id: string, name: string, positions: list<array{id: string, title: string}>} */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => (string) $this->name,
            'positions' => $this->positions
                ->map(fn (Position $position): array => ['id' => (string) $position->id, 'title' => (string) $position->title])
                ->values()
                ->all(),
        ];
    }
}
