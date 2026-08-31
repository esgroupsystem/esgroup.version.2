<?php

declare(strict_types=1);

namespace App\Services\HR_Department;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class DepartmentService
{
    /** @return Collection<int, Department> */
    public function directory(): Collection
    {
        return Department::query()->with('positions')->latest()->get();
    }

    public function createDepartment(string $name): Department
    {
        return Department::query()->create(['name' => $name]);
    }

    public function createPosition(int $departmentId, string $title): Position
    {
        return Position::query()->create([
            'department_id' => $departmentId,
            'title' => $title,
        ]);
    }

    public function deleteDepartment(Department $department): void
    {
        DB::transaction(static function () use ($department): void {
            $department->delete();
        });
    }

    public function deletePosition(Position $position): void
    {
        $position->delete();
    }
}
