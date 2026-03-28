<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\DailyAttendanceSummary;
use App\Services\Payroll\DailyAttendanceSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

        [$startDate, $endDate, $cutoffLabel] = $this->resolveCutoffRange($cutoffMonth, $cutoffYear, $cutoffType);

        $baseQuery = DailyAttendanceSummary::query()
            ->whereBetween('work_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employee_name', 'like', "%{$search}%")
                        ->orWhere('employee_no', 'like', "%{$search}%")
                        ->orWhere('biometric_employee_id', 'like', "%{$search}%")
                        ->orWhere('attendance_status', 'like', "%{$search}%")
                        ->orWhere('shift_name', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('attendance_status', $status);
            })
            ->when($dayType, function ($query) use ($dayType) {
                if ($dayType === 'holiday') {
                    $query->where('is_holiday', true);
                } elseif ($dayType === 'rest_day') {
                    $query->where('is_rest_day', true);
                } elseif ($dayType === 'leave') {
                    $query->where('is_leave', true);
                } elseif ($dayType === 'regular') {
                    $query->where('is_holiday', false)
                        ->where('is_rest_day', false)
                        ->where('is_leave', false);
                }
            });

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'present' => (clone $baseQuery)->whereIn('attendance_status', ['present', 'adjusted_present'])->count(),
            'late' => (clone $baseQuery)->whereIn('attendance_status', ['late', 'late_undertime'])->count(),
            'undertime' => (clone $baseQuery)->whereIn('attendance_status', ['undertime', 'late_undertime'])->count(),
            'absent' => (clone $baseQuery)->where('attendance_status', 'absent')->count(),
            'incomplete' => (clone $baseQuery)->where('attendance_status', 'incomplete_log')->count(),
            'holiday' => (clone $baseQuery)->whereIn('attendance_status', ['holiday', 'holiday_worked'])->count(),
            'rest_day' => (clone $baseQuery)->whereIn('attendance_status', ['rest_day', 'rest_day_worked'])->count(),
            'leave' => (clone $baseQuery)->where('attendance_status', 'leave')->count(),
            'adjustment' => (clone $baseQuery)->where('has_adjustment', true)->count(),

            'total_late_minutes' => (clone $baseQuery)->sum('late_minutes'),
            'total_undertime_minutes' => (clone $baseQuery)->sum('undertime_minutes'),
            'total_worked_minutes' => (clone $baseQuery)->sum('worked_minutes'),
            'total_overtime_minutes' => (clone $baseQuery)->sum('overtime_minutes'),
            'total_payable_days' => (clone $baseQuery)->sum('payable_days'),
            'total_payable_hours' => (clone $baseQuery)->sum('payable_hours'),
        ];

        $summaries = (clone $baseQuery)
            ->orderBy('work_date')
            ->orderBy('employee_name')
            ->paginate(15)
            ->withQueryString();

        $statusOptions = [
            '' => 'All Status',
            'present' => 'Present',
            'adjusted_present' => 'Adjusted Present',
            'late' => 'Late',
            'undertime' => 'Undertime',
            'late_undertime' => 'Late + Undertime',
            'absent' => 'Absent',
            'incomplete_log' => 'Incomplete Log',
            'holiday' => 'Holiday',
            'holiday_worked' => 'Holiday Worked',
            'rest_day' => 'Rest Day',
            'rest_day_worked' => 'Rest Day Worked',
            'leave' => 'Leave',
        ];

        $dayTypeOptions = [
            '' => 'All Day Types',
            'regular' => 'Regular Day',
            'holiday' => 'Holiday',
            'rest_day' => 'Rest Day',
            'leave' => 'Leave',
        ];

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
            'statusOptions',
            'dayTypeOptions',
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

        [$startDate, $endDate] = $this->resolveCutoffRange(
            (int) $request->cutoff_month,
            (int) $request->cutoff_year,
            $request->cutoff_type
        );

        $service->buildForPeriod($startDate, $endDate);

        return redirect()
            ->route('attendance-summary.index', [
                'cutoff_month' => $request->cutoff_month,
                'cutoff_year' => $request->cutoff_year,
                'cutoff_type' => $request->cutoff_type,
                'search' => $request->search,
                'status' => $request->status,
                'day_type' => $request->day_type,
            ])
            ->with('success', 'Attendance summary rebuilt successfully.');
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
        $type = in_array($type, ['first', 'second']) ? $type : 'first';

        if ($type === 'first') {
            $startDate = Carbon::create($year, $month, 11, 0, 0, 0, 'Asia/Manila')->startOfDay();
            $endDate = Carbon::create($year, $month, 25, 23, 59, 59, 'Asia/Manila')->endOfDay();
            $label = $startDate->format('F d, Y').' - '.$endDate->format('F d, Y').' (1st Cutoff)';
        } else {
            $startDate = Carbon::create($year, $month, 26, 0, 0, 0, 'Asia/Manila')->startOfDay();
            $endDate = Carbon::create($year, $month, 26, 23, 59, 59, 'Asia/Manila')
                ->addMonth()
                ->day(10)
                ->endOfDay();

            $label = $startDate->format('F d, Y').' - '.$endDate->format('F d, Y').' (2nd Cutoff)';
        }

        return [$startDate, $endDate, $label];
    }
}
