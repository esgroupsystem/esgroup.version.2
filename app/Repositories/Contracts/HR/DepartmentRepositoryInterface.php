<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\HR;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Support\Collection;

/** Departments and their positions. */
interface DepartmentRepositoryInterface
{
    /** @return Collection<int, Department> newest first, with positions (Department & Position page) */
    public function latestWithPositions(): Collection;

    /** @return Collection<int, Department> by name, positions by title (employee form selects) */
    public function optionsWithPositions(): Collection;

    /** @return Collection<int, string> department id => name */
    public function names(): Collection;

    /** @return Collection<int, string> position id => title */
    public function positionTitles(): Collection;

    public function create(string $name): Department;

    public function createPosition(int $departmentId, string $title): Position;

    public function delete(Department $department): void;

    public function deletePosition(Position $position): void;
}
