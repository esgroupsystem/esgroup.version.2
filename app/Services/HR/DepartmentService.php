<?php

declare(strict_types=1);

namespace App\Services\HR;

use App\Models\Department;
use App\Models\Position;
use App\Repositories\Contracts\HR\DepartmentRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Department & Position page. */
final class DepartmentService
{
    public function __construct(
        private readonly DepartmentRepositoryInterface $departments,
    ) {}

    /** @return Collection<int, Department> newest first, with positions */
    public function directory(): Collection
    {
        return $this->departments->latestWithPositions();
    }

    public function createDepartment(string $name): Department
    {
        return $this->departments->create($name);
    }

    public function createPosition(int $departmentId, string $title): Position
    {
        return $this->departments->createPosition($departmentId, $title);
    }

    public function deleteDepartment(Department $department): void
    {
        DB::transaction(fn () => $this->departments->delete($department));
    }

    public function deletePosition(Position $position): void
    {
        $this->departments->deletePosition($position);
    }
}
