<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\DailyAttendanceSummary;
use App\Models\EmployeePlottingSchedule;
use App\Services\Payroll\DailyAttendanceSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceSummaryController extends Controller
{
    public function index(Request $request)
    {
        [$defaultCutoffMonth, $defaultCutoffYear, $defaultCutoffType] = $this->getDefaultCutoff();

        $cutoffMonth = (int) ($request->cutoff_month ?: $defaultCutoffMonth);
        $cutoffYear = (int) ($request->cutoff_year ?: $defaultCutoffYear);
        $cutoffType = $request->cutoff_type ?: $defaultCutoffType;

        $search = trim((string) $request->search);
        $status = trim((string) $request->status);
        $dayType = trim((string) $request->day_type);

        [$startDate, $endDate, $cutoffLabel] = $this->resolveCutoffRange(
            $cutoffMonth,
            $cutoffYear,
            $cutoffType
        );

        $baseQuery = $this->summaryBaseQuery($startDate, $endDate, $search, $status, $dayType);

        $stats = $this->buildStats(clone $baseQuery);

        $summaries = (clone $baseQuery)
            ->orderBy('work_date')
            ->orderBy('employee_name')
            ->paginate(25)
            ->withQueryString();

        if ($request->ajax()) {
            return view('payroll.attendance_summary.table', compact(
                'summaries',
                'cutoffLabel',
                'stats'
            ))->render();
        }

        return view('payroll.attendance_summary.index', compact(
            'summaries',
            'stats',
            'cutoffMonth',
            'cutoffYear',
            'cutoffType',
            'cutoffLabel',
            'search',
            'status',
            'dayType'
        ));
    }

    public function rebuild(Request $request, DailyAttendanceSummaryService $service)
    {
        $request->validate([
            'cutoff_month' => ['required', 'integer', 'min:1', 'max:12'],
            'cutoff_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'cutoff_type' => ['required', 'in:first,second'],
        ]);

        @ini_set('max_execution_time', 300);

        [$startDate, $endDate] = $this->resolveCutoffRange(
            (int) $request->cutoff_month,
            (int) $request->cutoff_year,
            $request->cutoff_type
        );

        $service->buildForPeriod($startDate, $endDate);

        return redirect()
            ->route('attendance-summary.index', $request->only([
                'cutoff_month',
                'cutoff_year',
                'cutoff_type',
                'search',
                'status',
                'day_type',
            ]))
            ->with('success', 'Attendance summary rebuilt successfully.');
    }

    public function exportPayroll(Request $request)
    {
        [$defaultCutoffMonth, $defaultCutoffYear, $defaultCutoffType] = $this->getDefaultCutoff();

        $cutoffMonth = (int) ($request->cutoff_month ?: $defaultCutoffMonth);
        $cutoffYear = (int) ($request->cutoff_year ?: $defaultCutoffYear);
        $cutoffType = $request->cutoff_type ?: $defaultCutoffType;

        $search = trim((string) $request->search);
        $status = trim((string) $request->status);
        $dayType = trim((string) $request->day_type);

        [$startDate, $endDate, $cutoffLabel] = $this->resolveCutoffRange(
            $cutoffMonth,
            $cutoffYear,
            $cutoffType
        );

        $summaryRows = $this->summaryBaseQuery($startDate, $endDate, $search, $status, $dayType)
            ->orderBy('employee_name')
            ->orderBy('work_date')
            ->get();

        $masterEmployees = EmployeePlottingSchedule::query()
            ->selectRaw("
                MIN(biometric_employee_id) AS biometric_employee_id,
                TRIM(employee_no) AS employee_no,
                MIN(NULLIF(TRIM(employee_name), '')) AS employee_name
            ")
            ->whereNotNull('employee_no')
            ->whereRaw("TRIM(employee_no) <> ''")
            ->groupBy(DB::raw('TRIM(employee_no)'))
            ->orderBy('employee_name')
            ->get();

        $recordsByEmployee = $summaryRows->groupBy(fn ($row) => trim((string) $row->employee_no));

        $employees = $masterEmployees->map(function ($employee) use ($recordsByEmployee) {

            $employeeNo = trim((string) $employee->employee_no);
            $records = $recordsByEmployee->get($employeeNo, collect());

            return [
                'employee_name' => $employee->employee_name,
                'employee_no' => $employeeNo,
                'biometric_employee_id' => $employee->biometric_employee_id,
                'records' => $records,

                'total_late_minutes' => $records->sum('late_minutes'),
                'total_undertime_minutes' => $records->sum('undertime_minutes'),
                'total_worked_minutes' => $records->sum('worked_minutes'),
                'total_payable_days' => $records->sum('payable_days'),
                'total_payable_hours' => $records->sum('payable_hours'),
            ];
        });

        $employeePages = $employees->chunk(9);

        $stats = $this->buildStats($this->summaryBaseQuery($startDate, $endDate, $search, $status, $dayType));

        return view('payroll.attendance_summary.export-payroll', compact(
            'employeePages',
            'summaryRows',
            'stats',
            'cutoffMonth',
            'cutoffYear',
            'cutoffType',
            'cutoffLabel',
            'search',
            'status',
            'dayType'
        ));
    }

    protected function summaryBaseQuery(
        Carbon $startDate,
        Carbon $endDate,
        ?string $search = null,
        ?string $status = null,
        ?string $dayType = null
    ) {
        return DailyAttendanceSummary::query()
            ->whereBetween('work_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employee_name', 'like', "%{$search}%")
                        ->orWhere('employee_no', 'like', "%{$search}%")
                        ->orWhere('biometric_employee_id', 'like', "%{$search}%")
                        ->orWhere('attendance_status', 'like', "%{$search}%")
                        ->orWhere('shift_name', 'like', "%{$search}%")
                        ->orWhere('holiday_name', 'like', "%{$search}%")
                        ->orWhere('holiday_type', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%")
                        ->orWhere('schedule_remarks', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query) use ($status) {
                if ($status === 'needs_review') {
                    $query->whereIn('attendance_status', [
                        'half_day',
                        'incomplete_log',
                        'no_schedule',
                        'holiday_unpaid',
                        'absent',
                    ]);
                } elseif ($status === 'payable') {
                    $query->where('payable_days', '>', 0);
                } else {
                    $query->where('attendance_status', $status);
                }
            })
            ->when($dayType, function ($query) use ($dayType) {
                if ($dayType === 'holiday') {
                    $query->where('is_holiday', true);
                } elseif ($dayType === 'holiday_paid') {
                    $query->where('is_holiday', true)->where('payable_days', '>', 0);
                } elseif ($dayType === 'holiday_unpaid') {
                    $query->where('attendance_status', 'holiday_unpaid');
                } elseif ($dayType === 'rest_day') {
                    $query->where('is_rest_day', true);
                } elseif ($dayType === 'leave') {
                    $query->where('is_leave', true);
                } elseif ($dayType === 'adjustment') {
                    $query->where('has_adjustment', true);
                } elseif ($dayType === 'regular') {
                    $query->where('is_holiday', false)
                        ->where('is_rest_day', false)
                        ->where('is_leave', false);
                } elseif ($dayType === 'regular_shift') {
                    $query->where('shift_name', 'like', '%Regular%');
                } elseif ($dayType === 'flexible_shift') {
                    $query->where('shift_name', 'like', '%Flexible%');
                } elseif ($dayType === 'needs_review') {
                    $query->whereIn('attendance_status', [
                        'half_day',
                        'incomplete_log',
                        'no_schedule',
                        'holiday_unpaid',
                        'absent',
                    ]);
                }
            });
    }

    protected function buildStats($baseQuery): array
    {
        return [
            'total' => (clone $baseQuery)->count(),

            'present' => (clone $baseQuery)
                ->whereIn('attendance_status', [
                    'present',
                    'adjusted_present',
                ])
                ->count(),

            'payable_records' => (clone $baseQuery)
                ->where('payable_days', '>', 0)
                ->count(),

            'needs_review' => (clone $baseQuery)
                ->whereIn('attendance_status', [
                    'half_day',
                    'incomplete_log',
                    'no_schedule',
                    'holiday_unpaid',
                    'absent',
                ])
                ->count(),

            'half_day' => (clone $baseQuery)
                ->where('attendance_status', 'half_day')
                ->count(),

            'late_undertime_records' => (clone $baseQuery)
                ->whereIn('attendance_status', [
                    'late',
                    'undertime',
                    'late_undertime',
                ])
                ->count(),

            'late' => (clone $baseQuery)
                ->whereIn('attendance_status', [
                    'late',
                    'late_undertime',
                ])
                ->count(),

            'undertime' => (clone $baseQuery)
                ->whereIn('attendance_status', [
                    'undertime',
                    'late_undertime',
                    'half_day',
                ])
                ->count(),

            'absent' => (clone $baseQuery)
                ->where('attendance_status', 'absent')
                ->count(),

            'incomplete' => (clone $baseQuery)
                ->where('attendance_status', 'incomplete_log')
                ->count(),

            'no_schedule' => (clone $baseQuery)
                ->where('attendance_status', 'no_schedule')
                ->count(),

            'holiday' => (clone $baseQuery)
                ->where('is_holiday', true)
                ->count(),

            'holiday_paid' => (clone $baseQuery)
                ->where('is_holiday', true)
                ->where('payable_days', '>', 0)
                ->count(),

            'holiday_unpaid' => (clone $baseQuery)
                ->where('attendance_status', 'holiday_unpaid')
                ->count(),

            'holiday_worked' => (clone $baseQuery)
                ->where('attendance_status', 'holiday_worked')
                ->count(),

            'regular_holiday_worked' => (clone $baseQuery)
                ->where('attendance_status', 'holiday_worked')
                ->where(function ($query) {
                    $query->where('holiday_type', 'like', '%regular%')
                        ->where('holiday_type', 'not like', '%non%')
                        ->where('holiday_type', 'not like', '%special%');
                })
                ->count(),

            'special_holiday_worked' => (clone $baseQuery)
                ->where('attendance_status', 'holiday_worked')
                ->where(function ($query) {
                    $query->where('holiday_type', 'like', '%special%')
                        ->orWhere('holiday_type', 'like', '%non%');
                })
                ->count(),

            'rest_day' => (clone $baseQuery)
                ->where('is_rest_day', true)
                ->count(),

            'rest_day_paid' => (clone $baseQuery)
                ->where('is_rest_day', true)
                ->where('payable_days', '>', 0)
                ->count(),

            'leave' => (clone $baseQuery)
                ->where('is_leave', true)
                ->count(),

            'adjustment' => (clone $baseQuery)
                ->where('has_adjustment', true)
                ->count(),

            'regular_shift' => (clone $baseQuery)
                ->where('shift_name', 'like', '%Regular%')
                ->count(),

            'flexible_shift' => (clone $baseQuery)
                ->where('shift_name', 'like', '%Flexible%')
                ->count(),

            'total_late_minutes' => (clone $baseQuery)->sum('late_minutes'),
            'total_undertime_minutes' => (clone $baseQuery)->sum('undertime_minutes'),
            'total_worked_minutes' => (clone $baseQuery)->sum('worked_minutes'),
            'total_overtime_minutes' => (clone $baseQuery)->sum('overtime_minutes'),
            'total_payable_days' => (clone $baseQuery)->sum('payable_days'),
            'total_payable_hours' => (clone $baseQuery)->sum('payable_hours'),
        ];
    }

    protected function statusOptions(): array
    {
        return [
            '' => 'All Status',
            'payable' => 'Payable Records',
            'needs_review' => 'Needs Review',
            'present' => 'Present',
            'adjusted_present' => 'Adjusted Present',
            'half_day' => 'Half Day',
            'late' => 'Late',
            'undertime' => 'Undertime',
            'late_undertime' => 'Late + Undertime',
            'absent' => 'Absent',
            'incomplete_log' => 'Incomplete Log',
            'no_schedule' => 'No Plotted Schedule',
            'holiday' => 'Paid Holiday',
            'holiday_worked' => 'Holiday Worked',
            'holiday_unpaid' => 'Unpaid Holiday',
            'rest_day' => 'Paid Rest Day',
            'rest_day_worked' => 'Rest Day Worked',
            'leave' => 'Leave',
        ];
    }

    protected function dayTypeOptions(): array
    {
        return [
            '' => 'All Day Types',
            'regular' => 'Regular Day',
            'regular_shift' => 'Regular Shift',
            'flexible_shift' => 'Flexible Shift',
            'holiday' => 'All Holidays',
            'holiday_paid' => 'Paid Holidays',
            'holiday_unpaid' => 'Unpaid Holidays',
            'rest_day' => 'Rest Day / Day Off',
            'leave' => 'Leave',
            'adjustment' => 'With Adjustment',
            'needs_review' => 'Needs Review',
        ];
    }

    protected function getDefaultCutoff(): array
    {
        $today = now('Asia/Manila');

        if ($today->day >= 11 && $today->day <= 25) {
            return [
                (int) $today->month,
                (int) $today->year,
                'first',
            ];
        }

        if ($today->day >= 26) {
            return [
                (int) $today->month,
                (int) $today->year,
                'second',
            ];
        }

        $previousMonth = $today->copy()->subMonth();

        return [
            (int) $previousMonth->month,
            (int) $previousMonth->year,
            'second',
        ];
    }

    protected function resolveCutoffRange(int $month, int $year, string $type): array
    {
        $month = max(1, min(12, $month));
        $year = max(2000, min(2100, $year));
        $type = in_array($type, ['first', 'second'], true) ? $type : 'first';

        if ($type === 'first') {
            $startDate = Carbon::create($year, $month, 11, 0, 0, 0, 'Asia/Manila')->startOfDay();
            $endDate = Carbon::create($year, $month, 25, 23, 59, 59, 'Asia/Manila')->endOfDay();
            $label = $startDate->format('F d, Y').' - '.$endDate->format('F d, Y').' (1st Cutoff)';

            return [$startDate, $endDate, $label];
        }

        $startDate = Carbon::create($year, $month, 26, 0, 0, 0, 'Asia/Manila')->startOfDay();
        $endDate = Carbon::create($year, $month, 26, 23, 59, 59, 'Asia/Manila')
            ->addMonth()
            ->day(10)
            ->endOfDay();

        $label = $startDate->format('F d, Y').' - '.$endDate->format('F d, Y').' (2nd Cutoff)';

        return [$startDate, $endDate, $label];
    }
}
