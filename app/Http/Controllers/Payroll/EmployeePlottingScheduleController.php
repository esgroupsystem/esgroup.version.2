<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\EmployeePlottingSchedule;
use App\Models\MirasolBiometricsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmployeePlottingScheduleController extends Controller
{
    public function index(Request $request)
    {
        [$defaultCutoffMonth, $defaultCutoffYear, $defaultCutoffType] = $this->getDefaultCutoff();

        $cutoffMonth = (int) ($request->cutoff_month ?: $defaultCutoffMonth);
        $cutoffYear = (int) ($request->cutoff_year ?: $defaultCutoffYear);
        $cutoffType = $request->cutoff_type ?: $defaultCutoffType;
        $search = trim((string) $request->search);
        $perPage = 10;

        [$startDate, $endDate, $cutoffLabel] = $this->resolveCutoffRange(
            $cutoffYear,
            $cutoffMonth,
            $cutoffType
        );

        $employeeBaseQuery = MirasolBiometricsLog::query()
            ->selectRaw('
            MAX(crosschex_id) as crosschex_id,
            employee_id as biometric_employee_id,
            employee_no,
            employee_name
        ')
            ->whereNotNull('employee_name')
            ->groupBy('employee_id', 'employee_no', 'employee_name');

        $employeesQuery = DB::query()
            ->fromSub($employeeBaseQuery, 'employees')
            ->orderBy('employee_name');

        if ($search !== '') {
            $employeesQuery->where(function ($q) use ($search) {
                $q->where('employee_name', 'like', "%{$search}%")
                    ->orWhere('employee_no', 'like', "%{$search}%")
                    ->orWhere('biometric_employee_id', 'like', "%{$search}%")
                    ->orWhere('crosschex_id', 'like', "%{$search}%");
            });
        } else {
            $employeesQuery->whereRaw('1 = 0');
        }

        $employees = $employeesQuery
            ->paginate($perPage)
            ->withQueryString();

        $pageEmployees = collect($employees->items());

        $bioIds = $pageEmployees->pluck('biometric_employee_id')->filter()->values()->all();
        $employeeNos = $pageEmployees->pluck('employee_no')->filter()->values()->all();

        $schedulesQuery = EmployeePlottingSchedule::query()
            ->whereBetween('work_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if (! empty($bioIds) || ! empty($employeeNos)) {
            $schedulesQuery->where(function ($query) use ($bioIds, $employeeNos) {
                if (! empty($bioIds)) {
                    $query->orWhereIn('biometric_employee_id', $bioIds);
                }

                if (! empty($employeeNos)) {
                    $query->orWhereIn('employee_no', $employeeNos);
                }
            });
        } else {
            $schedulesQuery->whereRaw('1 = 0');
        }

        $schedules = $schedulesQuery
            ->get()
            ->groupBy(function ($item) {
                return ($item->biometric_employee_id ?: $item->employee_no).'_'.$item->work_date->format('Y-m-d');
            });

        $days = collect();
        $cursor = $startDate->copy();

        while ($cursor->lte($endDate)) {
            $days->push([
                'day' => $cursor->day,
                'date' => $cursor->toDateString(),
                'dow_short' => $cursor->format('D'),
                'is_sunday' => $cursor->isSunday(),
                'is_saturday' => $cursor->isSaturday(),
                'is_today' => $cursor->isToday(),
                'month_short' => $cursor->format('M'),
            ]);

            $cursor->addDay();
        }

        if ($request->ajax()) {
            return view('payroll.plotting.table', compact(
                'employees',
                'schedules',
                'days',
                'search',
                'cutoffMonth',
                'cutoffYear',
                'cutoffType',
                'startDate',
                'endDate',
                'cutoffLabel'
            ));
        }

        return view('payroll.plotting.index', compact(
            'employees',
            'schedules',
            'days',
            'search',
            'cutoffMonth',
            'cutoffYear',
            'cutoffType',
            'startDate',
            'endDate',
            'cutoffLabel'
        ));
    }

    public function saveMonthly(Request $request)
    {
        try {
            $validated = $request->validate([
                'cutoff_month' => ['required', 'integer', 'min:1', 'max:12'],
                'cutoff_year' => ['required', 'integer', 'min:2000', 'max:2100'],
                'cutoff_type' => ['required', Rule::in(['11_25', '26_10'])],
                'page' => ['nullable', 'integer', 'min:1'],
                'search' => ['nullable', 'string'],

                'schedule' => ['required', 'array'],
                'schedule.*.crosschex_id' => ['nullable', 'string', 'max:255'],
                'schedule.*.biometric_employee_id' => ['nullable', 'string', 'max:255'],
                'schedule.*.employee_no' => ['nullable', 'string', 'max:255'],
                'schedule.*.employee_name' => ['required', 'string', 'max:255'],
                'schedule.*.days' => ['required', 'array'],
                'schedule.*.days.*.work_date' => ['required', 'date'],
                'schedule.*.days.*.status' => ['nullable', Rule::in(['scheduled', 'rest_day', 'leave', 'holiday'])],
                'schedule.*.days.*.shift_name' => ['nullable', 'string', 'max:255'],
                'schedule.*.days.*.time_in' => ['nullable', 'date_format:H:i'],
                'schedule.*.days.*.time_out' => ['nullable', 'date_format:H:i'],
                'schedule.*.days.*.grace_minutes' => ['nullable', 'integer', 'min:0'],
                'schedule.*.days.*.remarks' => ['nullable', 'string'],
            ]);

            [$startDate, $endDate] = $this->resolveCutoffRange(
                (int) $validated['cutoff_year'],
                (int) $validated['cutoff_month'],
                $validated['cutoff_type']
            );

            foreach ($validated['schedule'] as $employeeRow) {
                foreach ($employeeRow['days'] as $dayData) {
                    $workDate = Carbon::parse($dayData['work_date'])->startOfDay();

                    if ($workDate->lt($startDate) || $workDate->gt($endDate)) {
                        continue;
                    }

                    $status = $dayData['status'] ?? null;
                    $timeIn = $dayData['time_in'] ?? null;
                    $timeOut = $dayData['time_out'] ?? null;
                    $shiftName = $dayData['shift_name'] ?? null;
                    $remarks = $dayData['remarks'] ?? null;
                    $graceMinutes = isset($dayData['grace_minutes']) && $dayData['grace_minutes'] !== ''
                        ? (int) $dayData['grace_minutes']
                        : 15;

                    $isCompletelyEmpty =
                        empty($status) &&
                        empty($timeIn) &&
                        empty($timeOut) &&
                        empty($shiftName) &&
                        empty($remarks);

                    $identityKey = ! empty($employeeRow['biometric_employee_id'])
                        ? [
                            'biometric_employee_id' => $employeeRow['biometric_employee_id'],
                            'work_date' => $dayData['work_date'],
                        ]
                        : [
                            'employee_no' => $employeeRow['employee_no'],
                            'work_date' => $dayData['work_date'],
                        ];

                    if ($isCompletelyEmpty) {
                        EmployeePlottingSchedule::where($identityKey)->delete();

                        continue;
                    }

                    EmployeePlottingSchedule::updateOrCreate(
                        $identityKey,
                        [
                            'crosschex_id' => $employeeRow['crosschex_id'] ?? null,
                            'biometric_employee_id' => $employeeRow['biometric_employee_id'] ?? null,
                            'employee_no' => $employeeRow['employee_no'] ?? null,
                            'employee_name' => $employeeRow['employee_name'],
                            'shift_name' => $shiftName,
                            'time_in' => $status === 'scheduled' ? $timeIn : null,
                            'time_out' => $status === 'scheduled' ? $timeOut : null,
                            'grace_minutes' => $graceMinutes,
                            'status' => $status ?: 'scheduled',
                            'remarks' => $remarks,
                        ]
                    );
                }
            }

            return redirect()
                ->route('payroll-plotting.index', [
                    'cutoff_month' => $validated['cutoff_month'],
                    'cutoff_year' => $validated['cutoff_year'],
                    'cutoff_type' => $validated['cutoff_type'],
                    'search' => $request->search,
                    'page' => $request->page,
                ])
                ->with('success', 'Cutoff plotting schedule saved successfully.');
        } catch (\Throwable $e) {
            \Log::error('Error saving plotting schedule', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong while saving the plotting schedule.');
        }
    }

    public function quickFill(Request $request)
    {
        $validated = $request->validate([
            'cutoff_month' => ['required', 'integer', 'min:1', 'max:12'],
            'cutoff_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'cutoff_type' => ['required', Rule::in(['11_25', '26_10'])],
            'employee_keys' => ['required', 'array', 'min:1'],
            'default_shift_name' => ['nullable', 'string', 'max:255'],
            'default_time_in' => ['nullable', 'date_format:H:i'],
            'default_time_out' => ['nullable', 'date_format:H:i'],
            'default_grace_minutes' => ['nullable', 'integer', 'min:0'],
            'rest_day_mode' => ['nullable', Rule::in(['sunday', 'sat_sun', 'none'])],
        ], [
            'employee_keys.required' => 'Please search employee first before generating default cutoff.',
            'employee_keys.min' => 'Please search employee first before generating default cutoff.',
        ]);

        $cutoffMonth = (int) $validated['cutoff_month'];
        $cutoffYear = (int) $validated['cutoff_year'];
        $cutoffType = $validated['cutoff_type'];

        $restDayMode = $validated['rest_day_mode'] ?? 'sunday';
        $defaultShiftName = $validated['default_shift_name'] ?? 'Regular Shift';
        $defaultTimeIn = $validated['default_time_in'] ?? '08:00';
        $defaultTimeOut = $validated['default_time_out'] ?? '17:00';
        $defaultGraceMinutes = isset($validated['default_grace_minutes'])
            ? (int) $validated['default_grace_minutes']
            : 15;

        [$startDate, $endDate] = $this->resolveCutoffRange(
            $cutoffYear,
            $cutoffMonth,
            $cutoffType
        );

        $employeesQuery = MirasolBiometricsLog::query()
            ->selectRaw('
                MAX(crosschex_id) as crosschex_id,
                employee_id as biometric_employee_id,
                employee_no,
                employee_name
            ')
            ->whereNotNull('employee_name')
            ->groupBy('employee_id', 'employee_no', 'employee_name')
            ->orderBy('employee_name');

        if (! empty($validated['employee_keys'])) {
            $keys = $validated['employee_keys'];

            $employeesQuery->where(function ($query) use ($keys) {
                foreach ($keys as $key) {
                    [$idType, $idValue] = array_pad(explode(':', $key, 2), 2, null);

                    if ($idType === 'bio' && $idValue !== null) {
                        $query->orWhere('employee_id', $idValue);
                    }

                    if ($idType === 'empno' && $idValue !== null) {
                        $query->orWhere('employee_no', $idValue);
                    }
                }
            });
        } else {
            $employeesQuery->whereRaw('1 = 0');
        }

        $employees = $employeesQuery->get();

        foreach ($employees as $employee) {
            $cursor = $startDate->copy();

            while ($cursor->lte($endDate)) {
                $status = 'scheduled';

                if ($restDayMode === 'sunday' && $cursor->isSunday()) {
                    $status = 'rest_day';
                }

                if ($restDayMode === 'sat_sun' && ($cursor->isSaturday() || $cursor->isSunday())) {
                    $status = 'rest_day';
                }

                $identityKey = ! empty($employee->biometric_employee_id)
                    ? [
                        'biometric_employee_id' => $employee->biometric_employee_id,
                        'work_date' => $cursor->toDateString(),
                    ]
                    : [
                        'employee_no' => $employee->employee_no,
                        'work_date' => $cursor->toDateString(),
                    ];

                EmployeePlottingSchedule::updateOrCreate(
                    $identityKey,
                    [
                        'crosschex_id' => $employee->crosschex_id,
                        'employee_no' => $employee->employee_no,
                        'employee_name' => $employee->employee_name,
                        'shift_name' => $status === 'scheduled' ? $defaultShiftName : null,
                        'time_in' => $status === 'scheduled' ? $defaultTimeIn : null,
                        'time_out' => $status === 'scheduled' ? $defaultTimeOut : null,
                        'grace_minutes' => $defaultGraceMinutes,
                        'status' => $status,
                        'remarks' => null,
                    ]
                );

                $cursor->addDay();
            }
        }

        return redirect()
            ->route('payroll-plotting.index', [
                'cutoff_month' => $cutoffMonth,
                'cutoff_year' => $cutoffYear,
                'cutoff_type' => $cutoffType,
                'search' => $request->search,
                'page' => $request->page,
            ])
            ->with('success', 'Default cutoff plotting generated successfully.');
    }

    private function resolveCutoffRange(int $year, int $month, string $cutoffType): array
    {
        if ($cutoffType === '11_25') {
            $startDate = Carbon::create($year, $month, 11)->startOfDay();
            $endDate = Carbon::create($year, $month, 25)->startOfDay();
            $label = $startDate->format('F d').' - '.$endDate->format('d, Y');
        } else {
            $startDate = Carbon::create($year, $month, 26)->startOfDay();
            $endDate = Carbon::create($year, $month, 26)->addMonth()->day(10)->startOfDay();
            $label = $startDate->format('F d, Y').' - '.$endDate->format('F d, Y');
        }

        return [$startDate, $endDate, $label];
    }

    private function getDefaultCutoff(): array
    {
        $today = now()->startOfDay();

        if ($today->day >= 11 && $today->day <= 25) {
            return [(int) $today->month, (int) $today->year, '11_25'];
        }

        if ($today->day >= 26) {
            return [(int) $today->month, (int) $today->year, '26_10'];
        }

        $previousMonth = $today->copy()->subMonth();

        return [(int) $previousMonth->month, (int) $previousMonth->year, '26_10'];
    }

    public function searchSuggestions(Request $request)
    {
        $term = trim((string) $request->get('q', ''));

        if ($term === '' || mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $suggestions = MirasolBiometricsLog::query()
            ->selectRaw('
            MAX(crosschex_id) as crosschex_id,
            employee_id as biometric_employee_id,
            employee_no,
            employee_name
        ')
            ->whereNotNull('employee_name')
            ->where(function ($query) use ($term) {
                $query->where('employee_name', 'like', "%{$term}%")
                    ->orWhere('employee_no', 'like', "%{$term}%")
                    ->orWhere('employee_id', 'like', "%{$term}%")
                    ->orWhere('crosschex_id', 'like', "%{$term}%");
            })
            ->groupBy('employee_id', 'employee_no', 'employee_name')
            ->orderBy('employee_name')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'label' => trim($item->employee_name.' | '.($item->employee_no ?: 'No Emp No').' | Bio ID: '.($item->biometric_employee_id ?: '-')),
                    'value' => $item->employee_name,
                ];
            })
            ->values();

        return response()->json($suggestions);
    }
}
