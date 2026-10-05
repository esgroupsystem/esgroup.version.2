<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreEmployeeAttachmentRequest;
use App\Models\Employee;
use App\Services\HR\EmployeeAttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

/**
 * Attachments on the employee 201 profile.
 */
final class EmployeeAttachmentController extends Controller
{
    public function __construct(
        private readonly EmployeeAttachmentService $attachments,
    ) {}

    public function store(StoreEmployeeAttachmentRequest $request, Employee $employee): RedirectResponse
    {
        try {
            $file = $request->file('attachment');
            abort_if($file === null, 422);
            $this->attachments->store($employee, $file);
            flash('Attachment uploaded!')->success();

            return redirect()->route('employees.staff.show', $employee->id);
        } catch (Throwable $exception) {
            Log::error('Employee attachment upload failed', ['employee_id' => $employee->id, 'message' => $exception->getMessage()]);
            flash('Unable to upload attachment.')->error();

            return back()->withInput();
        }
    }

    public function download(Employee $employee, int $attachment): BinaryFileResponse
    {
        ['path' => $path, 'name' => $name] = $this->attachments->download($employee, $attachment);

        return response()->download($path, $name);
    }

    public function destroy(Employee $employee, int $attachment): RedirectResponse
    {
        try {
            $this->attachments->delete($employee, $attachment);
            flash('Attachment removed!')->success();
        } catch (Throwable $exception) {
            Log::error('Employee attachment delete failed', ['employee_id' => $employee->id, 'attachment_id' => $attachment, 'message' => $exception->getMessage()]);
            flash('Unable to remove attachment.')->error();
        }

        return redirect()->route('employees.staff.show', $employee->id);
    }
}
