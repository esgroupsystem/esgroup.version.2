<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreDepartmentRequest;
use App\Http\Requests\HR\StorePositionRequest;
use App\Http\Resources\HR\DepartmentResource;
use App\Models\Department;
use App\Models\Position;
use App\Services\HR\DepartmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Human Resources → Department & Position.
 */
final class DepartmentController extends Controller
{
    public function __construct(
        private readonly DepartmentService $departments,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('hr/departments/index', [
            'departments' => DepartmentResource::collection($this->departments->directory())->resolve($request),
            'can' => [
                'create' => (bool) $user?->can('departments.create'),
                'delete' => (bool) $user?->can('departments.delete'),
            ],
            'urls' => [
                'store' => route('employees.departments.store'),
                'storePosition' => route('employees.departments.position.store'),
            ],
        ]);
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $this->departments->createDepartment((string) $request->validated('name'));

        return back()->with('success', 'Department added successfully!');
    }

    public function storePosition(StorePositionRequest $request): RedirectResponse
    {
        $this->departments->createPosition((int) $request->validated('department_id'), (string) $request->validated('title'));

        return back()->with('success', 'Position added successfully!');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->departments->deleteDepartment($department);

        return back()->with('success', 'Department deleted successfully!');
    }

    public function destroyPosition(Position $position): RedirectResponse
    {
        $this->departments->deletePosition($position);

        return back()->with('success', 'Position deleted successfully!');
    }
}
