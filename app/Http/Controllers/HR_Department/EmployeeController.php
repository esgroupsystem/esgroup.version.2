<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR_Department\EmployeeHistoryRequest;
use App\Http\Requests\HR_Department\StoreEmployeeAttachmentRequest;
use App\Http\Requests\HR_Department\StoreEmployeeRequest;
use App\Http\Requests\HR_Department\UpdateEmployeeAssetsRequest;
use App\Http\Requests\HR_Department\UpdateEmployeeRequest;
use App\Http\Requests\HR_Department\UpdateEmployeeStatusDetailsRequest;
use App\Models\Employee;
use App\Models\Position;
use App\Services\HR_Department\EmployeeAttachmentService;
use App\Services\HR_Department\EmployeeDirectoryService;
use App\Services\HR_Department\EmployeeHistoryService;
use App\Services\HR_Department\EmployeeProfileService;
use App\Services\HR_Department\EmployeeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class EmployeeController extends Controller
{
    public function __construct(
        private readonly EmployeeService $employees,
        private readonly EmployeeHistoryService $histories,
        private readonly EmployeeAttachmentService $attachments,
        private readonly EmployeeDirectoryService $directory,
        private readonly EmployeeProfileService $profiles,
    ) {}

    /* ==========================================================
        LISTING / SEARCH
    ========================================================== */
    public function index(Request $request)
    {
        return view('hr_department.employees.index', $this->directory->indexData($request->query()));
    }

    /* ==========================================================
        SHOW PROFILE
    ========================================================== */
    public function show(Employee $employee)
    {
        return view('hr_department.employees.modals._employee_profile', $this->profiles->data($employee));
    }

    /* ==========================================================
        CREATE EMPLOYEE
    ========================================================== */

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        try {
            $this->employees->create($request->validated());
            flash('Employee added successfully!')->success();

            return redirect()->route('employees.staff.index');
        } catch (\Throwable $exception) {
            Log::error('Error adding employee', ['message' => $exception->getMessage()]);
            flash('Something went wrong while adding the employee.')->error();

            return back()->withInput();
        }
    }

    /* ==========================================================
        UPDATE EMPLOYEE PROFILE
    ========================================================== */

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        try {
            $this->employees->updateProfile(
                $employee, $request->validated(), $request->boolean('remove_profile_picture'),
                $request->filled('profile_picture_cropped') ? (string) $request->input('profile_picture_cropped') : null,
                $request->file('profile_picture'),
            );
            flash('Employee profile updated successfully!')->success();

            return redirect()->route('employees.staff.show', $employee->id);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            foreach ($exception->errors() as $messages) {
                foreach ($messages as $message) {
                    flash($message)->warning();
                }
            }

            return back()->withErrors($exception->errors())->withInput();
        } catch (\Throwable $exception) {
            Log::error('Employee profile update failed', ['employee_id' => $employee->id, 'message' => $exception->getMessage()]);
            flash('Unable to update employee profile.')->error();

            return back()->withInput();
        }
    }

    /* ==========================================================
    UPDATE EMPLOYEE STATUS DETAILS
    ========================================================== */
    public function updateStatusDetails(UpdateEmployeeStatusDetailsRequest $request, Employee $employee): RedirectResponse
    {
        $this->employees->updateStatusDetails($employee, $request->validated());
        flash('Employee status details updated!')->success();

        return redirect()->route('employees.staff.show', $employee->id);
    }

    /* ==========================================================
        UPDATE 201 FILES
    ========================================================== */
    public function updateAssets(UpdateEmployeeAssetsRequest $request, Employee $employee): RedirectResponse
    {
        try {
            $this->employees->updateAssets($employee, $request->validated());
            flash('201 file updated successfully!')->success();
        } catch (\Throwable $exception) {
            Log::error('updateAssets error', ['employee_id' => $employee->id, 'message' => $exception->getMessage()]);
            flash('Something went wrong updating the 201 file.')->error();
        }

        return redirect()->route('employees.staff.show', $employee->id);
    }

    /* ==========================================================
        ADD HISTORY
    ========================================================== */
    public function storeHistory(EmployeeHistoryRequest $request, Employee $employee): RedirectResponse
    {
        try {
            $this->histories->createViolation($employee, $request->validated());
            flash('Violation history added successfully!')->success();

            return redirect()->route('employees.staff.show', $employee->id);
        } catch (\Throwable $exception) {
            Log::error('Employee history store failed', ['employee_id' => $employee->id, 'message' => $exception->getMessage()]);
            flash('Unable to add violation history. Please try again.')->error();

            return back()->withInput();
        }
    }

    /* ==========================================================
        UPDATE HISTORY / IR CASE
    ========================================================== */
    public function updateHistory(EmployeeHistoryRequest $request, Employee $employee, int $historyId): RedirectResponse
    {
        try {
            $this->histories->updateViolation($employee, $historyId, $request->validated());
            flash('Violation history updated successfully!')->success();

            return redirect()->route('employees.staff.show', $employee->id);
        } catch (\Throwable $exception) {
            Log::error('Employee history update failed', ['employee_id' => $employee->id, 'history_id' => $historyId, 'message' => $exception->getMessage()]);
            flash('Unable to update violation history. Please try again.')->error();

            return back()->withInput();
        }
    }

    /* ==========================================================
        ADD ATTACHMENT
    ========================================================== */
    public function storeAttachment(StoreEmployeeAttachmentRequest $request, Employee $employee): RedirectResponse
    {
        try {
            $file = $request->file('attachment');
            abort_if($file === null, 422);
            $this->attachments->store($employee, $file);
            flash('Attachment uploaded!')->success();

            return redirect()->route('employees.staff.show', $employee->id);
        } catch (\Throwable $exception) {
            Log::error('Employee attachment upload failed', ['employee_id' => $employee->id, 'message' => $exception->getMessage()]);
            flash('Unable to upload attachment.')->error();

            return back()->withInput();
        }
    }

    public function downloadAttachment(Employee $employee, int $attachment): BinaryFileResponse
    {
        $att = $employee->attachments()->findOrFail($attachment);

        abort_unless(Storage::disk('local')->exists($att->file_path), 404);

        $disk = Storage::disk('local');

        return response()->download($disk->path($att->file_path), $att->file_name);
    }

    /* ==========================================================
        DELETE ATTACHMENT
    ========================================================== */
    public function destroyAttachment(Employee $employee, int $attachmentId): RedirectResponse
    {
        try {
            $this->attachments->delete($employee, $attachmentId);
            flash('Attachment removed!')->success();
        } catch (\Throwable $exception) {
            Log::error('Employee attachment delete failed', ['employee_id' => $employee->id, 'attachment_id' => $attachmentId, 'message' => $exception->getMessage()]);
            flash('Unable to remove attachment.')->error();
        }

        return redirect()->route('employees.staff.show', $employee->id);
    }

    /* ==========================================================
        DELETE HISTORY
    ========================================================== */
    public function destroyHistory(Employee $employee, int $historyId): RedirectResponse
    {
        try {
            $this->histories->delete($employee, $historyId);
            flash('History removed successfully!')->success();
        } catch (\Throwable $exception) {
            Log::error('Employee history delete failed', ['employee_id' => $employee->id, 'history_id' => $historyId, 'message' => $exception->getMessage()]);
            flash('Unable to remove history.')->error();
        }

        return redirect()->route('employees.staff.show', $employee->id);
    }

    /* ==========================================================
        DELETE EMPLOYEE
    ========================================================== */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->employees->delete(Employee::query()->findOrFail($id));

            return back()->with('success', 'Employee deleted successfully.');
        } catch (\Throwable $exception) {
            Log::error('Employee delete failed', ['employee_id' => $id, 'message' => $exception->getMessage()]);

            return back()->with('error', 'Unable to delete employee.');
        }
    }

    /* ==========================================================
        PDF
    ========================================================== */
    public function print201($id)
    {
        $employee = Employee::with(['asset', 'histories', 'attachments', 'position', 'department'])
            ->findOrFail($id);

        $profileDataUri = null;
        if ($employee->asset?->profile_picture && Storage::disk('local')->exists($employee->asset->profile_picture)) {
            $profileDataUri = 'data:'.(Storage::disk('local')->mimeType($employee->asset->profile_picture) ?: 'image/jpeg').';base64,'.base64_encode(Storage::disk('local')->get($employee->asset->profile_picture));
        }

        $pdf = Pdf::loadView('hr_department.employees.modals._employee_201_pdf', compact('employee', 'profileDataUri'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream($employee->employee_id.'_201.pdf');
    }

    /* ==========================================================
        GET POSITIONS BY DEPARTMENT (for dynamic dropdown)
    ========================================================== */
    public function getPositions($id)
    {
        $positions = Position::where('department_id', $id)->get();

        return response()->json($positions);
    }

    /* ==========================================================
        CHECK PERMANENT ID (for AJAX validation)
    ========================================================== */
    public function checkPermanentId(Request $request)
    {
        $value = trim((string) $request->query('value', ''));
        $ignoreId = $request->query('ignore_id'); // for edit mode

        if ($value === '') {
            return response()->json([
                'exists' => false,
                'message' => '',
            ]);
        }

        $q = Employee::query()->where('employee_id_permanent', $value);

        if (! empty($ignoreId)) {
            $q->where('id', '!=', $ignoreId);
        }

        $exists = $q->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'ID already exists in database.' : 'ID is available.',
        ]);
    }
}
