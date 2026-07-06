<?php

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLog;
use App\Models\HrOffense;
use App\Models\Position;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    /* ==========================================================
        LISTING / SEARCH
    ========================================================== */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $query = Employee::query()
            ->with([
                'position',
                'department',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        | Uses your actual fields:
        | employee_id, employee_id_permanent, full_name, email, phone_number,
        | company, garage, position title, and department name.
        */
        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('employee_id_permanent', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('garage', 'like', "%{$search}%")
                    ->orWhereHas('position', function ($positionQuery) use ($search) {
                        $positionQuery->where('title', 'like', "%{$search}%");
                    })
                    ->orWhereHas('department', function ($departmentQuery) use ($search) {
                        $departmentQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('company')) {
            $query->where('company', $request->input('company'));
        }

        if ($request->filled('garage')) {
            $query->where('garage', $request->input('garage'));
        }

        /*
        |--------------------------------------------------------------------------
        | Paginated Employees
        |--------------------------------------------------------------------------
        */
        $employees = $query
            ->orderBy('full_name')
            ->paginate($perPage)
            ->appends($request->query());

        /*
        |--------------------------------------------------------------------------
        | Needed By Add Employee Modal
        |--------------------------------------------------------------------------
        | This fixes:
        | Undefined variable $departments
        */
        $departments = Department::with([
            'positions' => function ($query) {
                $query->orderBy('title');
            },
        ])
            ->orderBy('name')
            ->get();

        $positions = Position::query()
            ->orderBy('title')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Header / Dashboard Statistics
        |--------------------------------------------------------------------------
        */
        $employeeStats = [
            'total' => Employee::count(),

            'active' => Employee::whereIn('status', [
                'Active',
                'Active(Re-Entry)',
            ])->count(),

            'inactive' => Employee::whereIn('status', [
                'Inactive',
                'Resigned',
                'Terminated',
                'Terminated(due to AWOL)',
                'End of Contract',
                'Retrench',
                'Retired',
            ])->count(),

            'suspended' => Employee::where('status', 'Suspended')->count(),

            'companies' => Employee::whereNotNull('company')
                ->where('company', '!=', '')
                ->distinct()
                ->count('company'),

            'garages' => Employee::whereNotNull('garage')
                ->where('garage', '!=', '')
                ->distinct()
                ->count('garage'),
        ];

        /*
        |--------------------------------------------------------------------------
        | Filter Dropdown Data
        |--------------------------------------------------------------------------
        */
        $companies = Employee::query()
            ->whereNotNull('company')
            ->where('company', '!=', '')
            ->distinct()
            ->orderBy('company')
            ->pluck('company');

        $garages = Employee::query()
            ->whereNotNull('garage')
            ->where('garage', '!=', '')
            ->distinct()
            ->orderBy('garage')
            ->pluck('garage');

        $statusOptions = [
            'Active',
            'Active(Re-Entry)',
            'Suspended',
            'Inactive',
            'Terminated',
            'Terminated(due to AWOL)',
            'End of Contract',
            'Retrench',
            'Retired',
            'Resigned',
        ];

        return view('hr_department.employees.index', compact(
            'employees',
            'departments',
            'positions',
            'employeeStats',
            'companies',
            'garages',
            'statusOptions'
        ));
    }

    /* ==========================================================
        SHOW PROFILE
    ========================================================== */
    public function show(Employee $employee)
    {
        $employee->load([
            'asset',
            'histories' => fn ($q) => $q
                ->with('offense')
                ->orderByDesc('created_at'),
            'attachments',
            'position',
            'department',
            'department.positions',
        ]);

        $offenses = HrOffense::orderBy('section')->get();
        $departments = Department::with('positions')->get();
        $deptMap = $departments->pluck('name', 'id');
        $posMap = Position::pluck('title', 'id');

        $logs = $employee->logs()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(3)
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

        // ✅ Build “clean view data” (no more heavy PHP in Blade)
        $historyItems = $employee->histories->map(function ($h) {

            // dates
            $start = $h->start_date ? Carbon::parse($h->start_date) : null;
            $end = $h->end_date ? Carbon::parse($h->end_date) : null;
            $isPresent = empty($h->end_date);

            $rangeText = ($start ? $start->format('M d, Y') : '—').' • '.($end ? $end->format('M d, Y') : 'Present');

            $durationText = null;
            if ($start) {
                $durationText = $end ? $start->diffForHumans($end, true) : $start->diffForHumans(now(), true);
            }

            // actions (ensure array)
            $actions = $h->disciplinary_action;
            if (is_string($actions)) {
                $actions = $actions ? [$actions] : [];
            }
            if (! is_array($actions)) {
                $actions = [];
            }

            $hasSda = in_array('Salary Deduction Authorization', $actions, true);
            $hasSuspension = in_array('Suspension', $actions, true);

            // SDA
            $sdaTotal = ! is_null($h->sda_amount) ? (float) $h->sda_amount : null;
            $perCutoffAmount = $h->sda_terms ? (float) $h->sda_terms : null; // ✅ you decided this is per cutoff amount now

            $sdaStart = $h->sda_start_date ? Carbon::parse($h->sda_start_date) : null;
            $sdaEnd = $h->sda_end_date ? Carbon::parse($h->sda_end_date) : null;

            $sdaRangeText = null;
            if ($sdaStart || $sdaEnd) {
                $sdaRangeText = ($sdaStart ? $sdaStart->format('M d, Y') : '—').' • '.($sdaEnd ? $sdaEnd->format('M d, Y') : 'Ongoing');
            }

            // months (no decimals)
            $monthsDuration = null;
            if ($hasSda && $sdaTotal !== null && $perCutoffAmount && $perCutoffAmount > 0) {
                $totalCutoffs = $sdaTotal / $perCutoffAmount;
                $monthsDuration = (int) round($totalCutoffs / 2); // 10 & 25 cutoff = 2 per month
            }

            // Suspension dates
            $susStart = $h->suspension_start_date ? Carbon::parse($h->suspension_start_date) : null;
            $susEnd = $h->suspension_end_date ? Carbon::parse($h->suspension_end_date) : null;

            $susRangeText = null;
            if ($susStart || $susEnd) {
                $susRangeText = ($susStart ? $susStart->format('M d, Y') : '—').' • '.($susEnd ? $susEnd->format('M d, Y') : 'Ongoing');
            }

            return [
                'model' => $h, // keep original model (for id, description, etc.)
                'title' => $h->title,
                'is_present' => $isPresent,
                'range_text' => $rangeText,
                'duration_text' => $durationText,

                'offense_section' => ($h->title === 'Violations' && $h->offense) ? $h->offense->section : null,

                'actions' => $actions,
                'has_sda' => $hasSda,
                'has_suspension' => $hasSuspension,

                'sda_total' => $sdaTotal,
                'per_cutoff_amount' => $hasSda ? $perCutoffAmount : null,
                'months_duration' => $monthsDuration,
                'sda_range_text' => $hasSda ? $sdaRangeText : null,

                'sus_range_text' => $hasSuspension ? $susRangeText : null,
            ];
        });

        $groupedIrHistories = $employee->histories
            ->where('title', 'Violations')
            ->groupBy(function ($item) {
                return $item->ir_number ?: 'NO-IR';
            })
            ->map(function ($records, $irNumber) {

                $first = $records->first();

                $actions = $first->disciplinary_action;

                if (is_string($actions)) {
                    $actions = $actions ? [$actions] : [];
                }

                if (! is_array($actions)) {
                    $actions = [];
                }

                return [
                    'ir_number' => $irNumber,
                    'records' => $records,
                    'count' => $records->count(),
                    'actions' => $actions,
                    'first_record' => $first,
                ];
            });

        return view('hr_department.employees.modals._employee_profile', compact(
            'employee',
            'departments',
            'tenure',
            'age',
            'deptMap',
            'posMap',
            'logs',
            'offenses',
            'historyItems',
            'groupedIrHistories'
        ));
    }

    /* ==========================================================
        CREATE EMPLOYEE
    ========================================================== */

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id_permanent' => 'nullable|digits_between:1,10|unique:employees,employee_id_permanent',
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
                    'employee_id_permanent' => $validated['employee_id_permanent'] ?? null,
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

            return redirect()->route('employees.staff.index');

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
                'employee_id_permanent' => 'nullable|digits_between:1,10|unique:employees,employee_id_permanent,'.$employee->id,
                'full_name' => 'required|string|max:255',
                'status' => 'required|string|in:Active,Active(Re-Entry),Inactive,Suspended,Terminated,Terminated(due to AWOL),End of Contract,Retrench,Retired,Resigned',
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
                'remove_profile_picture' => 'nullable|boolean',
                'profile_picture_cropped' => 'nullable|string',
                'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            $employeeData = collect($validated)->only([
                'employee_id_permanent',
                'full_name',
                'status',
                'date_hired',
                'company',
                'department_id',
                'position_id',
                'email',
                'phone_number',
                'garage',
                'date_of_birth',
                'address_1',
                'address_2',
                'emergency_name',
                'emergency_contact',
            ])->toArray();

            $before = $employee->only(array_keys($employeeData));

            if (array_key_exists('date_hired', $employeeData)) {
                $employeeData['date_hired'] = $this->normalizeDate($employeeData['date_hired']);
            }
            if (array_key_exists('date_of_birth', $employeeData)) {
                $employeeData['date_of_birth'] = $this->normalizeDate($employeeData['date_of_birth']);
            }

            $employee->update($employeeData);

            $after = $employee->fresh()->only(array_keys($employeeData));
            $changed = $this->diffChanges($before, $after);

            $asset = $employee->asset ?? $employee->asset()->create([]);

            if ($request->boolean('remove_profile_picture')) {
                if ($asset->profile_picture) {
                    Storage::disk('public')->delete($asset->profile_picture);
                }
                $asset->profile_picture = null;
                $asset->save();

                $changed['profile_picture'] = [
                    'from' => 'existing',
                    'to' => null,
                ];
            }

            if ($request->filled('profile_picture_cropped')) {
                $dataUrl = (string) $request->input('profile_picture_cropped');

                if (preg_match('/^data:image\/\w+;base64,/', $dataUrl)) {
                    $data = substr($dataUrl, strpos($dataUrl, ',') + 1);
                    $data = base64_decode($data);

                    if ($data !== false) {
                        if ($asset->profile_picture) {
                            Storage::disk('public')->delete($asset->profile_picture);
                        }

                        $cleanName = strtolower(str_replace(' ', '_', $employee->full_name));
                        $permanentId = $employee->employee_id_permanent ?? $employee->id;
                        $fileName = "employees/{$cleanName}_{$permanentId}.jpg";

                        if ($asset->profile_picture) {
                            Storage::disk('public')->delete($asset->profile_picture);
                        }

                        Storage::disk('public')->put($fileName, $data);

                        $asset->profile_picture = $fileName;
                        $asset->save();

                        $changed['profile_picture'] = [
                            'from' => 'existing',
                            'to' => $fileName,
                        ];
                    }
                }
            }

            if ($request->hasFile('profile_picture') && ! $request->filled('profile_picture_cropped')) {
                $file = $request->file('profile_picture');

                if ($asset->profile_picture) {
                    Storage::disk('public')->delete($asset->profile_picture);
                }

                $fileName = 'employees/profile_'.$employee->id.'_'.time().'.'.$file->getClientOriginalExtension();
                $path = $file->storeAs('employees', basename($fileName), 'public');

                // store path with folder
                $asset->profile_picture = 'employees/'.basename($fileName);
                $asset->save();

                $changed['profile_picture'] = [
                    'from' => 'existing',
                    'to' => $asset->profile_picture,
                ];
            }

            // Log changes if any
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

            'type_of_status' => [
                'nullable',
                Rule::in([
                    'Resigned',
                    'Terminated',
                    'Terminated due to AWOL',
                    'Retrenched',
                ]),
            ],

            'last_duty' => 'nullable|date',
            'clearance_date' => 'nullable|date',
            'last_pay_status' => 'nullable|in:Not released,Released',
            'last_pay_date' => 'nullable|date',
        ]);

        $before = $employee->only(array_keys($validated));

        // Normalize dates for clean diff
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

            $request->validate([
                'sss_number' => ['nullable', 'string', 'max:50'],
                'tin_number' => ['nullable', 'string', 'max:50'],
                'philhealth_number' => ['nullable', 'string', 'max:50'],
                'pagibig_number' => ['nullable', 'string', 'max:50'],

                // manual dates
                'sss_updated_at' => ['nullable', 'date'],
                'tin_updated_at' => ['nullable', 'date'],
                'philhealth_updated_at' => ['nullable', 'date'],
                'pagibig_updated_at' => ['nullable', 'date'],
            ]);

            $asset = $employee->asset ?? $employee->asset()->create([]);

            // BEFORE snapshot
            $before = [
                'sss_number' => $asset->sss_number,
                'tin_number' => $asset->tin_number,
                'philhealth_number' => $asset->philhealth_number,
                'pagibig_number' => $asset->pagibig_number,
                'sss_updated_at' => $asset->sss_updated_at,
                'tin_updated_at' => $asset->tin_updated_at,
                'philhealth_updated_at' => $asset->philhealth_updated_at,
                'pagibig_updated_at' => $asset->pagibig_updated_at,
                'profile_picture' => $asset->profile_picture,
                'birth_certificate' => $asset->birth_certificate,
                'resume' => $asset->resume,
                'contract' => $asset->contract,
            ];

            // --- files --- (your existing code stays)
            // ...

            // --- numbers + manual dates ---
            $fields = [
                'sss_number' => 'sss_updated_at',
                'tin_number' => 'tin_updated_at',
                'philhealth_number' => 'philhealth_updated_at',
                'pagibig_number' => 'pagibig_updated_at',
            ];

            foreach ($fields as $numberField => $dateField) {
                $oldNumber = $asset->{$numberField};
                $newNumber = $request->input($numberField);
                $newNumber = ($newNumber === '' ? null : $newNumber);

                $manualDate = $request->input($dateField);
                $manualDate = $manualDate ? Carbon::parse($manualDate)->startOfDay() : null;

                $numberChanged = ($oldNumber != $newNumber);

                if ($numberChanged) {
                    $asset->{$numberField} = $newNumber;

                    // if user typed a date, use it; else now()
                    $asset->{$dateField} = $manualDate ?? now();
                } else {
                    // OPTIONAL: allow updating the date even if number didn't change
                    if ($manualDate) {
                        $asset->{$dateField} = $manualDate;
                    }
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
                'sss_updated_at' => $asset->sss_updated_at,
                'tin_updated_at' => $asset->tin_updated_at,
                'philhealth_updated_at' => $asset->philhealth_updated_at,
                'pagibig_updated_at' => $asset->pagibig_updated_at,
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
    public function storeHistory(Request $request, Employee $employee): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],

                'ir_number' => ['required', 'string', 'max:255'],

                'offense_id' => ['required', 'array', 'min:1'],
                'offense_id.*' => ['required', 'integer', 'exists:hr_offenses,id'],

                'description' => ['nullable', 'array'],
                'description.*' => ['nullable', 'string'],

                'remarks' => ['nullable', 'string', 'max:5000'],

                'disciplinary_action' => ['nullable', 'array'],
                'disciplinary_action.*' => [
                    'string',
                    Rule::in([
                        'Salary Deduction Authorization',
                        'Suspension',
                        'Final Warning',
                    ]),
                ],

                'sda_amount' => ['nullable', 'numeric', 'min:0'],
                'sda_terms' => ['nullable', 'numeric', 'min:0'],
                'sda_start_date' => ['nullable', 'date'],
                'sda_end_date' => ['nullable', 'date', 'after_or_equal:sda_start_date'],

                'suspension_start_date' => ['nullable', 'date'],
                'suspension_end_date' => ['nullable', 'date', 'after_or_equal:suspension_start_date'],
            ]);

            $actions = $this->normalizeDisciplinaryActions(
                $validated['disciplinary_action'] ?? []
            );

            $validated = $this->prepareDisciplinaryFields($validated, $actions);

            DB::transaction(function () use ($employee, $validated, $actions) {
                foreach ($validated['offense_id'] as $index => $offenseId) {
                    $employee->histories()->create([
                        'title' => 'Violations',
                        'ir_number' => $validated['ir_number'],
                        'offense_id' => $offenseId,
                        'description' => $validated['description'][$index] ?? null,
                        'remarks' => $validated['remarks'] ?? null,

                        'disciplinary_action' => $actions,

                        'sda_amount' => $validated['sda_amount'] ?? null,
                        'sda_terms' => $validated['sda_terms'] ?? null,
                        'sda_start_date' => $validated['sda_start_date'] ?? null,
                        'sda_end_date' => $validated['sda_end_date'] ?? null,

                        'suspension_start_date' => $validated['suspension_start_date'] ?? null,
                        'suspension_end_date' => $validated['suspension_end_date'] ?? null,
                    ]);
                }

                $this->logEmployee($employee, 'added_violation_history', [
                    'ir_number' => $validated['ir_number'],
                    'offense_count' => count($validated['offense_id']),
                    'disciplinary_action' => $actions,
                ]);
            });

            flash('Violation history added successfully!')->success();

            return redirect()->route('employees.staff.show', $employee->id);

        } catch (ValidationException $e) {
            foreach ($e->errors() as $messages) {
                foreach ($messages as $message) {
                    flash($message)->warning();
                }
            }

            return back()->withErrors($e->validator)->withInput();

        } catch (\Throwable $e) {
            Log::error('Employee history store failed', [
                'employee_id' => $employee->id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            flash('Unable to add violation history. Please try again.')->error();

            return back()->withInput();
        }
    }

    /* ==========================================================
        UPDATE HISTORY / IR CASE
    ========================================================== */
    public function updateHistory(Request $request, Employee $employee, $historyId): RedirectResponse
    {
        try {
            $originalHistory = $employee->histories()->findOrFail($historyId);

            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],

                'ir_number' => ['required', 'string', 'max:255'],

                'offense_id' => ['required', 'array', 'min:1'],
                'offense_id.*' => ['required', 'integer', 'exists:hr_offenses,id'],

                'description' => ['nullable', 'array'],
                'description.*' => ['nullable', 'string'],

                'remarks' => ['nullable', 'string', 'max:5000'],

                'disciplinary_action' => ['nullable', 'array'],
                'disciplinary_action.*' => [
                    'string',
                    Rule::in([
                        'Salary Deduction Authorization',
                        'Suspension',
                        'Final Warning',
                    ]),
                ],

                'sda_amount' => ['nullable', 'numeric', 'min:0'],
                'sda_terms' => ['nullable', 'numeric', 'min:0'],
                'sda_start_date' => ['nullable', 'date'],
                'sda_end_date' => ['nullable', 'date', 'after_or_equal:sda_start_date'],

                'suspension_start_date' => ['nullable', 'date'],
                'suspension_end_date' => ['nullable', 'date', 'after_or_equal:suspension_start_date'],
            ]);

            $actions = $this->normalizeDisciplinaryActions(
                $validated['disciplinary_action'] ?? []
            );

            $validated = $this->prepareDisciplinaryFields($validated, $actions);

            DB::transaction(function () use ($employee, $originalHistory, $validated, $actions) {
                $oldIrNumber = $originalHistory->ir_number;

                $employee->histories()
                    ->where('title', 'Violations')
                    ->where('ir_number', $oldIrNumber)
                    ->delete();

                foreach ($validated['offense_id'] as $index => $offenseId) {
                    $employee->histories()->create([
                        'title' => 'Violations',
                        'ir_number' => $validated['ir_number'],
                        'offense_id' => $offenseId,
                        'description' => $validated['description'][$index] ?? null,
                        'remarks' => $validated['remarks'] ?? null,

                        'disciplinary_action' => $actions,

                        'sda_amount' => $validated['sda_amount'] ?? null,
                        'sda_terms' => $validated['sda_terms'] ?? null,
                        'sda_start_date' => $validated['sda_start_date'] ?? null,
                        'sda_end_date' => $validated['sda_end_date'] ?? null,

                        'suspension_start_date' => $validated['suspension_start_date'] ?? null,
                        'suspension_end_date' => $validated['suspension_end_date'] ?? null,
                    ]);
                }

                $this->logEmployee($employee, 'updated_violation_history', [
                    'old_ir_number' => $oldIrNumber,
                    'new_ir_number' => $validated['ir_number'],
                    'offense_count' => count($validated['offense_id']),
                    'disciplinary_action' => $actions,
                ]);
            });

            flash('Violation history updated successfully!')->success();

            return redirect()->route('employees.staff.show', $employee->id);

        } catch (ValidationException $e) {
            foreach ($e->errors() as $messages) {
                foreach ($messages as $message) {
                    flash($message)->warning();
                }
            }

            return back()->withErrors($e->validator)->withInput();

        } catch (\Throwable $e) {
            Log::error('Employee history update failed', [
                'employee_id' => $employee->id,
                'history_id' => $historyId,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            flash('Unable to update violation history. Please try again.')->error();

            return back()->withInput();
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
    public function destroyHistory(Employee $employee, $historyId): RedirectResponse
    {
        try {
            $history = $employee->histories()->findOrFail($historyId);

            DB::transaction(function () use ($employee, $history) {
                $irNumber = $history->ir_number;

                if ($history->title === 'Violations' && filled($irNumber)) {
                    $deletedCount = $employee->histories()
                        ->where('title', 'Violations')
                        ->where('ir_number', $irNumber)
                        ->count();

                    $employee->histories()
                        ->where('title', 'Violations')
                        ->where('ir_number', $irNumber)
                        ->delete();

                    $this->logEmployee($employee, 'removed_violation_history', [
                        'ir_number' => $irNumber,
                        'deleted_count' => $deletedCount,
                    ]);

                    return;
                }

                $this->logEmployee($employee, 'removed_history', [
                    'title' => $history->title,
                    'start_date' => $history->start_date,
                    'end_date' => $history->end_date,
                ]);

                $history->delete();
            });

            flash('History removed successfully!')->success();

            return redirect()->route('employees.staff.show', $employee->id);

        } catch (\Throwable $e) {
            Log::error('Employee history delete failed', [
                'employee_id' => $employee->id,
                'history_id' => $historyId,
                'message' => $e->getMessage(),
            ]);

            flash('Unable to remove history.')->error();

            return redirect()->route('employees.staff.show', $employee->id);
        }
    }

    /* ==========================================================
        DELETE EMPLOYEE
    ========================================================== */
    public function destroy($id)
    {
        try {
            $employee = Employee::findOrFail($id);

            $employee->delete();

            return back()->with('success', 'Employee deleted successfully.');
        } catch (\Throwable $e) {
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

        $pdf = Pdf::loadView('hr_department.employees.modals._employee_201_pdf', compact('employee'))
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
        HELPER METHODS
    ========================================================== */
    private function logEmployee(Employee $employee, string $action, array $meta = []): void
    {
        EmployeeLog::create([
            'employee_id' => $employee->id,
            'action' => $action,
            'meta' => $meta ?: null,
            'user_id' => auth()->id(),
        ]);
    }

    /* ==========================================================
        DIFF CHANGES (for logging what changed in updates)
    ========================================================== */
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

    /* ==========================================================
        NORMALIZE DATE (for consistent storage and diffing)
    ========================================================== */
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

    /* ==========================================================
        PRIVATE HELPERS
    ========================================================== */
    private function normalizeDisciplinaryActions(array|string|null $actions): array
    {
        if (blank($actions)) {
            return [];
        }

        if (is_string($actions)) {
            $decoded = json_decode($actions, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $actions = $decoded;
            } else {
                $actions = [$actions];
            }
        }

        return collect($actions)
            ->filter()
            ->map(fn ($action) => trim((string) $action))
            ->filter(fn ($action) => in_array($action, [
                'Salary Deduction Authorization',
                'Suspension',
                'Final Warning',
            ], true))
            ->unique()
            ->values()
            ->all();
    }

    private function prepareDisciplinaryFields(array $validated, array $actions): array
    {
        $hasSda = in_array('Salary Deduction Authorization', $actions, true);
        $hasSuspension = in_array('Suspension', $actions, true);

        if ($hasSda) {
            $errors = [];

            if (blank($validated['sda_amount'] ?? null)) {
                $errors['sda_amount'] = 'SDA total amount is required when Salary Deduction Authorization is selected.';
            }

            if (blank($validated['sda_terms'] ?? null)) {
                $errors['sda_terms'] = 'Per cutoff amount / deduction terms is required when Salary Deduction Authorization is selected.';
            }

            if (blank($validated['sda_start_date'] ?? null)) {
                $errors['sda_start_date'] = 'SDA start date is required when Salary Deduction Authorization is selected.';
            }

            if (! empty($errors)) {
                throw ValidationException::withMessages($errors);
            }
        } else {
            $validated['sda_amount'] = null;
            $validated['sda_terms'] = null;
            $validated['sda_start_date'] = null;
            $validated['sda_end_date'] = null;
        }

        if ($hasSuspension) {
            if (blank($validated['suspension_start_date'] ?? null)) {
                throw ValidationException::withMessages([
                    'suspension_start_date' => 'Suspension start date is required when Suspension is selected.',
                ]);
            }
        } else {
            $validated['suspension_start_date'] = null;
            $validated['suspension_end_date'] = null;
        }

        return $validated;
    }
}
