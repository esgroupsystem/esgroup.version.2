<?php

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    /* ==========================================================
        LISTING / SEARCH
    ========================================================== */
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'position'])
            ->orderBy('employee_id', 'asc');

        // AJAX SEARCH
        if ($request->ajax()) {
            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('full_name', 'like', "%{$request->search}%")
                        ->orWhere('employee_id', 'like', "%{$request->search}%");
                });
            }

            $employees = $query->paginate(10);

            return view('hr_department.employees.table', compact('employees'))->render();
        }

        $employees = $query->paginate(10);
        $departments = Department::with('positions')->get();

        return view('hr_department.index', compact('employees', 'departments'));
    }

    /* ==========================================================
        SHOW PROFILE
    ========================================================== */
    public function show(Employee $employee)
    {
        $employee->load([
            'asset',
            'histories' => fn ($q) => $q->orderBy('start_date', 'desc'),
            'attachments',
            'position',
            'department',
            'department.positions',
        ]);

        $departments = Department::with('positions')->get();

        return view('hr_department.employee_profile', compact('employee', 'departments'));
    }

    /* ==========================================================
        CREATE EMPLOYEE
    ========================================================== */
    public function store(Request $request)
    {
        try {

            $request->validate([
                'full_name' => 'required|string|max:255',
                'department_id' => 'nullable|exists:departments,id',
                'position_id' => 'nullable|exists:positions,id',
                'email' => 'nullable|email|max:255',
                'phone_number' => 'nullable|digits:11|regex:/^[0-9]*$/',
                'company' => 'required|in:Jell Transport,ES Transport,Earthstar Transport,Kellen Transport',
                'garage' => 'required|in:Mirasol,Balintawak',
            ]);

            $lastId = Employee::latest()->value('id') ?? 0;
            $employee_id = 'EMP-'.str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            Employee::create([
                'employee_id' => $employee_id,
                'full_name' => $request->full_name,
                'department_id' => $request->department_id,
                'position_id' => $request->position_id,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'company' => $request->company,
                'garage' => $request->garage,
            ]);

            flash('Employee added successfully!')->success();

            return back();

        } catch (ValidationException $e) {

            // SHOW EXACT ERRORS IN FLASH
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $msg) {
                    flash($msg)->warning();
                }
            }

            Log::warning('Employee validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);

            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {

            Log::error('Error adding employee', [
                'message' => $e->getMessage(),
            ]);

            flash('Something went wrong while adding the employee.')->error();

            return back()->withInput();
        }
    }

    /* ==========================================================
        UPDATE EMPLOYEE PROFILE
    ========================================================== */
    public function update(Request $request, Employee $employee)
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'status' => 'required|string|in:Active,Inactive,Suspended,Terminated,Retrench,Retired,Resigned',
                'date_hired' => 'nullable|date',
                'company' => 'required|in:Jell Transport,ES Transport,Kellen Transport,Earthstar Transport',
                'department_id' => 'nullable|exists:departments,id',
                'position_id' => 'nullable|exists:positions,id',
                'email' => 'nullable|email|max:255',
                'phone_number' => 'nullable|digits:11|regex:/^[0-9]*$/',
                'garage' => 'required|in:Mirasol,Balintawak',
            ]);

            $employee->update($validated);

            flash('Employee profile updated successfully!')->success();

            return back();

        } catch (ValidationException $e) {

            foreach ($e->errors() as $msgList) {
                foreach ($msgList as $msg) {
                    flash($msg)->warning();
                }
            }

            return back()->withErrors($e->validator)->withInput();
        }
    }

    /* ==========================================================
        UPDATE 201 FILES
    ========================================================== */
    public function updateAssets(Request $request, Employee $employee)
    {
        try {
            DB::beginTransaction();

            $asset = $employee->asset ?? $employee->asset()->create([]);

            // PROFILE PICTURE
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');

                $safeName = str_replace(' ', '_', $employee->full_name.'_profile_'.rand(1000, 9999).'.'.$file->extension());
                $path = $file->storeAs('employees/profile', $safeName, 'public');

                if ($asset->profile_picture) {
                    Storage::disk('public')->delete($asset->profile_picture);
                }

                $asset->profile_picture = $path;
            }

            // OTHER FILES
            foreach (['birth_certificate', 'resume', 'contract'] as $field) {

                if ($request->hasFile($field)) {
                    $file = $request->file($field);

                    $safeName = str_replace(' ', '_', $employee->full_name."_{$field}_".rand(1000, 9999).'.'.$file->extension());
                    $path = $file->storeAs("employees/{$field}", $safeName, 'public');

                    if ($asset->{$field}) {
                        Storage::disk('public')->delete($asset->{$field});
                    }

                    $asset->{$field} = $path;
                }
            }

            $asset->fill([
                'sss_number' => $request->sss_number,
                'tin_number' => $request->tin_number,
                'philhealth_number' => $request->philhealth_number,
                'pagibig_number' => $request->pagibig_number,
            ])->save();

            DB::commit();

            flash('201 file updated successfully!')->success();

            return back();

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("updateAssets error: {$e->getMessage()}");
            flash('Something went wrong updating the 201 file.')->error();

            return back();
        }
    }

    /* ==========================================================
        ADD HISTORY
    ========================================================== */
    public function storeHistory(Request $request, Employee $employee)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
            ]);

            $employee->histories()->create($request->all());

            flash('History added!')->success();

            return back();

        } catch (ValidationException $e) {
            foreach ($e->errors() as $msgList) {
                foreach ($msgList as $msg) {
                    flash($msg)->warning();
                }
            }

            return back()->withErrors($e->validator)->withInput();
        }
    }

    /* ==========================================================
        ADD ATTACHMENT
    ========================================================== */
    public function storeAttachment(Request $request, Employee $employee)
    {
        try {
            $request->validate([
                'attachment' => 'required|file|max:10240',
            ]);

            $file = $request->file('attachment');

            $cleanName = strtolower(str_replace(' ', '_', $employee->full_name));
            $original = strtolower(str_replace([' ', '.', '-'], '_', $file->getClientOriginalName()));

            $newName = "{$cleanName}_{$original}_".uniqid().'.'.$file->getClientOriginalExtension();

            $path = $file->storeAs('employees/attachments', $newName, 'public');

            $employee->attachments()->create([
                'file_name' => $newName,
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            flash('Attachment uploaded!')->success();

            return back();

        } catch (ValidationException $e) {
            foreach ($e->errors() as $errorList) {
                foreach ($errorList as $msg) {
                    flash($msg)->warning();
                }
            }

            return back()->withInput();
        }
    }

    /* ==========================================================
        DELETE ATTACHMENT
    ========================================================== */
    public function destroyAttachment(Employee $employee, $attachmentId)
    {
        try {
            $att = $employee->attachments()->findOrFail($attachmentId);
            Storage::disk('public')->delete($att->file_path);
            $att->delete();

            flash('Attachment removed!')->success();

            return back();

        } catch (\Throwable $e) {
            flash('Unable to remove attachment.')->error();

            return back();
        }
    }

    /* ==========================================================
        DELETE HISTORY
    ========================================================== */
    public function destroyHistory(Employee $employee, $historyId)
    {
        try {
            $employee->histories()->findOrFail($historyId)->delete();
            flash('History removed!')->success();

            return back();

        } catch (\Throwable $e) {
            flash('Unable to remove history.')->error();

            return back();
        }
    }

    /* ==========================================================
        DELETE EMPLOYEE
    ========================================================== */
    public function destroy(Employee $employee)
    {
        try {
            $employee->delete();
            flash('Employee deleted!')->success();

            return back();
        } catch (\Throwable $e) {
            flash('Unable to delete employee.')->error();

            return back();
        }
    }

    /* ==========================================================
        PDF
    ========================================================== */
    public function print201($id)
    {
        $employee = Employee::with(['asset', 'histories', 'attachments', 'position', 'department'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('hr_department.employee_201_pdf', compact('employee'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream($employee->employee_id.'_201.pdf');
    }
}
