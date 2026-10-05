<?php

declare(strict_types=1);

namespace App\Repositories\HR;

use App\Models\Department;
use App\Models\Position;
use App\Repositories\Contracts\HR\DepartmentRepositoryInterface;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

final class DepartmentRepository implements DepartmentRepositoryInterface
{
    public function latestWithPositions(): Collection
    {
        return Department::query()->with('positions')->latest()->get();
    }

    public function optionsWithPositions(): Collection
    {
        return Department::query()
            ->with(['positions' => fn (HasMany $query) => $query->orderBy('title')])
            ->orderBy('name')
            ->get();
    }

    public function names(): Collection
    {
        return Department::query()->pluck('name', 'id');
    }

    public function positionTitles(): Collection
    {
        return Position::query()->pluck('title', 'id');
    }

    public function create(string $name): Department
    {
        return Department::query()->create(['name' => $name]);
    }

    public function createPosition(int $departmentId, string $title): Position
    {
        return Position::query()->create(['department_id' => $departmentId, 'title' => $title]);
    }

    public function delete(Department $department): void
    {
        $department->delete();
    }

    public function deletePosition(Position $position): void
    {
        $position->delete();
    }
}
