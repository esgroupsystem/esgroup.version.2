<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR_Department\StoreDepartmentRequest;
use App\Http\Requests\HR_Department\StorePositionRequest;
use App\Models\Department;
use App\Models\Position;
use App\Services\HR_Department\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final class DepartmentController extends Controller
{
    public function __construct(
        private readonly DepartmentService $departmentService
    ) {}

    public function index(): Response|RedirectResponse
    {
        try {
            $user = auth()->user();

            return Inertia::render('hr/departments/index', [
                'departments' => collect($this->departmentService->directory())->map(fn (Department $department): array => [
                    'id' => $department->id,
                    'name' => $department->name,
                    'positions' => $department->positions->map(fn (Position $position): array => [
                        'id' => $position->id,
                        'title' => $position->title,
                        'destroy_url' => route('employees.positions.destroy', $position->id),
                    ])->values(),
                    'destroy_url' => route('employees.departments.destroy', $department->id),
                ])->values(),
                'can' => [
                    'create' => (bool) $user?->can('departments.create'),
                    'delete' => (bool) $user?->can('departments.delete'),
                ],
                'urls' => [
                    'store' => route('employees.departments.store'),
                    'storePosition' => route('employees.departments.position.store'),
                ],
            ]);
        } catch (Throwable $exception) {
            Log::error('Department index failed.', [
                'user_id' => auth()->id(),
                'exception' => $exception,
            ]);
            flash('Something went wrong while loading departments.')->error();

            return back();
        }
    }

    public function positions(int $id): JsonResponse
    {
        $department = Department::query()->with('positions')->find($id);

        return response()->json($department->positions ?? []);
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $this->departmentService->createDepartment((string) $request->validated('name'));

        return back()->with('success', 'Department added successfully!');
    }

    public function storePosition(StorePositionRequest $request): RedirectResponse
    {
        $this->departmentService->createPosition(
            (int) $request->validated('department_id'),
            (string) $request->validated('title')
        );

        return back()->with('success', 'Position added successfully!');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->departmentService->deleteDepartment($department);

        return back()->with('success', 'Department deleted successfully!');
    }

    public function destroyPosition(Position $position): RedirectResponse
    {
        $this->departmentService->deletePosition($position);

        return back()->with('success', 'Position deleted successfully!');
    }
}
