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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MirasolBiometricsLogController extends Controller
{
    private const FLEXIBLE_REQUIRED_MINUTES = 540; // 9 hours

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
        $isSearch = $search !== '';

        if (! $isSearch) {
            $rows = $this->emptyPaginator($request);

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

        $matchedPeople = $this->resolvePeopleFromSearch($search);

        if ($matchedPeople->isEmpty()) {
            $rows = $this->emptyPaginator($request);

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

        $employeeNos = $matchedPeople
            ->pluck('employee_no')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        $biometricEmployeeIds = $matchedPeople
            ->pluck('biometric_employee_id')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        $logsByEmployeeDate = $this->getLogsByEmployeeDate(
            $employeeNos,
            $biometricEmployeeIds,
            $startDate,
            $endDate
        );

        [$dateSchedules, $permanentSchedules] = $this->getSchedules(
            $employeeNos,
            $biometricEmployeeIds,
            $startDate,
            $endDate
        );

        $rows = collect();

        foreach ($matchedPeople as $person) {
            $employeeKey = $this->buildEmployeeKey(
                $person['employee_no'] ?? null,
                $person['biometric_employee_id'] ?? null
            );

            foreach ($this->dateRange($startDate, $endDate) as $date) {
                $dateString = $date->toDateString();
                $logKey = $employeeKey.'_'.$dateString;

                $schedule = $dateSchedules->get($logKey)
                    ?? $permanentSchedules->get($employeeKey);

                $logRow = $logsByEmployeeDate->get($logKey);

                $row = [
                    'employee_key' => $employeeKey,
                    'biometric_employee_id' => $person['biometric_employee_id'] ?? null,
                    'employee_no' => $person['employee_no'] ?? null,
                    'employee_name' => $person['employee_name'] ?? null,
                    'log_date' => $dateString,

                    'has_schedule' => $schedule !== null,
                    'schedule_status' => null,
                    'shift_name' => null,
                    'scheduled_time_in' => null,
                    'scheduled_time_out' => null,
                    'grace_minutes' => 15,
                    'day_off' => null,
                    'remarks' => null,

                    'actual_time_in' => $logRow['actual_time_in'] ?? null,
                    'actual_time_out' => $logRow['actual_time_out'] ?? null,
                    'log_count' => $logRow['log_count'] ?? 0,
                    'has_logs' => ! empty($logRow),
                ];

                if ($schedule) {
                    $row = array_merge($row, $this->schedulePayload($schedule, $date));
                }

                $rows->push($this->decorateAttendanceRow($row));
            }
        }

        $rows = $rows
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
            $validated = $request->validate([
                'from' => ['required', 'date'],
                'to' => ['required', 'date', 'after_or_equal:from'],
            ]);

            $from = Carbon::parse($validated['from'])->startOfDay();
            $to = Carbon::parse($validated['to'])->endOfDay();

            $accounts = array_keys(config('services.crosschex.accounts', []));

            if (empty($accounts)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No CrossChex accounts configured.',
                ], 422);
            }

            $jobId = (string) Str::uuid();
            $key = "crosschex_sync_status:{$jobId}";

            Cache::put($key, [
                'state' => 'queued',
                'message' => 'Queued for all CrossChex accounts...',
                'from' => $from->toDateTimeString(),
                'to' => $to->toDateTimeString(),
                'accounts' => $accounts,
                'page' => 0,
                'pageCount' => null,
                'saved' => 0,
                'updated' => 0,
                'percent' => 0,
                'done' => false,
                'error' => null,
            ], now()->addMinutes(60));

            Cache::put('crosschex_active_job_id', $jobId, now()->addMinutes(60));

            CrossChexSyncLogsJob::dispatch(
                $jobId,
                $from->toDateTimeString(),
                $to->toDateTimeString(),
                $accounts
            );

            return response()->json([
                'ok' => true,
                'jobId' => $jobId,
                'accounts' => $accounts,
                'message' => 'Sync started successfully.',
            ]);
        } catch (\Throwable $e) {
            Log::error('startSync failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
                'file' => config('app.debug') ? $e->getFile() : null,
                'line' => config('app.debug') ? $e->getLine() : null,
            ], 500);
        }
    }

    public function syncStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'job' => ['required', 'string'],
            ]);

            $key = "crosschex_sync_status:{$validated['job']}";
            $status = Cache::get($key);

            if (! $status) {
                return response()->json([
                    'ok' => false,
                    'state' => 'unknown',
                    'message' => 'No status found. It may have expired.',
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
            Log::error('syncStatus failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Failed to read status. Check laravel.log.',
            ], 500);
        }
    }

    private function getLogsByEmployeeDate($employeeNos, $biometricEmployeeIds, Carbon $startDate, Carbon $endDate)
    {
        return MirasolBiometricsLog::query()
            ->whereNotNull('check_time')
            ->whereBetween('check_time', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ])
            ->where(function ($query) use ($employeeNos, $biometricEmployeeIds) {
                if ($employeeNos->isNotEmpty()) {
                    $query->orWhereIn(DB::raw('TRIM(employee_no)'), $employeeNos->all());
                }

                if ($biometricEmployeeIds->isNotEmpty()) {
                    $query->orWhereIn(DB::raw('TRIM(employee_id)'), $biometricEmployeeIds->all());
                }
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
                    'actual_time_out' => $sorted->count() > 1 ? $lastCheckTime?->toDateTimeString() : null,
                    'log_count' => $group->count(),
                    'has_logs' => true,
                ];
            });
    }

    private function getSchedules($employeeNos, $biometricEmployeeIds, Carbon $startDate, Carbon $endDate): array
    {
        $scheduleQuery = EmployeePlottingSchedule::query()
            ->where(function ($query) use ($employeeNos, $biometricEmployeeIds) {
                if ($employeeNos->isNotEmpty()) {
                    $query->orWhereIn(DB::raw('TRIM(employee_no)'), $employeeNos->all());
                }

                if ($biometricEmployeeIds->isNotEmpty()) {
                    $query->orWhereIn(DB::raw('TRIM(biometric_employee_id)'), $biometricEmployeeIds->all());
                }
            })
            ->orderByDesc('updated_at')
            ->get();

        $dateSchedules = $scheduleQuery
            ->filter(fn ($schedule) => ! empty($schedule->work_date))
            ->filter(function ($schedule) use ($startDate, $endDate) {
                $workDate = Carbon::parse($schedule->work_date)->toDateString();

                return $workDate >= $startDate->toDateString()
                    && $workDate <= $endDate->toDateString();
            })
            ->keyBy(function ($schedule) {
                return $this->buildEmployeeKey($schedule->employee_no, $schedule->biometric_employee_id).'_'.
                    Carbon::parse($schedule->work_date)->toDateString();
            });

        $permanentSchedules = $scheduleQuery
            ->unique(function ($schedule) {
                return $this->buildEmployeeKey($schedule->employee_no, $schedule->biometric_employee_id);
            })
            ->keyBy(function ($schedule) {
                return $this->buildEmployeeKey($schedule->employee_no, $schedule->biometric_employee_id);
            });

        return [$dateSchedules, $permanentSchedules];
    }

    private function schedulePayload(EmployeePlottingSchedule $schedule, Carbon $date): array
    {
        $shiftName = $schedule->shift_name ?: 'Regular Shift';
        $dayOff = $schedule->day_off ?: null;
        $status = $schedule->status ?: 'scheduled';

        if ($dayOff && strtolower($dayOff) === strtolower($date->format('l'))) {
            $status = 'rest_day';
        }

        return [
            'has_schedule' => true,
            'schedule_status' => $status,
            'shift_name' => $shiftName,
            'scheduled_time_in' => $this->normalizeTime($schedule->time_in),
            'scheduled_time_out' => $this->normalizeTime($schedule->time_out),
            'grace_minutes' => (int) ($schedule->grace_minutes ?? 15),
            'day_off' => $dayOff,
            'remarks' => $schedule->remarks,
        ];
    }

    private function decorateAttendanceRow(array $row): array
    {
        $date = Carbon::parse($row['log_date']);
        $shiftName = $row['shift_name'] ?? null;
        $status = $row['schedule_status'] ?? null;
        $isFlexible = $this->isFlexibleShift($shiftName);
        $isRegular = $this->isRegularShift($shiftName);

        $scheduledIn = ! empty($row['scheduled_time_in'])
            ? Carbon::parse($date->toDateString().' '.$row['scheduled_time_in'])
            : null;

        $scheduledOut = ! empty($row['scheduled_time_out'])
            ? Carbon::parse($date->toDateString().' '.$row['scheduled_time_out'])
            : null;

        if ($scheduledIn && $scheduledOut && $scheduledOut->lessThanOrEqualTo($scheduledIn)) {
            $scheduledOut->addDay();
        }

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
        $attendanceNote = 'No attendance remark.';
        $attendanceClass = 'secondary';

        if ($actualIn && $actualOut) {
            $workedMinutes = $actualIn->diffInMinutes($actualOut, false);

            if ($workedMinutes <= 0) {
                $workedMinutes = null;
            }
        }

        if (! $row['has_schedule'] && $row['has_logs']) {
            $attendanceNote = 'No plotted schedule found.';
            $attendanceClass = 'warning';
        } elseif (! $row['has_schedule'] && ! $row['has_logs']) {
            $attendanceNote = 'No schedule and no biometric log.';
            $attendanceClass = 'secondary';
        } elseif (in_array($status, ['rest_day', 'leave', 'holiday'], true)) {
            if ($row['has_logs']) {
                $attendanceNote = 'Biometric log detected on '.ucwords(str_replace('_', ' ', $status)).'.';
                $attendanceClass = 'info';
            } else {
                $attendanceNote = ucwords(str_replace('_', ' ', $status));
                $attendanceClass = 'secondary';
            }
        } elseif ($status === 'scheduled') {
            if (! $row['has_logs']) {
                $attendanceNote = 'Absent';
                $attendanceClass = 'danger';
            } elseif ((int) $row['log_count'] < 2) {
                $attendanceNote = 'Incomplete biometric logs.';
                $attendanceClass = 'warning';
            } elseif ($isFlexible) {
                $requiredMinutes = self::FLEXIBLE_REQUIRED_MINUTES;

                if ($workedMinutes === null) {
                    $attendanceNote = 'Incomplete biometric logs.';
                    $attendanceClass = 'warning';
                } elseif ($workedMinutes >= $requiredMinutes) {
                    $attendanceNote = 'Completed Flexible 9 Hours';
                    $attendanceClass = 'success';
                } else {
                    $undertimeMinutes = $requiredMinutes - $workedMinutes;
                    $attendanceNote = 'Incomplete Flexible Hours';
                    $attendanceClass = 'warning';
                }
            } elseif ($isRegular) {
                if (! $scheduledIn || ! $scheduledOut) {
                    $attendanceNote = 'Regular Shift needs plotted Time In and Time Out.';
                    $attendanceClass = 'warning';
                } else {
                    $allowedIn = $scheduledIn->copy()->addMinutes($graceMinutes);

                    if ($actualIn && $actualIn->gt($allowedIn)) {
                        $lateMinutes = $allowedIn->diffInMinutes($actualIn);
                    }

                    if ($actualOut && $actualOut->lt($scheduledOut)) {
                        $undertimeMinutes = $actualOut->diffInMinutes($scheduledOut);
                    }

                    $parts = [];

                    if ($lateMinutes > 0) {
                        $parts[] = 'Late';
                    }

                    if ($undertimeMinutes > 0) {
                        $parts[] = 'Undertime';
                    }

                    if (empty($parts)) {
                        $parts[] = 'On Time';
                    }

                    $attendanceNote = implode(' / ', $parts);
                    $attendanceClass = ($lateMinutes > 0 || $undertimeMinutes > 0) ? 'warning' : 'success';
                }
            } else {
                $attendanceNote = 'Unknown shift type.';
                $attendanceClass = 'warning';
            }
        }

        $row['shift_mode'] = $isFlexible ? 'Flexible' : 'Regular';
        $row['required_minutes'] = $isFlexible ? self::FLEXIBLE_REQUIRED_MINUTES : null;
        $row['required_hours_label'] = $isFlexible ? '09:00' : '—';

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

    private function buildPeopleSuggestions()
    {
        $logPeople = MirasolBiometricsLog::query()
            ->selectRaw("
                MIN(employee_id) AS biometric_employee_id,
                TRIM(employee_no) AS employee_no,
                MIN(NULLIF(TRIM(employee_name), '')) AS employee_name
            ")
            ->whereNotNull('employee_name')
            ->whereRaw("TRIM(employee_name) <> ''")
            ->groupBy(DB::raw('TRIM(employee_no)'))
            ->get()
            ->map(function ($row) {
                return [
                    'employee_no' => $row->employee_no,
                    'employee_name' => $row->employee_name,
                    'biometric_employee_id' => $row->biometric_employee_id,
                ];
            });

        $schedulePeople = EmployeePlottingSchedule::query()
            ->selectRaw("
                MIN(biometric_employee_id) AS biometric_employee_id,
                TRIM(employee_no) AS employee_no,
                MIN(NULLIF(TRIM(employee_name), '')) AS employee_name
            ")
            ->whereNotNull('employee_name')
            ->whereRaw("TRIM(employee_name) <> ''")
            ->groupBy(DB::raw('TRIM(employee_no)'))
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
            ->filter(fn ($row) => ! empty($row['employee_name']) || ! empty($row['employee_no']))
            ->unique(function ($row) {
                return $this->buildEmployeeKey($row['employee_no'] ?? null, $row['biometric_employee_id'] ?? null);
            })
            ->sortBy('employee_name')
            ->values();
    }

    private function resolvePeopleFromSearch(string $search)
    {
        $logPeople = MirasolBiometricsLog::query()
            ->selectRaw("
                MIN(employee_id) AS biometric_employee_id,
                TRIM(employee_no) AS employee_no,
                MIN(NULLIF(TRIM(employee_name), '')) AS employee_name
            ")
            ->where(function ($query) use ($search) {
                $query->where('employee_name', 'like', "%{$search}%")
                    ->orWhere('employee_no', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('crosschex_id', 'like', "%{$search}%");
            })
            ->groupBy(DB::raw('TRIM(employee_no)'))
            ->get()
            ->map(function ($row) {
                return [
                    'employee_no' => $row->employee_no,
                    'employee_name' => $row->employee_name,
                    'biometric_employee_id' => $row->biometric_employee_id,
                ];
            });

        $schedulePeople = EmployeePlottingSchedule::query()
            ->selectRaw("
                MIN(biometric_employee_id) AS biometric_employee_id,
                TRIM(employee_no) AS employee_no,
                MIN(NULLIF(TRIM(employee_name), '')) AS employee_name
            ")
            ->where(function ($query) use ($search) {
                $query->where('employee_name', 'like', "%{$search}%")
                    ->orWhere('employee_no', 'like', "%{$search}%")
                    ->orWhere('biometric_employee_id', 'like', "%{$search}%")
                    ->orWhere('crosschex_id', 'like', "%{$search}%");
            })
            ->groupBy(DB::raw('TRIM(employee_no)'))
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
            ->filter(fn ($row) => ! empty($row['employee_name']) || ! empty($row['employee_no']))
            ->unique(function ($row) {
                return $this->buildEmployeeKey($row['employee_no'] ?? null, $row['biometric_employee_id'] ?? null);
            })
            ->sortBy('employee_name')
            ->values();
    }

    private function buildEmployeeKey($employeeNo, $biometricEmployeeId): string
    {
        $employeeNo = trim((string) $employeeNo);
        $biometricEmployeeId = trim((string) $biometricEmployeeId);

        if ($employeeNo !== '') {
            return 'EMPNO:'.$employeeNo;
        }

        return 'BIO:'.($biometricEmployeeId !== '' ? $biometricEmployeeId : 'UNKNOWN');
    }

    private function isFlexibleShift(?string $shiftName): bool
    {
        return str_contains(strtolower((string) $shiftName), 'flexible');
    }

    private function isRegularShift(?string $shiftName): bool
    {
        return ! $this->isFlexibleShift($shiftName);
    }

    private function normalizeTime($time): ?string
    {
        if (empty($time)) {
            return null;
        }

        return Carbon::parse($time)->format('H:i');
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

    private function dateRange(Carbon $startDate, Carbon $endDate)
    {
        $dates = collect();
        $current = $startDate->copy()->startOfDay();

        while ($current->lte($endDate)) {
            $dates->push($current->copy());
            $current->addDay();
        }

        return $dates;
    }

    private function emptyPaginator(Request $request): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            collect(),
            0,
            20,
            LengthAwarePaginator::resolveCurrentPage(),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
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
