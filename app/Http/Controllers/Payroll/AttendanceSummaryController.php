<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\RebuildAttendanceSummaryRequest;
use App\Http\Resources\Payroll\AttendanceSummaryRowResource;
use App\Models\DailyAttendanceSummary;
use App\Models\EmployeeBiometric;
use App\Services\Payroll\AttendanceSummaryReportService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/**
 * Payroll → Summary: the daily attendance summary of a cutoff, its rebuild and the printable export.
 */
final class AttendanceSummaryController extends Controller
{
    /** Request keys kept when redirecting back to the list. */
    private const KEPT = ['cutoff_month', 'cutoff_year', 'cutoff_type', 'search', 'status', 'day_type', 'group_name'];

    public function __construct(
        private readonly AttendanceSummaryReportService $report,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $this->report->filters($request->query());
        [$start, $end, $cutoffLabel] = $this->report->cutoff($filters['cutoff_month'], $filters['cutoff_year'], $filters['cutoff_type']);
        $groups = $this->groups();
        $thisYear = (int) now('Asia/Manila')->year;

        return Inertia::render('payroll/attendance-summary/index', [
            'summaries' => $this->report->paginate($start, $end, $filters)
                ->through(fn (DailyAttendanceSummary $row): array => AttendanceSummaryRowResource::make($row)->resolve($request)),
            'stats' => $this->report->stats($start, $end, $filters),
            'filters' => $filters,
            'cutoffLabel' => $cutoffLabel,
            'groupLabel' => $filters['group_name'] !== '' ? ($groups[$filters['group_name']] ?? 'Payroll Group '.$filters['group_name']) : 'All Payroll Groups',
            'statusOptions' => AttendanceSummaryReportService::STATUS_OPTIONS,
            'dayTypeOptions' => AttendanceSummaryReportService::DAY_TYPE_OPTIONS,
            'payrollGroups' => $groups,
            'years' => range($thisYear + 1, $thisYear - 3),
            'can' => [
                'rebuild' => $request->user()->can('attendance-summary.create'),
                'export' => $request->user()->can('attendance-summary.export'),
            ],
            'urls' => [
                'index' => route('attendance-summary.index'),
                'rebuild' => route('attendance-summary.rebuild'),
                'export' => route('attendance-summary.export-payroll', array_filter($filters, fn ($value) => $value !== '' && $value !== null)),
            ],
        ]);
    }

    public function rebuild(RebuildAttendanceSummaryRequest $request): RedirectResponse
    {
        [$start, $end] = $this->report->cutoff((int) $request->validated('cutoff_month'), (int) $request->validated('cutoff_year'), (string) $request->validated('cutoff_type'));
        $back = redirect()->route('attendance-summary.index', $request->only(self::KEPT));

        try {
            $this->report->rebuild($start, $end);
        } catch (Throwable $exception) {
            Log::error('Attendance Summary rebuild failed', [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'group_name' => $request->input('group_name'),
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            return $back->withErrors(['attendance_summary' => 'Attendance Summary rebuild failed. No partial failed-date data was committed. Check storage/logs for the exact error.']);
        }

        return $back->with('success', 'Attendance summary rebuilt successfully for all Active payroll-included employees and roster coverage was verified.');
    }

    /** The printable per-employee export (React print page). */
    public function exportPayroll(Request $request): Response
    {
        $filters = $this->report->filters($request->query());
        [$start, $end, $cutoffLabel] = $this->report->cutoff($filters['cutoff_month'], $filters['cutoff_year'], $filters['cutoff_type']);
        $export = $this->report->export($start, $end, $filters);
        $time = fn ($value): ?string => $value ? Carbon::parse($value)->format('h:i A') : null;

        return Inertia::render('payroll/attendance-summary/export', [
            'cutoffLabel' => $cutoffLabel,
            'groupLabel' => $this->groups()[$filters['group_name']] ?? 'All Payroll Groups',
            'recordCount' => $export['rows']->count(),
            'stats' => collect($this->report->stats($start, $end, $filters))->map(fn ($value) => is_numeric($value) ? (float) $value : $value)->all(),
            'employees' => $export['people']->map(fn (array $row): array => [
                'id' => (int) $row['person']->id,
                'name' => $row['person']->payroll_display_name,
                'employee_no' => $row['person']->effective_employee_no,
                'biometric_id' => $row['person']->legacy_biometric_employee_id,
                'records' => $row['records']->map(fn (DailyAttendanceSummary $record): array => [
                    'day' => $record->work_date ? Carbon::parse($record->work_date)->format('D') : null,
                    'date' => $record->work_date ? Carbon::parse($record->work_date)->format('m/d') : null,
                    'in' => $time($record->actual_time_in),
                    'out' => $time($record->actual_time_out),
                    'worked_hours' => round(((int) $record->worked_minutes) / 60, 2),
                    'payable_days' => (float) $record->payable_days,
                    'status' => (string) ($record->attendance_status ?? ''),
                ])->values(),
                'totals' => $row['totals'],
            ])->values(),
            'printed' => now('Asia/Manila')->format('F d, Y h:i A'),
        ]);
    }

    /** @return array<string, string> */
    private function groups(): array
    {
        return collect(EmployeeBiometric::GROUP_LABELS)->mapWithKeys(fn (string $label, int $group): array => [(string) $group => $label])->all();
    }
}
