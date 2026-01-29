<?php

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLog;
use App\Models\Position;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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

        if (! $request->ajax()) {
            session(['employees_back_url' => url()->full()]);
        }

        $query = Employee::with(['department', 'position'])
            ->orderBy('full_name', 'asc');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->search}%")
                    ->orWhere('employee_id', 'like', "%{$request->search}%");
            });
        }

        $employees = $query->paginate(20)->withQueryString();

        if ($request->ajax()) {
            return view('hr_department.employees.table', compact('employees'))->render();
        }

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
        $deptMap = $departments->pluck('name', 'id');
        $posMap = Position::pluck('title', 'id');

        $logs = $employee->logs()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(10) 
            ->withQueryString();

        $age = '—';
        if (! empty($employee->date_of_birth)) {
            $age = Carbon::parse($employee->date_of_birth)->age.' yrs old';
        }

        $tenure = '—';
        if (! empty($employee->date_hired)) {
            $hired = Carbon::parse($employee->date_hired)->startOfDay();
            $today = now()->startOfDay();
            if ($hired->lte($today)) {
                $diff = $hired->diff($today);
                $parts = [];
                if ($diff->y > 0) {
                    $parts[] = $diff->y.' yr'.($diff->y > 1 ? 's' : '');
                }
                if ($diff->m > 0) {
                    $parts[] = $diff->m.' mo'.($diff->m > 1 ? 's' : '');
                }
                if ($diff->y === 0 && $diff->m === 0) {
                    $parts[] = $diff->d.' day'.($diff->d > 1 ? 's' : '');
                }
                $tenure = implode(' ', $parts);
            } else {
                $tenure = 'Not started';
            }
        }

        return view('hr_department.employee_profile', compact(
            'employee', 'departments', 'tenure', 'age', 'deptMap', 'posMap', 'logs'
        ));
    }

    /* ==========================================================
        CREATE EMPLOYEE
    ========================================================== */

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'department_id' => 'nullable|exists:departments,id',
                'position_id' => 'nullable|exists:positions,id',
                'email' => 'nullable|email|max:255',
                'phone_number' => 'nullable|digits:11|regex:/^[0-9]*$/',
                'company' => 'required|in:Jell Transport,ES Transport,Earthstar Transport,Kellen Transport',
                'garage' => 'required|in:Mirasol,Balintawak,Gonzales',
            ]);

            $employee = DB::transaction(function () use ($validated) {

                $lastId = Employee::lockForUpdate()->max('id') ?? 0;
                $employee_id = 'EMP-'.str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

                return Employee::create([
                    'employee_id' => $employee_id,
                    'full_name' => $validated['full_name'],
                    'department_id' => $validated['department_id'] ?? null,
                    'position_id' => $validated['position_id'] ?? null,
                    'email' => $validated['email'] ?? null,
                    'phone_number' => $validated['phone_number'] ?? null,
                    'company' => $validated['company'],
                    'garage' => $validated['garage'],
                ]);
            });

            $this->logEmployee($employee, 'created', [
                'employee_id' => $employee->employee_id,
                'full_name' => $employee->full_name,
            ]);

            flash('Employee added successfully!')->success();

            return redirect()->route('employees.staff.show', $employee->id);

        } catch (ValidationException $e) {

            foreach ($e->errors() as $messages) {
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
                'status' => 'required|string|in:Active,Inactive,Suspended,Terminated,Terminated(due to AWOL),End of Contract,Retrench,Retired,Resigned',
                'date_hired' => 'nullable|date',
                'company' => 'required|in:Jell Transport,ES Transport,Kellen Transport,Earthstar Transport',
                'department_id' => 'nullable|exists:departments,id',
                'position_id' => 'nullable|exists:positions,id',
                'email' => 'nullable|email|max:255',
                'phone_number' => 'nullable|digits:11|regex:/^[0-9]*$/',
                'garage' => 'required|in:Mirasol,Balintawak,Gonzales',
                'date_of_birth' => 'nullable|date',
                'address_1' => 'nullable|string|max:255',
                'address_2' => 'nullable|string|max:255',
                'emergency_name' => 'nullable|string|max:255',
                'emergency_contact' => 'nullable|digits:11',
            ]);

            // BEFORE must be captured BEFORE update
            $before = $employee->only(array_keys($validated));

            // normalize date fields (optional but nice)
            if (array_key_exists('date_hired', $validated)) {
                $validated['date_hired'] = $this->normalizeDate($validated['date_hired']);
            }
            if (array_key_exists('date_of_birth', $validated)) {
                $validated['date_of_birth'] = $this->normalizeDate($validated['date_of_birth']);
            }

            $employee->update($validated);

            $after = $employee->fresh()->only(array_keys($validated));
            $changed = $this->diffChanges($before, $after);

            $this->logEmployee($employee, 'updated_profile', [
                'changed' => $changed,
            ]);

            flash('Employee profile updated successfully!')->success();

            return redirect()->route('employees.staff.show', $employee->id);

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
    UPDATE EMPLOYEE STATUS DETAILS
    ========================================================== */
    public function updateStatusDetails(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'date_resigned' => 'nullable|date',
            'last_duty' => 'nullable|date',
            'clearance_date' => 'nullable|date',
            'last_pay_status' => 'nullable|in:Not released,Released',
            'last_pay_date' => 'nullable|date',
        ]);

        $before = $employee->only(array_keys($validated));

        // normalize dates for clean diff
        foreach (['date_resigned', 'last_duty', 'clearance_date', 'last_pay_date'] as $df) {
            if (array_key_exists($df, $validated)) {
                $validated[$df] = $this->normalizeDate($validated[$df]);
            }
        }

        $employee->update($validated);

        $after = $employee->fresh()->only(array_keys($validated));
        $changed = $this->diffChanges($before, $after);

        $this->logEmployee($employee, 'updated_status_details', [
            'changed' => $changed,
        ]);

        flash('Employee status details updated!')->success();

        return redirect()->route('employees.staff.show', $employee->id);
    }

    /* ==========================================================
        UPDATE 201 FILES
    ========================================================== */
    public function updateAssets(Request $request, Employee $employee)
    {
        try {
            DB::beginTransaction();

            $asset = $employee->asset ?? $employee->asset()->create([]);

            // BEFORE snapshot
            $before = [
                'sss_number' => $asset->sss_number,
                'tin_number' => $asset->tin_number,
                'philhealth_number' => $asset->philhealth_number,
                'pagibig_number' => $asset->pagibig_number,
                'profile_picture' => $asset->profile_picture,
                'birth_certificate' => $asset->birth_certificate,
                'resume' => $asset->resume,
                'contract' => $asset->contract,
            ];

            // --- files ---
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $safeName = str_replace(' ', '_', $employee->full_name.'_profile_'.rand(1000, 9999).'.'.$file->extension());
                $path = $file->storeAs('employees/profile', $safeName, 'public');

                if ($asset->profile_picture) {
                    Storage::disk('public')->delete($asset->profile_picture);
                }

                $asset->profile_picture = $path;
                $asset->profile_picture_updated_at = now(); // ✅
            }

            foreach (['birth_certificate', 'resume', 'contract'] as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $safeName = str_replace(' ', '_', $employee->full_name."_{$field}_".rand(1000, 9999).'.'.$file->extension());
                    $path = $file->storeAs("employees/{$field}", $safeName, 'public');

                    if ($asset->{$field}) {
                        Storage::disk('public')->delete($asset->{$field});
                    }

                    $asset->{$field} = $path;
                    $asset->{$field.'_updated_at'} = now(); // ✅ birth_certificate_updated_at etc
                }
            }

            // --- numbers ---
            $incoming = [
                'sss_number' => $request->sss_number,
                'tin_number' => $request->tin_number,
                'philhealth_number' => $request->philhealth_number,
                'pagibig_number' => $request->pagibig_number,
            ];

            foreach ($incoming as $k => $v) {
                $old = $asset->{$k};
                $new = ($v === '') ? null : $v;

                if ($old != $new) {
                    $asset->{$k} = $new;
                    $asset->{str_replace('_number', '', $k).'_updated_at'} = now();
                    // sss_number -> sss_updated_at
                    // tin_number -> tin_updated_at
                }
            }

            $asset->save();
            DB::commit();

            // AFTER snapshot for logs
            $after = [
                'sss_number' => $asset->sss_number,
                'tin_number' => $asset->tin_number,
                'philhealth_number' => $asset->philhealth_number,
                'pagibig_number' => $asset->pagibig_number,
                'profile_picture' => $asset->profile_picture,
                'birth_certificate' => $asset->birth_certificate,
                'resume' => $asset->resume,
                'contract' => $asset->contract,
            ];

            $changed = $this->diffChanges($before, $after);

            if (! empty($changed)) {
                $this->logEmployee($employee, 'updated_201_file', ['changed' => $changed]);
            }

            flash('201 file updated successfully!')->success();

            return redirect()->route('employees.staff.show', $employee->id);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("updateAssets error: {$e->getMessage()}");
            flash('Something went wrong updating the 201 file.')->error();

            return redirect()->route('employees.staff.show', $employee->id);
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

            $this->logEmployee($employee, 'added_history', [
                'title' => $request->title,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            flash('History added!')->success();

            return redirect()->route('employees.staff.show', $employee->id);

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

            $this->logEmployee($employee, 'uploaded_attachment', [
                'file_name' => $newName,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            flash('Attachment uploaded!')->success();

            return redirect()->route('employees.staff.show', $employee->id);

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

            $this->logEmployee($employee, 'deleted_attachment', [
                'file_name' => $att->file_name,
                'file_path' => $att->file_path,
            ]);

            $att->delete();

            flash('Attachment removed!')->success();

            return redirect()->route('employees.staff.show', $employee->id);

        } catch (\Throwable $e) {
            flash('Unable to remove attachment.')->error();

            return redirect()->route('employees.staff.show', $employee->id);
        }
    }

    /* ==========================================================
        DELETE HISTORY
    ========================================================== */
    public function destroyHistory(Employee $employee, $historyId)
    {
        try {
            $history = $employee->histories()->findOrFail($historyId);

            $this->logEmployee($employee, 'removed_history', [
                'title' => $history->title,
                'start_date' => $history->start_date,
                'end_date' => $history->end_date,
            ]);

            $history->delete();

            flash('History removed!')->success();

            return redirect()->route('employees.staff.show', $employee->id);

        } catch (\Throwable $e) {
            flash('Unable to remove history.')->error();

            return redirect()->route('employees.staff.show', $employee->id);
        }
    }

    /* ==========================================================
        DELETE EMPLOYEE
    ========================================================== */
    public function destroy(Employee $employee)
    {
        try {
            $this->logEmployee($employee, 'deleted_employee', [
                'employee_id' => $employee->employee_id,
                'full_name' => $employee->full_name,
            ]);

            $employee->delete();

            flash('Employee deleted!')->success();

            return redirect()->route('employees.staff.show', $employee->id);

        } catch (\Throwable $e) {
            flash('Unable to delete employee.')->error();

            return redirect()->route('employees.staff.show', $employee->id);
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

    public function getPositions($id)
    {
        $positions = Position::where('department_id', $id)->get();

        return response()->json($positions);
    }

    private function logEmployee(Employee $employee, string $action, array $meta = []): void
    {
        EmployeeLog::create([
            'employee_id' => $employee->id,
            'action' => $action,
            'meta' => $meta ?: null,
            'user_id' => auth()->id(),
        ]);
    }

    private function diffChanges(array $before, array $after): array
    {
        $changed = [];

        foreach ($after as $k => $v) {
            $old = $before[$k] ?? null;

            // normalize null / empty
            $oldNorm = ($old === '') ? null : $old;
            $newNorm = ($v === '') ? null : $v;

            if ($oldNorm != $newNorm) {
                $changed[$k] = [
                    'from' => $oldNorm,
                    'to' => $newNorm,
                ];
            }
        }

        return $changed;
    }

    private function normalizeDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return $value; // fallback
        }
    }
}
