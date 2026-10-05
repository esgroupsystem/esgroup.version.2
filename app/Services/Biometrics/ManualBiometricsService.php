<?php

declare(strict_types=1);

namespace App\Services\Biometrics;

use App\Models\EmployeeBiometric;
use App\Models\MirasolBiometricsLog;
use App\Repositories\Contracts\Biometrics\BiometricsLogRepositoryInterface;
use App\Repositories\Contracts\Biometrics\EmployeeBiometricRepositoryInterface;
use App\Services\Payroll\DailyAttendanceSummaryService;
use App\Services\Payroll\PayrollPeriodService;
use App\Support\PayrollEmployeeNameFormatter;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Manual WFH encoding: Time In / Time Out per cutoff day written as punches into
 * mirasol_biometrics_logs (device WFH-MANUAL), then the attendance summary is rebuilt.
 *
 * CrossChex stores a per-punch hash in crosschex_id (unique per account), so every manual punch
 * gets its own MANUAL-* id. Attendance matches punches through employee_no, like device logs.
 */
final class ManualBiometricsService
{
    public const DEVICE_SN = 'WFH-MANUAL';

    public const DEVICE_NAME = 'WFH Manual Encoding';

    public function __construct(
        private readonly BiometricsLogRepositoryInterface $logs,
        private readonly EmployeeBiometricRepositoryInterface $employees,
        private readonly EmployeeBiometricIdentityService $identity,
        private readonly DailyAttendanceSummaryService $attendance,
        private readonly PayrollPeriodService $periods,
    ) {}

    /** @return array{0: int, 1: int, 2: string} month, year, `first` | `second` */
    public function defaultCutoff(): array
    {
        return $this->periods->getDefaultCutoff();
    }

    /** @return array{0: CarbonInterface, 1: CarbonInterface, 2: string} start, end and label */
    public function cutoffRange(int $year, int $month, string $type): array
    {
        [$start, $end] = $this->periods->resolveCutoffRange($month, $year, $type === 'first' ? 'first' : 'second');
        $label = $start->format('F d, Y').' - '.$end->format('F d, Y').' | '.($type === 'first'
            ? config('payroll.cutoff_display.first.full', '2nd Cutoff (11-25)')
            : config('payroll.cutoff_display.second.full', '1st Cutoff (26-10)'));

        return [$start, $end, $label];
    }

    /**
     * @return array{employee_biometric_id: int, employee_no: ?string, employee_name: string, employee_display_name: string, source_employee_id: ?string, crosschex_account: string, crosschex_account_name: ?string}|null
     */
    public function employee(int $id): ?array
    {
        $employee = $id > 0 ? $this->employees->find($id) : null;

        return $employee ? $this->identityOf($employee) : null;
    }

    /** @return Collection<int, array<string, mixed>> up to 20 payroll-active people for the picker */
    public function search(string $search): Collection
    {
        return $this->employees->searchPayrollActive(trim($search), 20)->map(function (EmployeeBiometric $employee): array {
            $identity = $this->identityOf($employee);

            return [
                'employee_biometric_id' => $employee->id,
                'employee_no' => $identity['employee_no'],
                'employee_name' => $identity['employee_name'],
                'employee_display_name' => $identity['employee_display_name'],
                'label' => trim($identity['employee_display_name'].' | '.($identity['employee_no'] ?? '-')),
            ];
        })->values();
    }

    /**
     * The grid: one row per cutoff day with the manual Time In / Out, remarks and device punches.
     *
     * @param  array<string, mixed>  $identity
     * @return Collection<int, array<string, mixed>>
     */
    public function grid(array $identity, CarbonInterface $start, CarbonInterface $end): Collection
    {
        $byDate = $this->logsOf($identity, $start, $end)
            // Manual punches carry their grid date so an overnight Time Out stays on its row.
            ->groupBy(fn (MirasolBiometricsLog $log): string => (string) (data_get($log->raw, 'work_date') ?: Carbon::parse($log->check_time)->format('Y-m-d')));

        return collect(CarbonPeriod::create($start, $end))->map(function (CarbonInterface $date) use ($byDate): array {
            $dayLogs = collect($byDate->get($date->format('Y-m-d'), []));
            $manual = $dayLogs->where('device_sn', self::DEVICE_SN);
            $in = $manual->first(fn (MirasolBiometricsLog $log): bool => strtolower((string) $log->state) === 'check in');
            $out = $manual->first(fn (MirasolBiometricsLog $log): bool => strtolower((string) $log->state) === 'check out');

            return [
                'work_date' => $date->format('Y-m-d'),
                'day_name' => $date->format('D'),
                'time_in' => $in ? Carbon::parse($in->check_time)->format('H:i') : null,
                'time_out' => $out ? Carbon::parse($out->check_time)->format('H:i') : null,
                'remarks' => data_get($in?->raw, 'remarks') ?: data_get($out?->raw, 'remarks'),
                'has_manual_log' => $manual->isNotEmpty(),
                'device_punches' => $dayLogs->where('device_sn', '!=', self::DEVICE_SN)
                    ->map(fn (MirasolBiometricsLog $log): string => Carbon::parse($log->check_time)->format('h:i A'))
                    ->values()
                    ->all(),
            ];
        })->values();
    }

    /**
     * @param  array<string, mixed>  $identity
     * @return Collection<int, MirasolBiometricsLog> the manual punches of the cutoff
     */
    public function manualLogs(array $identity, CarbonInterface $start, CarbonInterface $end): Collection
    {
        return $this->logsOf($identity, $start, $end, self::DEVICE_SN);
    }

    /**
     * Replaces the manual punches of every changed day in the cutoff, then rebuilds attendance.
     *
     * @param  array<string, mixed>  $data  validated StoreManualBiometricsRequest
     * @return string the result message
     *
     * @throws ValidationException when the employee has no employee number or the save fails
     */
    public function save(array $data, ?int $userId): string
    {
        [$start, $end] = $this->cutoffRange((int) $data['cutoff_year'], (int) $data['cutoff_month'], (string) $data['cutoff_type']);
        $employee = $this->employees->findOrFail((int) $data['employee_biometric_id']);
        $identity = $this->identityOf($employee);

        if (blank($identity['employee_no'])) {
            throw ValidationException::withMessages(['error' => 'This employee has no employee number, so manual logs cannot be matched to attendance. Set the employee number in the biometrics directory first.']);
        }

        $created = 0;
        $removed = 0;
        $changedDates = [];

        try {
            DB::transaction(function () use ($data, $start, $end, $identity, $userId, &$created, &$removed, &$changedDates): void {
                foreach ($data['rows'] as $row) {
                    $workDate = Carbon::parse($row['work_date'], 'Asia/Manila')->startOfDay();
                    if ($workDate->lt($start->copy()->startOfDay()) || $workDate->gt($end->copy()->endOfDay())) {
                        continue;
                    }

                    $remarks = $row['remarks'] ?? null;
                    $punches = $this->punches($workDate, $row['time_in'] ?? null, $row['time_out'] ?? null);

                    // The grid is the source of truth for a date's manual punches: replace them, so an
                    // edited or cleared time never leaves a stale punch that attendance still reads.
                    $existing = $this->logs->manualForDate($identity['employee_no'], $identity['crosschex_account'], self::DEVICE_SN, $workDate);
                    $unchanged = $existing->count() === count($punches)
                        && $existing->every(fn (MirasolBiometricsLog $log): bool => isset($punches[$log->state])
                            && Carbon::parse($log->check_time)->equalTo($punches[$log->state])
                            && (string) data_get($log->raw, 'remarks') === (string) $remarks);
                    if ($unchanged) {
                        continue;
                    }

                    $removed += $existing->count();
                    $this->logs->deleteMany($existing->modelKeys());

                    foreach ($punches as $state => $checkTime) {
                        $this->logs->create($this->punchAttributes($identity, $checkTime, $state, $remarks, $data, $workDate, $userId));
                        $created++;
                    }
                    $changedDates[] = $workDate->toDateString();
                }
            });
        } catch (Throwable $e) {
            Log::error('Manual biometrics save failed.', ['employee_biometric_id' => $employee->id, 'exception' => $e]);

            throw ValidationException::withMessages(['error' => 'Failed to save manual cutoff biometrics logs. Please check the times and try again.']);
        }

        $message = "Manual biometrics saved. Punches written: {$created}, replaced/removed: {$removed}, dates changed: ".count($changedDates).'.';

        try {
            $this->rebuildAttendance($employee, $changedDates);
        } catch (Throwable $e) {
            Log::warning('Manual biometrics saved but attendance rebuild failed.', ['employee_biometric_id' => $employee->id, 'exception' => $e]);
            $message .= ' Logs were saved, but the attendance summary could not be rebuilt automatically; rebuild it from Attendance Summary.';
        }

        return $message;
    }

    /**
     * Check In / Check Out times of a row; a Time Out not after the Time In is the next morning.
     *
     * @return array<string, Carbon>
     */
    private function punches(Carbon $workDate, ?string $timeIn, ?string $timeOut): array
    {
        $in = $timeIn ? $workDate->copy()->setTimeFromTimeString($timeIn) : null;
        $out = $timeOut ? $workDate->copy()->setTimeFromTimeString($timeOut) : null;
        if ($in && $out && $out->lessThanOrEqualTo($in)) {
            $out->addDay();
        }

        return array_filter(['Check In' => $in, 'Check Out' => $out]);
    }

    /**
     * @param  array<string, mixed>  $identity
     * @return Collection<int, MirasolBiometricsLog>
     */
    private function logsOf(array $identity, CarbonInterface $start, CarbonInterface $end, ?string $deviceSn = null): Collection
    {
        // Overnight manual Time Out lands on the next calendar day.
        return $this->logs->forIdentity((string) $identity['employee_no'], $identity['crosschex_account'], $start->copy()->startOfDay(), $end->copy()->addDay()->endOfDay(), $deviceSn);
    }

    /** @return array{employee_biometric_id: int, employee_no: ?string, employee_name: string, employee_display_name: string, source_employee_id: ?string, crosschex_account: string, crosschex_account_name: ?string} */
    private function identityOf(EmployeeBiometric $employee): array
    {
        $snapshot = $this->identity->snapshot($employee);

        return [
            'employee_biometric_id' => (int) $employee->id,
            // Device logs carry the CrossChex employee number, which attendance matches on.
            'employee_no' => $this->identity->clean($employee->source_employee_no) ?? $this->identity->clean($snapshot['employee_no']),
            'employee_name' => (string) $snapshot['employee_name'],
            'employee_display_name' => PayrollEmployeeNameFormatter::display($snapshot['employee_name']),
            'source_employee_id' => $this->identity->clean($employee->source_employee_id),
            'crosschex_account' => $this->identity->clean($employee->source_crosschex_account) ?? 'main',
            'crosschex_account_name' => $this->identity->clean($employee->source_crosschex_account_name),
        ];
    }

    /**
     * @param  array<string, mixed>  $identity
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function punchAttributes(array $identity, Carbon $checkTime, string $state, ?string $remarks, array $data, Carbon $workDate, ?int $userId): array
    {
        $time = $checkTime->format('Y-m-d H:i:s');
        $sourceId = $identity['source_employee_id'];

        return [
            'crosschex_account' => $identity['crosschex_account'],
            'crosschex_account_name' => $identity['crosschex_account_name'],
            'crosschex_id' => 'MANUAL-'.sha1(implode('|', [$identity['crosschex_account'], $identity['employee_no'], $time, $state])),
            'source_employee_id' => $sourceId,
            'employee_id' => $sourceId !== null && ctype_digit($sourceId) ? $sourceId : null,
            'employee_no' => $identity['employee_no'],
            'employee_name' => $identity['employee_name'],
            'check_time' => $time,
            'device_sn' => self::DEVICE_SN,
            'device_name' => self::DEVICE_NAME,
            'state' => $state,
            'raw' => [
                'source' => 'manual_wfh_cutoff_encoding',
                'type' => $state === 'Check In' ? 'time_in' : 'time_out',
                'work_date' => $workDate->toDateString(),
                'remarks' => $remarks,
                'encoded_by' => $userId,
                'encoded_at' => now('Asia/Manila')->toDateTimeString(),
                'cutoff_month' => (int) $data['cutoff_month'],
                'cutoff_year' => (int) $data['cutoff_year'],
                'cutoff_type' => (string) $data['cutoff_type'],
                'employee_biometric_id' => $identity['employee_biometric_id'],
            ],
        ];
    }

    /** @param list<string> $dates */
    private function rebuildAttendance(EmployeeBiometric $employee, array $dates): void
    {
        if ($dates === []) {
            return;
        }

        $snapshot = $this->identity->snapshot($employee);
        $person = [
            'employee_biometric_id' => $employee->id,
            'crosschex_id' => $snapshot['crosschex_id'],
            'biometric_employee_id' => $snapshot['biometric_employee_id'],
            'employee_no' => $snapshot['employee_no'],
            'employee_name' => $snapshot['employee_name'],
            'source_employee_id' => $this->identity->clean($employee->source_employee_id),
            'source_employee_no' => $this->identity->clean($employee->source_employee_no),
            'source_crosschex_id' => $this->identity->clean($employee->source_crosschex_id),
            'source_key' => $this->identity->clean($employee->source_key),
            'source_crosschex_account' => $this->identity->clean($employee->source_crosschex_account),
        ];

        foreach (array_unique($dates) as $date) {
            $this->attendance->buildForPersonDate($person, $date);
        }
    }
}
