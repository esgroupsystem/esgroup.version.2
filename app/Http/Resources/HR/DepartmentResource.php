<?php

declare(strict_types=1);

namespace App\Http\Resources\HR;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A department card on the Department & Position page (`hr/departments/index`).
 *
 * @mixin Department
 */
final class DepartmentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'positions' => $this->positions->map(fn (Position $position): array => [
                'id' => $position->id,
                'title' => $position->title,
                'destroy_url' => route('employees.positions.destroy', $position->id),
            ])->values(),
            'destroy_url' => route('employees.departments.destroy', $this->id),
        ];
    }
}
