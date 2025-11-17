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
    public function index()
    {
        $employees = Employee::with(['department', 'position', 'asset'])->latest()->paginate(10);
        $departments = Department::with('positions')->get();

        return view('hr_department.index', compact('employees', 'departments'));
    }

    public function show(Employee $employee)
    {
        
        $employee->load(['asset', 'histories' => function ($q) {
            $q->orderBy('start_date', 'desc');
        }, 'attachments', 'position', 'department', 'department.positions']);

        $departments = Department::with('positions')->get();

        return view('hr_department.employee_profile', compact('employee', 'departments'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'full_name' => 'required|string|max:255',
                'department_id' => 'nullable|exists:departments,id',
                'position_id' => 'nullable|exists:positions,id',
                'email' => 'nullable|email|max:255',
                'phone_number' => 'nullable|string|max:20',
                'company' => 'required|in:Jell Transport,ES Transport,Earthstar Transport, Kellen Transport',
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
            ]);

            flash('Employee added successfully!')->success();

            return back();
        } catch (ValidationException $e) {
            flash('Validation failed. Please check the input fields.')->warning();

            Log::warning('Employee validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);

            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Error adding employee', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            flash('Something went wrong while adding the employee.')->error();

            return back()->withInput();
        }
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'status' => 'nullable|string',
            'date_hired' => 'nullable|date',
            'company' => 'required|in:MIRASOL,BALINTAWAK',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        $employee->update($validated);

        flash('Employee profile updated successfully!')->success();

        return back();
    }

    public function updateAssets(Request $request, Employee $employee)
    {
        try {
            DB::beginTransaction();

            $asset = $employee->asset ?? $employee->asset()->create([]);

            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');

                $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $ext = $file->getClientOriginalExtension();
                $rand = rand(1000, 9999);

                $safeName = str_replace(' ', '_', $employee->full_name.'_'.$original.'_profile-'.$rand.'.'.$ext);

                $path = $file->storeAs('employees/profile', $safeName, 'public');

                if ($asset->profile_picture) {
                    Storage::disk('public')->delete($asset->profile_picture);
                }
                $asset->profile_picture = $path;
            }

            foreach (['birth_certificate', 'resume', 'contract'] as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);

                    $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $ext = $file->getClientOriginalExtension();
                    $rand = rand(1000, 9999);

                    $safeName = str_replace(
                        ' ',
                        '_',
                        $employee->full_name.'_'.$original.'_'.$field.'-'.$rand.'.'.$ext
                    );

                    $path = $file->storeAs("employees/{$field}", $safeName, 'public');

                    if ($asset->{$field}) {
                        Storage::disk('public')->delete($asset->{$field});
                    }

                    $asset->{$field} = $path;
                }
            }

            $asset->sss_number = $request->input('sss_number');
            $asset->tin_number = $request->input('tin_number');
            $asset->philhealth_number = $request->input('philhealth_number');
            $asset->pagibig_number = $request->input('pagibig_number');

            $asset->save();

            DB::commit();
            flash('201 file updated successfully.')->success();

            return back();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Employee updateAssets error: '.$e->getMessage());
            flash('Something went wrong while updating.')->error();

            return back();
        }
    }

    public function storeHistory(Request $request, Employee $employee)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        try {
            $employee->histories()->create($request->only(['title', 'description', 'start_date', 'end_date']));
            flash('History added.')->success();

            return back();
        } catch (\Throwable $e) {
            \Log::error('storeHistory error: '.$e->getMessage());
            flash('Unable to add history.')->error();

            return back();
        }
    }


    public function storeAttachment(Request $request, Employee $employee)
    {
        $request->validate([
            'attachment' => 'required|file|max:10240',
        ]);

        try {
            $file = $request->file('attachment');

            // CLEAN filename format
            $cleanName = str_replace(' ', '_', strtolower($employee->full_name));
            $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $cleanOriginal = str_replace([' ', '.', '-'], '_', strtolower($original));

            $newName = $cleanName.'_'.$cleanOriginal.'_'.uniqid().'.'.
                $file->getClientOriginalExtension();

            $path = $file->storeAs('employees/attachments', $newName, 'public');

            $employee->attachments()->create([
                'file_name' => $newName,
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            flash('Attachment uploaded.')->success();

            return back();
        } catch (\Throwable $e) {
            Log::error('storeAttachment error: '.$e->getMessage());
            flash('Unable to upload attachment.')->error();

            return back();
        }
    }

    // print 201 as PDF
    public function print201($id)
    {
        $employee = Employee::with(['asset', 'histories', 'attachments', 'position', 'department'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('hr_department.employee_201_pdf', compact('employee'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream($employee->employee_id.'_201.pdf');
    }

    // optionally: delete attachment
    public function destroyAttachment(Employee $employee, $attachmentId)
    {
        try {
            $att = $employee->attachments()->findOrFail($attachmentId);
            Storage::disk('public')->delete($att->file_path);
            $att->delete();
            flash('Attachment removed.')->success();

            return back();
        } catch (\Throwable $e) {
            \Log::error('destroyAttachment error: '.$e->getMessage());
            flash('Unable to remove attachment.')->error();

            return back();
        }
    }

    // optionally: delete history
    public function destroyHistory(Employee $employee, $historyId)
    {
        try {
            $employee->histories()->findOrFail($historyId)->delete();
            flash('History removed.')->success();

            return back();
        } catch (\Throwable $e) {
            \Log::error('destroyHistory error: '.$e->getMessage());
            flash('Unable to remove history.')->error();

            return back();
        }
    }

    public function destroy(Employee $employee)
    {
        try {
            $employee->delete();
            flash('Employee deleted.')->success();

            return back();
        } catch (\Throwable $e) {
            \Log::error('Employee delete error: '.$e->getMessage());
            flash('Unable to delete employee.')->error();

            return back();
        }
    }
}
