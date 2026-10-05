<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Models\DailyAttendanceSummary;
use App\Models\EmployeeBiometric;
use App\Repositories\Contracts\Biometrics\EmployeeBiometricRepositoryInterface;
use App\Repositories\Contracts\Payroll\AttendanceSummaryRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Payroll → Summary: the daily attendance summary of a cutoff (list, stat cards, roster
 * coverage, the printable per-employee export) and rebuilding it. The summary itself is
 * computed by DailyAttendanceSummaryService.
 */
final class AttendanceSummaryReportService
{
    public const FILTERS = ['search', 'status', 'day_type', 'group_name'];

    public const STATUS_OPTIONS = [
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

    public const DAY_TYPE_OPTIONS = [
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

    public function __construct(
        private readonly AttendanceSummaryRepositoryInterface $summaries,
        private readonly EmployeeBiometricRepositoryInterface $biometrics,
        private readonly PayrollPeriodService $periods,
        private readonly DailyAttendanceSummaryService $builder,
    ) {}

    /**
     * The cutoff (default: the current one) and list filters from the request.
     *
     * @param  array<string, mixed>  $input
     * @return array{cutoff_month: int, cutoff_year: int, cutoff_type: string, search: string, status: string, day_type: string, group_name: string}
     */
    public function filters(array $input): array
    {
        [$month, $year, $type] = $this->periods->getDefaultCutoff();
        $filters = [
            'cutoff_month' => (int) (($input['cutoff_month'] ?? null) ?: $month),
            'cutoff_year' => (int) (($input['cutoff_year'] ?? null) ?: $year),
            'cutoff_type' => (string) (($input['cutoff_type'] ?? null) ?: $type),
        ];
        foreach (self::FILTERS as $key) {
            $filters[$key] = trim((string) ($input[$key] ?? ''));
        }

        /** @var array{cutoff_month: int, cutoff_year: int, cutoff_type: string, search: string, status: string, day_type: string, group_name: string} $filters */
        return $filters;
    }

    /**
     * Cutoff dates and label. Month / year are clamped; an unknown type means the 11-25 cutoff.
     *
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    public function cutoff(int $month, int $year, string $type): array
    {
        $type = in_array($type, ['first', 'second'], true) ? $type : 'first';
        [$start, $end] = $this->periods->resolveCutoffRange(max(1, min(12, $month)), max(2000, min(2100, $year)), $type);
        $display = $type === 'first'
            ? config('payroll.cutoff_display.first.full', '2nd Cutoff (11-25)')
            : config('payroll.cutoff_display.second.full', '1st Cutoff (26-10)');

        return [$start, $end, $start->format('F d, Y').' - '.$end->format('F d, Y').' | '.$display];
    }

    /**
     * @param  array{search: string, status: string, day_type: string, group_name: string}  $filters
     * @return LengthAwarePaginator<int, DailyAttendanceSummary>
     */
    public function paginate(Carbon $start, Carbon $end, array $filters): LengthAwarePaginator
    {
        return $this->summaries->paginate($start, $end, $this->listFilters($filters));
    }

    /**
     * Stat cards plus roster coverage (which ignores search / status / day type).
     *
     * @param  array{search: string, status: string, day_type: string, group_name: string}  $filters
     * @return array<string, mixed>
     */
    public function stats(Carbon $start, Carbon $end, array $filters): array
    {
        return array_merge($this->summaries->stats($start, $end, $this->listFilters($filters)), $this->rosterCoverage($start, $end, $filters['group_name']));
    }

    /**
     * Printable export: every eligible person (even without rows, so a missing schedule shows)
     * with their rows and totals. With a search / status / day type filter, only matching people.
     *
     * @param  array{search: string, status: string, day_type: string, group_name: string}  $filters
     * @return array{rows: Collection<int, DailyAttendanceSummary>, people: Collection<int, array<string, mixed>>}
     */
    public function export(Carbon $start, Carbon $end, array $filters): array
    {
        $rows = $this->summaries->allMatching($start, $end, $this->listFilters($filters));
        $narrowed = $filters['search'] !== '' || $filters['status'] !== '' || $filters['day_type'] !== '';
        $ids = $narrowed ? $rows->pluck('employee_biometric_id')->filter()->map(fn ($id): int => (int) $id)->unique()->values() : null;
        $byPerson = $rows->groupBy(fn (DailyAttendanceSummary $row): int => (int) $row->employee_biometric_id);

        $people = $this->biometrics->payrollActive($filters['group_name'], $ids)->map(function (EmployeeBiometric $person) use ($byPerson): array {
            /** @var Collection<int, DailyAttendanceSummary> $records */
            $records = $byPerson->get((int) $person->id, collect());

            return [
                'person' => $person,
                'records' => $records,
                'totals' => [
                    'absent' => $records->where('attendance_status', 'absent')->count(),
                    'review' => $records->whereIn('attendance_status', ['half_day', 'incomplete_log', 'no_schedule', 'holiday_unpaid', 'absent'])->count(),
                    'holiday_paid' => $records->filter(fn (DailyAttendanceSummary $record): bool => (bool) $record->is_holiday && (float) $record->payable_days > 0)->count(),
                    'holiday_unpaid' => $records->where('attendance_status', 'holiday_unpaid')->count(),
                    'late_minutes' => (float) $records->sum('late_minutes'),
                    'undertime_minutes' => (float) $records->sum('undertime_minutes'),
                    'payable_days' => (float) $records->sum('payable_days'),
                ],
            ];
        });

        return ['rows' => $rows, 'people' => $people];
    }

    /** Rebuilds every payroll-included person's summary for the cutoff (all or nothing per date). */
    public function rebuild(Carbon $start, Carbon $end): void
    {
        @ini_set('max_execution_time', '300');
        $this->builder->buildForPeriod($start, $end);
    }

    /**
     * @return array{eligible_employees: int, summary_employees: int, missing_summary_employees: int, missing_summary_employee_list: list<array<string, mixed>>}
     */
    private function rosterCoverage(Carbon $start, Carbon $end, string $group): array
    {
        $eligible = $this->biometrics->payrollActive($group)->pluck('id')->map(fn ($id): int => (int) $id)->values();
        $withRows = $this->summaries->employeeIdsWithRows($start, $end, $eligible);
        $missing = $eligible->diff($withRows)->values();

        return [
            'eligible_employees' => $eligible->count(),
            'summary_employees' => $withRows->count(),
            'missing_summary_employees' => $missing->count(),
            'missing_summary_employee_list' => $this->biometrics->findManyInDirectoryOrder($missing)
                ->map(fn (EmployeeBiometric $person): array => [
                    'id' => (int) $person->id,
                    'employee_no' => $person->effective_employee_no,
                    'employee_name' => $person->payroll_display_name,
                    'group_name' => $person->group_name,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{search: string, status: string, day_type: string, group_name: string}
     */
    private function listFilters(array $filters): array
    {
        return [
            'search' => (string) $filters['search'],
            'status' => (string) $filters['status'],
            'day_type' => (string) $filters['day_type'],
            'group_name' => (string) $filters['group_name'],
        ];
    }
}
