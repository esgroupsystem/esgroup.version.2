<?php

declare(strict_types=1);

namespace App\Http\Controllers\Biometrics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Biometrics\StartBiometricsSyncRequest;
use App\Http\Requests\Biometrics\StepBiometricsSyncRequest;
use App\Services\Biometrics\BiometricsAttendanceService;
use App\Services\Biometrics\CrossChexSyncCoordinator;
use App\Services\CrossChexServiceFactory;
use App\Support\PayrollEmployeeNameFormatter;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;
use Throwable;

/** Biometrics → Biometrics Sync (mirasol-logs.*): cutoff attendance search and the CrossChex sync. */
final class BiometricsSyncController extends Controller
{
    private const PER_PAGE = 20;

    public function __construct(
        private readonly BiometricsAttendanceService $attendanceService,
        private readonly CrossChexSyncCoordinator $syncCoordinator,
        private readonly CrossChexServiceFactory $crossChexFactory,
    ) {}

    public function index(Request $request): Response
    {
        [$defaultMonth, $defaultYear, $defaultType] = $this->attendanceService->defaultCutoff();
        $month = (int) ($request->input('cutoff_month') ?: $defaultMonth);
        $year = (int) ($request->input('cutoff_year') ?: $defaultYear);
        $type = (string) ($request->input('cutoff_type') ?: $defaultType);
        $search = trim((string) $request->input('q'));
        [$start, $end, $label] = $this->attendanceService->cutoffRange($year, $month, $type);

        $rows = $search === '' ? collect() : $this->attendanceService->rows($search, $start, $end);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginator = new LengthAwarePaginator(
            $rows->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values(),
            $rows->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $time = fn (mixed $value): ?string => ! empty($value) ? Carbon::parse($value)->format('h:i A') : null;

        return Inertia::render('biometrics/sync/index', [
            'rows' => $paginator->through(fn (array $row): array => [
                'employee_name' => PayrollEmployeeNameFormatter::display($row['employee_name'] ?? null),
                'employee_no' => $row['employee_no'] ?: null,
                'date_label' => Carbon::parse($row['log_date'])->format('F d, Y (l)'),
                'remarks' => $row['remarks'],
                'shift_name' => $row['shift_name'],
                'shift_mode' => $row['shift_mode'],
                'schedule_status' => $row['schedule_status'],
                'scheduled_time_in' => $time($row['scheduled_time_in']),
                'scheduled_time_out' => $time($row['scheduled_time_out']),
                'grace_minutes' => (int) $row['grace_minutes'],
                'paid_hours' => number_format(((int) $row['paid_work_minutes']) / 60, 0),
                'day_off' => $row['day_off'],
                'actual_time_in' => $time($row['actual_time_in']),
                'actual_time_out' => $time($row['actual_time_out']),
                'worked_hours_label' => $row['worked_hours_label'],
                'required_hours_label' => $row['required_hours_label'],
                'late_minutes' => (int) $row['late_minutes'],
                'late_label' => $row['late_label'],
                'undertime_minutes' => (int) $row['undertime_minutes'],
                'undertime_label' => $row['undertime_label'],
                'attendance_note' => $row['attendance_note'],
                'attendance_class' => $row['attendance_class'],
            ]),
            'people' => $this->attendanceService->people()->flatMap(function (array $person): array {
                $name = PayrollEmployeeNameFormatter::display($person['employee_name'] ?? null);
                $options = [];
                if (! empty($person['employee_name'])) {
                    $options[] = ['value' => $person['employee_name'], 'label' => $name.(! empty($person['employee_no']) ? ' - '.$person['employee_no'] : '')];
                }
                if (! empty($person['employee_no'])) {
                    $options[] = ['value' => (string) $person['employee_no'], 'label' => $name];
                }

                return $options;
            })->values(),
            'filters' => ['q' => $search, 'cutoff_month' => $month, 'cutoff_year' => $year, 'cutoff_type' => $type],
            'cutoffLabel' => $label,
            'cutoffTypes' => [
                '26_10' => config('payroll.cutoff_display_by_range.26_10', '1st Cutoff (26-10)'),
                '11_25' => config('payroll.cutoff_display_by_range.11_25', '2nd Cutoff (11-25)'),
            ],
            'isSearch' => $search !== '',
            'syncAccounts' => array_values($this->crossChexFactory->configuredAccounts()),
            'today' => now()->toDateString(),
            'can' => ['sync' => (bool) $request->user()?->can('mirasol-logs.sync')],
            'urls' => [
                'index' => route('mirasol-logs.index'),
                'syncStart' => route('mirasol-logs.sync-start'),
                'syncStep' => route('mirasol-logs.sync-step'),
                'syncStatus' => route('mirasol-logs.sync-status'),
            ],
        ]);
    }

    public function startSync(StartBiometricsSyncRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $state = $this->syncCoordinator->start(
                from: Carbon::parse($validated['from']),
                to: Carbon::parse($validated['to']),
                requestedAccounts: $validated['accounts'],
            );

            return response()->json(['ok' => true, ...$state]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            Log::error('Biometrics sync start failed.', ['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);

            return response()->json(['ok' => false, 'message' => 'Unable to start biometric synchronization. Check laravel.log.'], 500);
        }
    }

    public function syncStep(StepBiometricsSyncRequest $request): JsonResponse
    {
        $state = $this->syncCoordinator->step((string) $request->validated('job'));

        return response()->json(['ok' => ($state['state'] ?? null) !== 'error', ...$state]);
    }

    public function syncStatus(StepBiometricsSyncRequest $request): JsonResponse
    {
        $state = $this->syncCoordinator->status((string) $request->validated('job'));

        if ($state === null) {
            return response()->json(['ok' => false, 'state' => 'unknown', 'message' => 'Sync session was not found or has expired.'], 404);
        }

        return response()->json(['ok' => ($state['state'] ?? null) !== 'error', ...$state]);
    }
}
