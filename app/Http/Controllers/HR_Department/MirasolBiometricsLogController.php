<?php

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Jobs\CrossChexSyncLogsJob;
use App\Models\EmployeePlottingSchedule;
use App\Models\MirasolBiometricsLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MirasolBiometricsLogController extends Controller
{
    public function index(Request $request)
    {
        [$defaultCutoffMonth, $defaultCutoffYear, $defaultCutoffType] = $this->getDefaultCutoff();

        $cutoffMonth = (int) ($request->cutoff_month ?: $defaultCutoffMonth);
        $cutoffYear = (int) ($request->cutoff_year ?: $defaultCutoffYear);
        $cutoffType = $request->cutoff_type ?: $defaultCutoffType;
        $search = trim((string) $request->q);

        [$startDate, $endDate, $cutoffLabel] = $this->resolveCutoffRange(
            $cutoffYear,
            $cutoffMonth,
            $cutoffType
        );

        $people = $this->buildPeopleSuggestions();

        $isSearch = ! empty($search);

        if (! $isSearch) {
            $rows = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                20,
                \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage(),
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );

            return view('hr_department.mirasol_logs.index', [
                'rows' => $rows,
                'people' => $people,
                'cutoffMonth' => $cutoffMonth,
                'cutoffYear' => $cutoffYear,
                'cutoffType' => $cutoffType,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'cutoffLabel' => $cutoffLabel,
                'search' => $search,
                'isSearch' => false,
            ]);
        }

        $logs = MirasolBiometricsLog::query()
            ->whereNotNull('check_time')
            ->whereBetween('check_time', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ])
            ->where(function ($q) use ($search) {
                $q->where('employee_name', 'like', "%{$search}%")
                    ->orWhere('employee_no', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('crosschex_id', 'like', "%{$search}%");
            })
            ->orderBy('employee_name')
            ->orderBy('check_time')
            ->get()
            ->groupBy(function ($log) {
                return $this->buildEmployeeKey($log->employee_no, $log->employee_id).'_'.
                    Carbon::parse($log->check_time)->toDateString();
            })
            ->map(function ($group) {
                $sorted = $group->sortBy('check_time')->values();
                $first = $sorted->first();
                $last = $sorted->last();

                $firstCheckTime = $first?->check_time ? Carbon::parse($first->check_time) : null;
                $lastCheckTime = $last?->check_time ? Carbon::parse($last->check_time) : null;

                return [
                    'employee_key' => $this->buildEmployeeKey($first->employee_no, $first->employee_id),
                    'biometric_employee_id' => $first->employee_id,
                    'employee_no' => $first->employee_no,
                    'employee_name' => $first->employee_name,
                    'log_date' => $firstCheckTime?->toDateString(),
                    'actual_time_in' => $firstCheckTime?->toDateTimeString(),
                    'actual_time_out' => $lastCheckTime?->toDateTimeString(),
                    'log_count' => $group->count(),
                    'has_logs' => true,
                ];
            });

        $schedules = EmployeePlottingSchedule::query()
            ->whereBetween('work_date', [
                $startDate->copy()->toDateString(),
                $endDate->copy()->toDateString(),
            ])
            ->where(function ($q) use ($search) {
                $q->where('employee_name', 'like', "%{$search}%")
                    ->orWhere('employee_no', 'like', "%{$search}%")
                    ->orWhere('biometric_employee_id', 'like', "%{$search}%")
                    ->orWhere('crosschex_id', 'like', "%{$search}%");
            })
            ->orderBy('employee_name')
            ->orderBy('work_date')
            ->get()
            ->keyBy(function ($schedule) {
                return $this->buildEmployeeKey($schedule->employee_no, $schedule->biometric_employee_id).'_'.
                    Carbon::parse($schedule->work_date)->toDateString();
            });

        $merged = [];

        foreach ($schedules as $key => $schedule) {
            $merged[$key] = [
                'employee_key' => $this->buildEmployeeKey($schedule->employee_no, $schedule->biometric_employee_id),
                'biometric_employee_id' => $schedule->biometric_employee_id,
                'employee_no' => $schedule->employee_no,
                'employee_name' => $schedule->employee_name,
                'log_date' => Carbon::parse($schedule->work_date)->toDateString(),

                'schedule_status' => $schedule->status,
                'shift_name' => $schedule->shift_name,
                'scheduled_time_in' => $schedule->time_in,
                'scheduled_time_out' => $schedule->time_out,
                'grace_minutes' => (int) ($schedule->grace_minutes ?? 15),
                'remarks' => $schedule->remarks,

                'actual_time_in' => null,
                'actual_time_out' => null,
                'log_count' => 0,
                'has_logs' => false,
                'has_schedule' => true,
            ];
        }

        foreach ($logs as $key => $logRow) {
            if (! isset($merged[$key])) {
                $merged[$key] = [
                    'employee_key' => $logRow['employee_key'],
                    'biometric_employee_id' => $logRow['biometric_employee_id'],
                    'employee_no' => $logRow['employee_no'],
                    'employee_name' => $logRow['employee_name'],
                    'log_date' => $logRow['log_date'],

                    'schedule_status' => null,
                    'shift_name' => null,
                    'scheduled_time_in' => null,
                    'scheduled_time_out' => null,
                    'grace_minutes' => 15,
                    'remarks' => null,

                    'actual_time_in' => $logRow['actual_time_in'],
                    'actual_time_out' => $logRow['actual_time_out'],
                    'log_count' => $logRow['log_count'],
                    'has_logs' => true,
                    'has_schedule' => false,
                ];
            } else {
                $merged[$key]['actual_time_in'] = $logRow['actual_time_in'];
                $merged[$key]['actual_time_out'] = $logRow['actual_time_out'];
                $merged[$key]['log_count'] = $logRow['log_count'];
                $merged[$key]['has_logs'] = true;
            }
        }

        $rows = collect($merged)
            ->map(function ($row) {
                return $this->decorateAttendanceRow($row);
            })
            ->sortBy([
                ['employee_name', 'asc'],
                ['log_date', 'asc'],
            ])
            ->values();

        $rows = $this->paginateCollection($rows, 20, $request);

        return view('hr_department.mirasol_logs.index', [
            'rows' => $rows,
            'people' => $people,
            'cutoffMonth' => $cutoffMonth,
            'cutoffYear' => $cutoffYear,
            'cutoffType' => $cutoffType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'cutoffLabel' => $cutoffLabel,
            'search' => $search,
            'isSearch' => true,
        ]);
    }

    public function startSync(Request $request)
    {
        try {
            $v = $request->validate([
                'from' => ['required', 'date'],
                'to' => ['required', 'date', 'after_or_equal:from'],
            ]);

            $from = Carbon::parse($v['from'])->startOfDay();
            $to = Carbon::parse($v['to'])->endOfDay();

            $lock = Cache::lock('crosschex-sync-start-lock', 10);

            if (! $lock->get()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Another sync request is already being started. Please wait.',
                ], 429);
            }

            try {
                // block if there is already an active running/queued sync
                $activeJobId = Cache::get('crosschex_active_job_id');
                if ($activeJobId) {
                    $activeStatus = Cache::get("crosschex_sync_status:{$activeJobId}");

                    if ($activeStatus && in_array($activeStatus['state'] ?? null, ['queued', 'running'])) {
                        return response()->json([
                            'ok' => false,
                            'message' => 'A CrossChex sync is already running.',
                            'jobId' => $activeJobId,
                            'status' => $activeStatus,
                        ], 409);
                    }
                }

                $jobId = (string) Str::uuid();
                $key = "crosschex_sync_status:{$jobId}";

                Cache::put($key, [
                    'state' => 'queued',
                    'message' => 'Queued...',
                    'from' => $from->toDateTimeString(),
                    'to' => $to->toDateTimeString(),
                    'page' => 0,
                    'pageCount' => null,
                    'saved' => 0,
                    'updated' => 0,
                    'percent' => 0,
                    'done' => false,
                    'error' => null,
                ], now()->addMinutes(60));

                Cache::put('crosschex_active_job_id', $jobId, now()->addMinutes(60));

                CrossChexSyncLogsJob::dispatch($jobId, $from->toDateTimeString(), $to->toDateTimeString());

                return response()->json([
                    'ok' => true,
                    'jobId' => $jobId,
                    'date_from' => $from->toDateString(),
                    'date_to' => $to->toDateString(),
                ]);
            } finally {
                optional($lock)->release();
            }
        } catch (ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('startSync failed', ['error' => $e->getMessage()]);

            return response()->json([
                'ok' => false,
                'message' => 'Failed to start sync. Check laravel.log.',
            ], 500);
        }
    }

    public function syncStatus(Request $request)
    {
        try {
            $v = $request->validate([
                'job' => ['required', 'string'],
            ]);

            $key = "crosschex_sync_status:{$v['job']}";
            $status = Cache::get($key);

            if (! $status) {
                return response()->json([
                    'ok' => false,
                    'state' => 'unknown',
                    'message' => 'No status found (maybe expired).',
                ], 404);
            }

            return response()->json(['ok' => true] + $status);
        } catch (ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('syncStatus failed', ['error' => $e->getMessage()]);

            return response()->json([
                'ok' => false,
                'message' => 'Failed to read status. Check laravel.log.',
            ], 500);
        }
    }

    private function decorateAttendanceRow(array $row): array
    {
        $date = Carbon::parse($row['log_date']);

        $scheduledIn = ! empty($row['scheduled_time_in'])
            ? Carbon::parse($date->toDateString().' '.$row['scheduled_time_in'])
            : null;

        $scheduledOut = ! empty($row['scheduled_time_out'])
            ? Carbon::parse($date->toDateString().' '.$row['scheduled_time_out'])
            : null;

        $actualIn = ! empty($row['actual_time_in'])
            ? Carbon::parse($row['actual_time_in'])
            : null;

        $actualOut = ! empty($row['actual_time_out'])
            ? Carbon::parse($row['actual_time_out'])
            : null;

        $graceMinutes = (int) ($row['grace_minutes'] ?? 15);

        $lateMinutes = 0;
        $undertimeMinutes = 0;
        $workedMinutes = null;
        $attendanceNote = null;
        $attendanceClass = 'secondary';

        if ($actualIn && $actualOut) {
            $workedMinutes = $actualIn->diffInMinutes($actualOut, false);
            if ($workedMinutes <= 0) {
                $workedMinutes = null;
            }
        }

        if (! $row['has_schedule'] && $row['has_logs']) {
            $attendanceNote = 'No plotted schedule found for this cutoff date. Please coordinate with Payroll / Admin to set the employee schedule.';
            $attendanceClass = 'warning';
        } elseif ($row['has_schedule'] && ! $row['has_logs'] && $row['schedule_status'] === 'scheduled') {
            $attendanceNote = 'Absent';
            $attendanceClass = 'danger';
        } elseif (in_array($row['schedule_status'], ['rest_day', 'leave', 'holiday'])) {
            if ($row['has_logs']) {
                $attendanceNote = 'Biometric log detected on a non-working plotted status.';
                $attendanceClass = 'info';
            } else {
                $attendanceNote = ucwords(str_replace('_', ' ', $row['schedule_status']));
                $attendanceClass = 'secondary';
            }
        } elseif ($row['schedule_status'] === 'scheduled') {
            if ($actualIn && $scheduledIn) {
                $allowedIn = $scheduledIn->copy()->addMinutes($graceMinutes);

                if ($actualIn->gt($allowedIn)) {
                    $lateMinutes = $allowedIn->diffInMinutes($actualIn);
                }
            }

            if ($actualOut && $scheduledOut && $actualOut->lt($scheduledOut)) {
                $undertimeMinutes = $actualOut->diffInMinutes($scheduledOut);
            }

            if ($row['has_logs'] && (int) $row['log_count'] < 2) {
                $attendanceNote = 'Incomplete biometric logs detected for this scheduled workday.';
                $attendanceClass = 'warning';
            } else {
                $parts = [];

                if ($lateMinutes > 0) {
                    $parts[] = 'Late';
                }

                if ($undertimeMinutes > 0) {
                    $parts[] = 'Undertime';
                }

                if (empty($parts) && $row['has_logs']) {
                    $parts[] = 'On Time';
                }

                $attendanceNote = ! empty($parts)
                    ? implode(' / ', $parts)
                    : 'No attendance remark.';
                $attendanceClass = ($lateMinutes > 0 || $undertimeMinutes > 0) ? 'warning' : 'success';
            }
        } else {
            $attendanceNote = 'No schedule and no attendance record available for this date.';
            $attendanceClass = 'secondary';
        }

        $row['late_minutes'] = $lateMinutes;
        $row['undertime_minutes'] = $undertimeMinutes;
        $row['worked_minutes'] = $workedMinutes;
        $row['worked_hours_label'] = $this->formatMinutesToHours($workedMinutes);
        $row['late_label'] = $lateMinutes > 0 ? $this->formatMinutesToHours($lateMinutes) : '—';
        $row['undertime_label'] = $undertimeMinutes > 0 ? $this->formatMinutesToHours($undertimeMinutes) : '—';
        $row['attendance_note'] = $attendanceNote;
        $row['attendance_class'] = $attendanceClass;

        return $row;
    }

    private function formatMinutesToHours(?int $minutes): string
    {
        if ($minutes === null || $minutes <= 0) {
            return '—';
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }

    private function buildPeopleSuggestions()
    {
        $logPeople = MirasolBiometricsLog::query()
            ->select('employee_no', 'employee_name', 'employee_id')
            ->whereNotNull('employee_name')
            ->get()
            ->map(function ($row) {
                return [
                    'employee_no' => $row->employee_no,
                    'employee_name' => $row->employee_name,
                    'biometric_employee_id' => $row->employee_id,
                ];
            });

        $schedulePeople = EmployeePlottingSchedule::query()
            ->select('employee_no', 'employee_name', 'biometric_employee_id')
            ->whereNotNull('employee_name')
            ->get()
            ->map(function ($row) {
                return [
                    'employee_no' => $row->employee_no,
                    'employee_name' => $row->employee_name,
                    'biometric_employee_id' => $row->biometric_employee_id,
                ];
            });

        return $logPeople
            ->merge($schedulePeople)
            ->unique(function ($row) {
                return strtolower(
                    ($row['employee_name'] ?? '').'|'.
                    ($row['employee_no'] ?? '').'|'.
                    ($row['biometric_employee_id'] ?? '')
                );
            })
            ->sortBy('employee_name')
            ->values();
    }

    private function buildEmployeeKey($employeeNo, $biometricEmployeeId): string
    {
        if (! empty($employeeNo)) {
            return 'EMPNO:'.$employeeNo;
        }

        return 'BIO:'.($biometricEmployeeId ?: 'UNKNOWN');
    }

    private function paginateCollection($items, int $perPage, Request $request): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $total = $items->count();
        $results = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function resolveCutoffRange(int $year, int $month, string $cutoffType): array
    {
        if ($cutoffType === '11_25') {
            $startDate = Carbon::create($year, $month, 11)->startOfDay();
            $endDate = Carbon::create($year, $month, 25)->startOfDay();
            $label = $startDate->format('F d, Y').' - '.$endDate->format('F d, Y');
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
}
