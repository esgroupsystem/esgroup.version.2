<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR;

use App\Enums\EmployeeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreEmployeeRequest;
use App\Http\Requests\HR\UpdateEmployeeAssetsRequest;
use App\Http\Requests\HR\UpdateEmployeeBiometricLinkRequest;
use App\Http\Requests\HR\UpdateEmployeeRequest;
use App\Http\Requests\HR\UpdateEmployeeStatusDetailsRequest;
use App\Http\Resources\HR\BiometricSummaryResource;
use App\Http\Resources\HR\DepartmentOptionResource;
use App\Http\Resources\HR\EmployeeLogResource;
use App\Http\Resources\HR\EmployeeProfileResource;
use App\Http\Resources\HR\EmployeeRowResource;
use App\Http\Resources\HR\HrOffenseResource;
use App\Http\Resources\HR\IrCaseResource;
use App\Models\Employee;
use App\Models\EmployeeLog;
use App\Models\HrOffense;
use App\Services\HR\EmployeeProfileService;
use App\Services\HR\EmployeeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

/**
 * Human Resources → Employee List and the employee 201 profile.
 */
final class EmployeeController extends Controller
{
    /** Private files are shown inline and never cached. */
    private const FILE_HEADERS = [
        'Content-Disposition' => 'inline',
        'Cache-Control' => 'private, no-store',
        'X-Content-Type-Options' => 'nosniff',
    ];

    public function __construct(
        private readonly EmployeeService $employees,
        private readonly EmployeeProfileService $profiles,
    ) {}

    public function index(Request $request): Response
    {
        session(['employees_back_url' => $request->fullUrl()]);
        $user = $request->user();
        $employees = $this->employees->paginate($request->query());

        return Inertia::render('hr/employees/index', [
            'employees' => $employees->through(fn (Employee $employee): array => EmployeeRowResource::make($employee)->resolve($request)),
            'stats' => $this->employees->stats(),
            'departments' => $this->departmentOptions($request, $this->employees->departmentOptions()),
            'companies' => $this->employees->companiesInUse()->values(),
            'garages' => $this->employees->garagesInUse()->values(),
            'statusOptions' => EmployeeStatus::values(),
            'companyOptions' => Employee::COMPANIES,
            'garageOptions' => Employee::GARAGES,
            'filters' => [
                'search' => (string) $request->query('search', ''),
                'status' => (string) $request->query('status', ''),
                'company' => (string) $request->query('company', ''),
                'garage' => (string) $request->query('garage', ''),
                'per_page' => (string) $employees->perPage(),
            ],
            'can' => [
                'create' => (bool) $user?->can('employees.create'),
                'delete' => (bool) $user?->can('employees.delete'),
            ],
            'urls' => [
                'index' => route('employees.staff.index'),
                'store' => route('employees.staff.store'),
                'checkPermanentId' => route('employees.staff.checkPermanentId'),
            ],
        ]);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        try {
            $this->employees->create($request->validated());
            flash('Employee added successfully!')->success();

            return redirect()->route('employees.staff.index');
        } catch (Throwable $exception) {
            Log::error('Error adding employee', ['message' => $exception->getMessage()]);
            flash('Something went wrong while adding the employee.')->error();

            return back()->withInput();
        }
    }

    public function show(Request $request, Employee $employee): Response
    {
        $data = $this->profiles->profile($employee);
        $canUpdate = (bool) $request->user()?->can('employees.update');
        $irCases = $data['irCases']
            ->map(fn (Collection $records, int|string $irNumber): array => IrCaseResource::make(['ir_number' => $irNumber, 'records' => $records])->resolve($request))
            ->values();
        $biometric = $this->profiles->linkedBiometric($employee);
        $irCasesWith = fn (string $action): int => $irCases->filter(fn (array $case): bool => in_array($action, $case['actions'], true))->count();

        return Inertia::render('hr/employees/show', [
            ...EmployeeProfileResource::make($employee)->resolve($request),
            'biometric' => $biometric ? BiometricSummaryResource::make($biometric)->resolve($request) : null,
            'biometricOptions' => $canUpdate ? $this->profiles->biometricOptions($employee) : [],
            'irGroups' => $irCases,
            'irStats' => [
                'irs' => $irCases->count(),
                'violations' => $irCases->sum('count'),
                'sda' => $irCasesWith('Salary Deduction Authorization'),
                'suspension' => $irCasesWith('Suspension'),
                'final_warning' => $irCasesWith('Final Warning'),
                'remarks' => $irCases->filter(fn (array $case): bool => $case['remarks'] !== [])->count(),
            ],
            'logs' => $data['logs']->through(fn (EmployeeLog $log): array => (new EmployeeLogResource($log, $data['departmentNames'], $data['positionTitles']))->resolve($request)),
            'departments' => $this->departmentOptions($request, $data['departments']),
            'offenses' => $data['offenses']->map(fn (HrOffense $offense): array => HrOffenseResource::make($offense)->asOption())->values(),
            'options' => [
                'statuses' => EmployeeStatus::values(),
                'companies' => Employee::COMPANIES,
                'garages' => Employee::GARAGES,
                'statusTypes' => Employee::STATUS_TYPES,
                'actions' => Employee::DISCIPLINARY_ACTIONS,
            ],
            'can' => ['update' => $canUpdate],
            'urls' => [
                'back' => session('employees_back_url', route('employees.staff.index')),
                'show' => route('employees.staff.show', $employee->id),
                'print' => route('employees.staff.print', $employee->id),
                'update' => route('employees.update', $employee->id),
                'assets' => route('employees.assets.update', $employee->id),
                'statusDetails' => route('employees.status-details.update', $employee->id),
                'attachments' => route('employees.staff.attachments.store', $employee->id),
                'historyStore' => route('employees.staff.history.store', $employee->id),
                'checkPermanentId' => route('employees.staff.checkPermanentId'),
                'biometricLink' => route('employees.biometric-link.update', $employee->id),
            ],
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        try {
            $this->employees->updateProfile(
                $employee,
                $request->validated(),
                $request->boolean('remove_profile_picture'),
                $request->filled('profile_picture_cropped') ? (string) $request->input('profile_picture_cropped') : null,
                $request->file('profile_picture'),
            );
            flash('Employee profile updated successfully!')->success();

            return redirect()->route('employees.staff.show', $employee->id);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $messages) {
                foreach ($messages as $message) {
                    flash($message)->warning();
                }
            }

            return back()->withErrors($exception->errors())->withInput();
        } catch (Throwable $exception) {
            Log::error('Employee profile update failed', ['employee_id' => $employee->id, 'message' => $exception->getMessage()]);
            flash('Unable to update employee profile.')->error();

            return back()->withInput();
        }
    }

    public function updateStatusDetails(UpdateEmployeeStatusDetailsRequest $request, Employee $employee): RedirectResponse
    {
        $this->employees->updateStatusDetails($employee, $request->validated());
        flash('Employee status details updated!')->success();

        return redirect()->route('employees.staff.show', $employee->id);
    }

    public function updateAssets(UpdateEmployeeAssetsRequest $request, Employee $employee): RedirectResponse
    {
        try {
            $this->employees->updateAssets($employee, [
                ...$request->validated(),
                ...array_filter($request->only(array_keys(Employee::DOCUMENTS)), fn ($file): bool => $file !== null),
            ]);
            flash('201 file updated successfully!')->success();
        } catch (Throwable $exception) {
            Log::error('updateAssets error', ['employee_id' => $employee->id, 'message' => $exception->getMessage()]);
            flash('Something went wrong updating the 201 file.')->error();
        }

        return redirect()->route('employees.staff.show', $employee->id);
    }

    public function updateBiometricLink(UpdateEmployeeBiometricLinkRequest $request, Employee $employee): RedirectResponse
    {
        $biometricId = $request->validated('employee_biometric_id');
        $this->employees->linkBiometric($employee, $biometricId === null ? null : (int) $biometricId);

        return back()->with('success', $biometricId ? 'Employee linked to the biometric record.' : 'Biometric link removed.');
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->employees->delete($this->employees->find($id));

            return back()->with('success', 'Employee deleted successfully.');
        } catch (Throwable $exception) {
            Log::error('Employee delete failed', ['employee_id' => $id, 'message' => $exception->getMessage()]);

            return back()->with('error', 'Unable to delete employee.');
        }
    }

    /** The 201 file as a PDF (dompdf), opened in the browser. */
    public function print201(int $id): HttpResponse
    {
        $employee = $this->employees->forPdf($id);

        return Pdf::loadView('hr_department.employees.modals._employee_201_pdf', [
            'employee' => $employee,
            'profileDataUri' => $this->employees->profilePictureDataUri($employee),
        ])->setPaper('A4', 'portrait')->stream($employee->employee_id.'_201.pdf');
    }

    public function checkPermanentId(Request $request): JsonResponse
    {
        return response()->json($this->employees->permanentIdStatus(
            trim((string) $request->query('value', '')),
            $request->query('ignore_id'),
        ));
    }

    public function profilePicture(Employee $employee): BinaryFileResponse
    {
        return response()->file($this->employees->profilePicturePath($employee), self::FILE_HEADERS);
    }

    public function document(Employee $employee, string $type): BinaryFileResponse
    {
        return response()->file($this->employees->documentPath($employee, $type), self::FILE_HEADERS);
    }

    /**
     * @param  Collection<int, \App\Models\Department>  $departments
     * @return list<array<string, mixed>>
     */
    private function departmentOptions(Request $request, Collection $departments): array
    {
        return DepartmentOptionResource::collection($departments)->resolve($request);
    }
}
