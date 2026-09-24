<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\EmployeeBiometric;
use App\Models\MirasolBiometricsLog;
use App\Services\Biometrics\EmployeeBiometricIdentityService;
use App\Services\Payroll\DailyAttendanceSummaryService;
use App\Support\PayrollEmployeeNameFormatter;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Manual WFH encoding writes punches into mirasol_biometrics_logs.
 *
 * CrossChex stores a per-punch hash in crosschex_id (unique per account), so
 * employees are identified here by their EmployeeBiometric record and every
 * manual punch receives its own MANUAL-* crosschex_id. Attendance matches the
 * punches through employee_no, exactly like device logs.
 */
class ManualBiometricsEncodingController extends Controller
{
    public const DEVICE_SN = 'WFH-MANUAL';

    public const DEVICE_NAME = 'WFH Manual Encoding';

    public function __construct(
        private readonly EmployeeBiometricIdentityService $identityService,
        private readonly DailyAttendanceSummaryService $dailyAttendanceSummaryService,
    ) {}

    public function index(Request $request): Response
    {
        [$defaultCutoffMonth, $defaultCutoffYear, $defaultCutoffType] = $this->getDefaultCutoff();

        $cutoffMonth = (int) ($request->cutoff_month ?: $defaultCutoffMonth);
        $cutoffYear = (int) ($request->cutoff_year ?: $defaultCutoffYear);
        $cutoffType = (string) ($request->cutoff_type ?: $defaultCutoffType);

        [$startDate, $endDate, $cutoffLabel] = $this->resolveCutoffRange($cutoffYear, $cutoffMonth, $cutoffType);

        $selectedEmployeeBiometricId = (int) $request->employee_biometric_id;

        $selectedEmployee = null;
        $cutoffRows = collect();
        $recentLogs = collect();

        if ($selectedEmployeeBiometricId > 0) {
            $employee = EmployeeBiometric::query()->find($selectedEmployeeBiometricId);
            $selectedEmployee = $employee ? $this->employeePayload($employee) : null;

            if ($selectedEmployee) {
                $logsByDate = $this->logsQuery($selectedEmployee, $startDate, $endDate)
                    ->orderBy('check_time')
                    ->get()
                    // Manual punches carry their grid date so an overnight Time
                    // Out stays on the row it was encoded on.
                    ->groupBy(fn (MirasolBiometricsLog $log): string => (string) (data_get($log->raw, 'work_date')
                        ?: Carbon::parse($log->check_time)->format('Y-m-d')));

                foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
                    $dayLogs = collect($logsByDate->get($date->format('Y-m-d'), []));
                    $manualLogs = $dayLogs->where('device_sn', self::DEVICE_SN);
                    $deviceLogs = $dayLogs->where('device_sn', '!=', self::DEVICE_SN);

                    $checkInLog = $manualLogs->first(
                        fn (MirasolBiometricsLog $log): bool => strtolower((string) $log->state) === 'check in'
                    );
                    $checkOutLog = $manualLogs->first(
                        fn (MirasolBiometricsLog $log): bool => strtolower((string) $log->state) === 'check out'
                    );

                    $cutoffRows->push([
                        'work_date' => $date->format('Y-m-d'),
                        'day_name' => $date->format('D'),
                        'time_in' => $checkInLog ? Carbon::parse($checkInLog->check_time)->format('H:i') : null,
                        'time_out' => $checkOutLog ? Carbon::parse($checkOutLog->check_time)->format('H:i') : null,
                        'remarks' => data_get($checkInLog?->raw, 'remarks')
                            ?: data_get($checkOutLog?->raw, 'remarks'),
                        'has_manual_log' => $manualLogs->isNotEmpty(),
                        'device_punches' => $deviceLogs
                            ->map(fn (MirasolBiometricsLog $log): string => Carbon::parse($log->check_time)->format('h:i A'))
                            ->values()
                            ->all(),
                    ]);
                }

                $recentLogs = $this->logsQuery($selectedEmployee, $startDate, $endDate)
                    ->where('device_sn', self::DEVICE_SN)
                    ->orderBy('check_time')
                    ->get();
            }
        }

        return Inertia::render('payroll/manual-biometrics/index', [
            'filters' => [
                'cutoff_month' => $cutoffMonth,
                'cutoff_year' => $cutoffYear,
                'cutoff_type' => $cutoffType,
            ],
            'cutoffLabel' => $cutoffLabel,
            'selectedEmployee' => $selectedEmployee ? [
                ...$selectedEmployee,
                'label' => $selectedEmployee['employee_display_name'].' | '.($selectedEmployee['employee_no'] ?? '-'),
            ] : null,
            'cutoffRows' => $cutoffRows->values(),
            'recentLogs' => $recentLogs->map(fn (MirasolBiometricsLog $log): array => [
                'id' => $log->id,
                'check_time' => $log->check_time ? Carbon::parse($log->check_time)->format('M d, Y h:i A') : null,
                'state' => $log->state,
                'device_name' => $log->device_name,
                'remarks' => data_get($log->raw, 'remarks') ?: '-',
            ])->values(),
            'can' => ['create' => (bool) $request->user()?->can('manual-biometrics.create')],
            'urls' => [
                'index' => route('manual-biometrics.index'),
                'search' => route('manual-biometrics.search-employees'),
                'store' => route('manual-biometrics.store'),
            ],
        ]);
    }

    public function searchEmployees(Request $request)
    {
        $search = trim((string) $request->input('q'));

        $employees = EmployeeBiometric::query()
            ->payrollActive()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('display_name', 'like', "%{$search}%")
                        ->orWhere('source_employee_name', 'like', "%{$search}%")
                        ->orWhere('display_employee_no', 'like', "%{$search}%")
                        ->orWhere('source_employee_no', 'like', "%{$search}%")
                        ->orWhere('source_employee_id', 'like', "%{$search}%");
                });
            })
            ->payrollDirectoryOrder()
            ->limit(20)
            ->get()
            ->map(function (EmployeeBiometric $employee): array {
                $payload = $this->employeePayload($employee);

                return [
                    'employee_biometric_id' => $employee->id,
                    'employee_no' => $payload['employee_no'],
                    'employee_name' => $payload['employee_name'],
                    'employee_display_name' => $payload['employee_display_name'],
                    'label' => trim($payload['employee_display_name'].' | '.($payload['employee_no'] ?? '-')),
                ];
            })
            ->values();

        return response()->json($employees);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cutoff_month' => ['required', 'integer', 'min:1', 'max:12'],
            'cutoff_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'cutoff_type' => ['required', 'in:first,second'],
            'employee_biometric_id' => ['required', 'integer', 'exists:employee_biometrics,id'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.work_date' => ['required', 'date'],
            'rows.*.time_in' => ['nullable', 'date_format:H:i'],
            'rows.*.time_out' => ['nullable', 'date_format:H:i'],
            'rows.*.remarks' => ['nullable', 'string', 'max:500'],
        ]);

        [$startDate, $endDate] = $this->resolveCutoffRange(
            (int) $validated['cutoff_year'],
            (int) $validated['cutoff_month'],
            (string) $validated['cutoff_type']
        );

        $employee = EmployeeBiometric::query()->findOrFail((int) $validated['employee_biometric_id']);
        $identity = $this->employeePayload($employee);

        $redirectParams = [
            'cutoff_month' => $validated['cutoff_month'],
            'cutoff_year' => $validated['cutoff_year'],
            'cutoff_type' => $validated['cutoff_type'],
            'employee_biometric_id' => $employee->id,
        ];

        if (blank($identity['employee_no'])) {
            return back()->withInput()->withErrors([
                'error' => 'This employee has no employee number, so manual logs cannot be matched to attendance. Set the employee number in the biometrics directory first.',
            ]);
        }

        $created = 0;
        $removed = 0;
        $changedDates = [];

        try {
            DB::transaction(function () use ($validated, $startDate, $endDate, $identity, &$created, &$removed, &$changedDates): void {
                foreach ($validated['rows'] as $row) {
                    $workDate = Carbon::parse($row['work_date'], 'Asia/Manila')->startOfDay();

                    if ($workDate->lt($startDate->copy()->startOfDay()) || $workDate->gt($endDate->copy()->endOfDay())) {
                        continue;
                    }

                    $timeIn = $row['time_in'] ?? null;
                    $timeOut = $row['time_out'] ?? null;
                    $remarks = $row['remarks'] ?? null;

                    $punches = array_filter([
                        'Check In' => $timeIn ? $workDate->copy()->setTimeFromTimeString($timeIn) : null,
                        // An earlier Time Out than Time In is an overnight shift.
                        'Check Out' => $timeOut
                            ? tap($workDate->copy()->setTimeFromTimeString($timeOut), function (Carbon $out) use ($timeIn, $workDate): void {
                                if ($timeIn && $out->lessThanOrEqualTo($workDate->copy()->setTimeFromTimeString($timeIn))) {
                                    $out->addDay();
                                }
                            })
                            : null,
                    ]);

                    // The grid is the source of truth for manual punches on a
                    // date: replace them so an edited or cleared time never
                    // leaves a stale punch that attendance would still read.
                    $existing = $this->manualLogsForDate($identity, $workDate);
                    $unchanged = $existing->count() === count($punches)
                        && $existing->every(fn (MirasolBiometricsLog $log): bool => isset($punches[$log->state])
                            && Carbon::parse($log->check_time)->equalTo($punches[$log->state])
                            && (string) data_get($log->raw, 'remarks') === (string) $remarks);

                    if ($unchanged) {
                        continue;
                    }

                    $removed += $existing->count();
                    MirasolBiometricsLog::query()->whereKey($existing->modelKeys())->delete();

                    foreach ($punches as $state => $checkTime) {
                        MirasolBiometricsLog::create($this->manualLogAttributes(
                            $identity,
                            $checkTime,
                            $state,
                            $remarks,
                            $validated,
                            $workDate
                        ));
                        $created++;
                    }

                    $changedDates[] = $workDate->toDateString();
                }
            });
        } catch (\Throwable $e) {
            Log::error('Manual biometrics save failed.', [
                'employee_biometric_id' => $employee->id,
                'exception' => $e,
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'error' => 'Failed to save manual cutoff biometrics logs. '.$e->getMessage(),
                ]);
        }

        $message = "Manual biometrics saved. Punches written: {$created}, replaced/removed: {$removed}, dates changed: ".count($changedDates).'.';

        try {
            $this->rebuildAttendance($employee, $changedDates);
        } catch (\Throwable $e) {
            Log::warning('Manual biometrics saved but attendance rebuild failed.', [
                'employee_biometric_id' => $employee->id,
                'exception' => $e,
            ]);

            $message .= ' Logs were saved, but the attendance summary could not be rebuilt automatically; rebuild it from Attendance Summary.';
        }

        return redirect()->route('manual-biometrics.index', $redirectParams)->with('success', $message);
    }

    /**
     * @return array{employee_biometric_id: int, employee_no: ?string, employee_name: string, employee_display_name: string, source_employee_id: ?string, crosschex_account: string, crosschex_account_name: ?string}
     */
    private function employeePayload(EmployeeBiometric $employee): array
    {
        $snapshot = $this->identityService->snapshot($employee);

        // Device logs carry the CrossChex employee number, which is what the
        // attendance summary matches on; prefer it over a display override.
        $employeeNo = $this->identityService->clean($employee->source_employee_no)
            ?? $this->identityService->clean($snapshot['employee_no']);

        return [
            'employee_biometric_id' => (int) $employee->id,
            'employee_no' => $employeeNo,
            'employee_name' => (string) $snapshot['employee_name'],
            'employee_display_name' => PayrollEmployeeNameFormatter::display($snapshot['employee_name']),
            'source_employee_id' => $this->identityService->clean($employee->source_employee_id),
            'crosschex_account' => $this->identityService->clean($employee->source_crosschex_account) ?? 'main',
            'crosschex_account_name' => $this->identityService->clean($employee->source_crosschex_account_name),
        ];
    }

    private function logsQuery(array $identity, Carbon $startDate, Carbon $endDate)
    {
        return MirasolBiometricsLog::query()
            ->where('employee_no', $identity['employee_no'])
            ->where('crosschex_account', $identity['crosschex_account'])
            ->whereBetween('check_time', [
                $startDate->copy()->startOfDay(),
                // Overnight manual Time Out lands on the next calendar day.
                $endDate->copy()->addDay()->endOfDay(),
            ]);
    }

    private function manualLogsForDate(array $identity, Carbon $workDate)
    {
        return MirasolBiometricsLog::query()
            ->where('employee_no', $identity['employee_no'])
            ->where('crosschex_account', $identity['crosschex_account'])
            ->where('device_sn', self::DEVICE_SN)
            ->where(function ($query) use ($workDate): void {
                $query->whereDate('check_time', $workDate->toDateString())
                    // Overnight Check Out saved on the following day.
                    ->orWhere(function ($overnight) use ($workDate): void {
                        $overnight->whereDate('check_time', $workDate->copy()->addDay()->toDateString())
                            ->where('state', 'Check Out')
                            ->where('raw->work_date', $workDate->toDateString());
                    });
            })
            ->get();
    }

    private function manualLogAttributes(
        array $identity,
        Carbon $checkTime,
        string $state,
        ?string $remarks,
        array $validated,
        Carbon $workDate
    ): array {
        $checkTimeString = $checkTime->format('Y-m-d H:i:s');
        $sourceEmployeeId = $identity['source_employee_id'];

        return [
            'crosschex_account' => $identity['crosschex_account'],
            'crosschex_account_name' => $identity['crosschex_account_name'],
            // crosschex_id is unique per account and holds CrossChex's
            // per-punch id, so manual punches need their own unique value.
            'crosschex_id' => 'MANUAL-'.sha1(implode('|', [
                $identity['crosschex_account'],
                $identity['employee_no'],
                $checkTimeString,
                $state,
            ])),
            'source_employee_id' => $sourceEmployeeId,
            'employee_id' => $sourceEmployeeId !== null && ctype_digit($sourceEmployeeId) ? $sourceEmployeeId : null,
            'employee_no' => $identity['employee_no'],
            'employee_name' => $identity['employee_name'],
            'check_time' => $checkTimeString,
            'device_sn' => self::DEVICE_SN,
            'device_name' => self::DEVICE_NAME,
            'state' => $state,
            'raw' => [
                'source' => 'manual_wfh_cutoff_encoding',
                'type' => $state === 'Check In' ? 'time_in' : 'time_out',
                'work_date' => $workDate->toDateString(),
                'remarks' => $remarks,
                'encoded_by' => auth()->id(),
                'encoded_at' => now('Asia/Manila')->toDateTimeString(),
                'cutoff_month' => (int) $validated['cutoff_month'],
                'cutoff_year' => (int) $validated['cutoff_year'],
                'cutoff_type' => (string) $validated['cutoff_type'],
                'employee_biometric_id' => $identity['employee_biometric_id'],
            ],
        ];
    }

    private function rebuildAttendance(EmployeeBiometric $employee, array $dates): void
    {
        if ($dates === []) {
            return;
        }

        $snapshot = $this->identityService->snapshot($employee);
        $person = [
            'employee_biometric_id' => $employee->id,
            'crosschex_id' => $snapshot['crosschex_id'],
            'biometric_employee_id' => $snapshot['biometric_employee_id'],
            'employee_no' => $snapshot['employee_no'],
            'employee_name' => $snapshot['employee_name'],
            'source_employee_id' => $this->identityService->clean($employee->source_employee_id),
            'source_employee_no' => $this->identityService->clean($employee->source_employee_no),
            'source_crosschex_id' => $this->identityService->clean($employee->source_crosschex_id),
            'source_key' => $this->identityService->clean($employee->source_key),
            'source_crosschex_account' => $this->identityService->clean($employee->source_crosschex_account),
        ];

        foreach (array_unique($dates) as $date) {
            $this->dailyAttendanceSummaryService->buildForPersonDate($person, $date);
        }
    }

    private function getDefaultCutoff(): array
    {
        $today = now('Asia/Manila');

        $day = (int) $today->day;
        $month = (int) $today->month;
        $year = (int) $today->year;

        if ($day >= 11 && $day <= 25) {
            return [$month, $year, 'first'];
        }

        if ($day >= 26) {
            $nextCycleMonth = $today->copy()->addMonthNoOverflow();

            return [(int) $nextCycleMonth->month, (int) $nextCycleMonth->year, 'second'];
        }

        return [$month, $year, 'second'];
    }

    private function resolveCutoffRange(int $year, int $month, string $type): array
    {
        $baseMonth = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila');

        if ($type === 'first') {
            $startDate = $baseMonth->copy()->day(11)->startOfDay();
            $endDate = $baseMonth->copy()->day(25)->endOfDay();
            $label = $startDate->format('F d, Y').' - '.$endDate->format('F d, Y').' | '.config('payroll.cutoff_display.first.full', '2nd Cutoff (11-25)');
        } else {
            $startDate = $baseMonth->copy()->subMonthNoOverflow()->day(26)->startOfDay();
            $endDate = $baseMonth->copy()->day(10)->endOfDay();
            $label = $startDate->format('F d, Y').' - '.$endDate->format('F d, Y').' | '.config('payroll.cutoff_display.second.full', '1st Cutoff (26-10)');
        }

        return [$startDate, $endDate, $label];
    }
}
