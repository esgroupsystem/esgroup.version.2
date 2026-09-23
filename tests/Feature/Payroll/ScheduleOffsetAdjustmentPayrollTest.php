<?php

declare(strict_types=1);

namespace Tests\Feature\Payroll;

use App\Enums\WorkdayType;
use App\Models\DailyAttendanceSummary;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Models\MirasolBiometricsLog;
use App\Models\PayrollAttendanceAdjustment;
use App\Models\PayrollEmployeeSalary;
use App\Models\PayrollItem;
use App\Models\User;
use App\Services\Payroll\DailyAttendanceSummaryService;
use App\Services\Payroll\PayrollComputationService;
use App\Services\Payroll\PayrollPayslipService;
use Carbon\CarbonPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * End-to-end checks for the Sept 2026 payroll changes:
 * straight 8-hour schedule, multi-date Offset, Salary Adjustment (+/-),
 * and Manual Biometrics encoding.
 *
 * Cutoff under test: business 1st cutoff (legacy key "second") of Oct 2026,
 * i.e. Sat Sep 26 - Sat Oct 10, 2026. Weekly day off: Sunday.
 */
final class ScheduleOffsetAdjustmentPayrollTest extends TestCase
{
    use RefreshDatabase;

    private const DAILY_RATE = 800.00; // 100.00 per paid hour

    private User $user;

    private EmployeeBiometric $employee;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('payroll.attendance.paid_minutes_per_day', 480);
        config()->set('payroll.attendance.unpaid_break_minutes', 60);
        config()->set('payroll.attendance.unpaid_break_start', '12:00');
        config()->set('payroll.attendance.unpaid_break_end', '13:00');
        config()->set('payroll.attendance.late_grace_minutes', 15);

        $this->user = $this->makeUser([
            'payroll-attendance-adjustments.view',
            'payroll-attendance-adjustments.create',
            'payroll-attendance-adjustments.update',
            'payroll.finalize',
            'payroll-plotting.view',
            'payroll-plotting.update',
            'manual-biometrics.view',
            'manual-biometrics.create',
        ]);

        $this->employee = EmployeeBiometric::query()->create([
            'source_key' => 'main:4713002',
            'source_crosschex_account' => 'main',
            'source_employee_no' => '4713002',
            'source_employee_name' => 'Divina Bartolo',
            'display_name' => 'Divina Bartolo',
            'employment_status' => 'active',
            'is_payroll_active' => true,
            'group_name' => 1,
        ]);
    }

    public function test_straight_eight_hours_pays_a_full_day_without_lunch_deduction(): void
    {
        $this->setSchedule(WorkdayType::StraightEightHours, '08:00', '16:00');
        $this->punch('2026-09-28 08:00:00');
        $this->punch('2026-09-28 16:00:00');

        $summary = $this->buildDay('2026-09-28');

        $this->assertSame(0, WorkdayType::StraightEightHours->lunchMinutes());
        $this->assertSame(480, WorkdayType::StraightEightHours->clockMinutes());
        $this->assertEquals(8.0, (float) $summary->payable_hours);
        $this->assertSame(0, (int) $summary->late_minutes);
        $this->assertSame(0, (int) $summary->undertime_minutes);
        $this->assertSame(0, (int) data_get($summary->meta, 'unpaid_break_minutes'));
    }

    public function test_same_punches_on_eight_plus_lunch_schedule_are_one_hour_short(): void
    {
        // Contrast: 08:00-16:00 on a regular 8 + 1 hr lunch schedule loses the lunch hour.
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');
        $this->punch('2026-09-28 08:00:00');
        $this->punch('2026-09-28 16:00:00');

        $summary = $this->buildDay('2026-09-28');

        $this->assertEquals(7.0, (float) $summary->payable_hours);
        $this->assertSame(60, (int) $summary->undertime_minutes);
    }

    public function test_plotting_save_accepts_straight_eight_span_and_rejects_nine_hours(): void
    {
        $row = [
            'employee_biometric_id' => $this->employee->id,
            'status' => 'scheduled',
            'shift_name' => 'Regular Shift',
            'workday_type' => 'straight_eight',
            'time_in' => '08:00',
            'time_out' => '16:00',
            'grace_minutes' => 5,
            'day_offs' => ['Sunday'],
        ];

        $this->asPayrollUser()
            ->post(route('payroll-plotting.save'), ['schedule' => [$row]])
            ->assertSessionHasNoErrors();

        $schedule = EmployeePlottingSchedule::query()->where('employee_biometric_id', $this->employee->id)->firstOrFail();
        $this->assertSame(WorkdayType::StraightEightHours, $schedule->workday_type);
        $this->assertSame(0, $schedule->lunchBreakMinutes());
        $this->assertSame(480, $schedule->paidWorkMinutes());

        $row['time_out'] = '17:00';

        $this->asPayrollUser()
            ->post(route('payroll-plotting.save'), ['schedule' => [$row]])
            ->assertSessionHasErrors('schedule.0.time_out');
    }

    public function test_multi_date_offset_pools_weekday_excess_to_pay_an_absence(): void
    {
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');

        // Mon Sep 28 - Fri Oct 2: one extra hour each day (08:00-18:00).
        foreach (['2026-09-28', '2026-09-29', '2026-09-30', '2026-10-01', '2026-10-02'] as $date) {
            $this->punch("{$date} 08:00:00");
            $this->punch("{$date} 18:00:00");
            $this->assertSame(60, (int) $this->buildDay($date)->overtime_minutes);
        }

        // Mon Oct 5: absent. Before the Offset it pays nothing.
        $this->assertEquals(0.0, (float) $this->buildDay('2026-10-05')->payable_hours);

        $sources = collect(['2026-09-28', '2026-09-29', '2026-09-30', '2026-10-01', '2026-10-02'])
            ->map(fn (string $date): array => ['date' => $date, 'hours' => '1'])
            ->all();

        $this->asPayrollUser()
            ->getJson(route('payroll-attendance-adjustments.offset-proof', $this->offsetIdentity() + [
                'work_date' => '2026-10-05',
                'offset_sources' => $sources,
            ]))
            ->assertOk()
            ->assertJsonPath('found', true)
            ->assertJsonCount(5, 'sources')
            ->assertJsonPath('proof.requested_minutes', 300);

        $this->asPayrollUser()
            ->post(route('payroll-attendance-adjustments.store'), $this->offsetIdentity() + [
                'adjustment_type' => PayrollAttendanceAdjustment::TYPE_OFFSET,
                'work_date' => '2026-10-05',
                'offset_sources' => $sources,
                'reason' => 'Pooled weekday excess',
            ])
            ->assertSessionHasNoErrors();

        $offset = PayrollAttendanceAdjustment::query()->where('adjustment_type', 'offset')->sole();
        $this->assertSame(PayrollAttendanceAdjustment::STATUS_PENDING, $offset->status);
        $this->assertSame(300, $offset->approved_minutes);
        $this->assertSame('2026-09-28', $offset->offset_source_date->toDateString());
        $this->assertCount(5, $offset->resolvedOffsetSources());

        $this->asPayrollUser()
            ->post(route('payroll-attendance-adjustments.approve', $offset))
            ->assertSessionHasNoErrors();

        $target = $this->buildDay('2026-10-05');
        $this->assertEquals(5.0, (float) $target->payable_hours, 'Five pooled hours must be paid on the absent day.');
        $this->assertSame(300, (int) data_get($target->meta, 'offset_applied_minutes'));
        $this->assertSame(180, (int) $target->undertime_minutes, 'The uncovered 3 hours stay deductible.');

        // The same source hours cannot be spent twice.
        $this->asPayrollUser()
            ->post(route('payroll-attendance-adjustments.store'), $this->offsetIdentity() + [
                'adjustment_type' => PayrollAttendanceAdjustment::TYPE_OFFSET,
                'work_date' => '2026-10-06',
                'offset_sources' => [['date' => '2026-09-28', 'hours' => '1']],
                'reason' => 'Reuse attempt',
            ])
            ->assertSessionHasErrors('offset_sources');

        // Asking for more than a source date's excess is refused.
        $this->asPayrollUser()
            ->getJson(route('payroll-attendance-adjustments.offset-proof', $this->offsetIdentity() + [
                'work_date' => '2026-10-06',
                'adjustment_id' => $offset->id,
                'offset_sources' => [['date' => '2026-09-28', 'hours' => '2']],
            ]))
            ->assertStatus(422)
            ->assertJsonPath('found', false);
    }

    public function test_single_date_offset_still_works_with_legacy_fields(): void
    {
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');
        $this->punch('2026-09-28 08:00:00');
        $this->punch('2026-09-28 19:00:00');
        $this->buildDay('2026-09-28');

        $this->asPayrollUser()
            ->post(route('payroll-attendance-adjustments.store'), $this->offsetIdentity() + [
                'adjustment_type' => PayrollAttendanceAdjustment::TYPE_OFFSET,
                'work_date' => '2026-10-05',
                'offset_source_date' => '2026-09-28',
                'offset_hours' => '2',
                'reason' => 'Legacy single source',
            ])
            ->assertSessionHasNoErrors();

        $offset = PayrollAttendanceAdjustment::query()->where('adjustment_type', 'offset')->sole();
        $this->assertSame(120, $offset->approved_minutes);
        $this->assertSame([['date' => '2026-09-28', 'minutes' => 120]], collect($offset->resolvedOffsetSources())
            ->map(fn (array $s): array => ['date' => $s['date'], 'minutes' => $s['minutes']])
            ->all());
    }

    public function test_salary_adjustment_addition_and_deduction_flow_through_payroll(): void
    {
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');
        $this->createSalary();
        $this->workWholeCutoff();

        $this->asPayrollUser()
            ->post(route('payroll-attendance-adjustments.store'), $this->offsetIdentity() + [
                'adjustment_type' => PayrollAttendanceAdjustment::TYPE_CASH_ADJUSTMENT,
                'work_date' => '2026-10-01',
                'amount' => '500',
                'reason' => 'Bonus',
            ])
            ->assertSessionHasNoErrors();

        $this->asPayrollUser()
            ->post(route('payroll-attendance-adjustments.store'), $this->offsetIdentity() + [
                'adjustment_type' => PayrollAttendanceAdjustment::TYPE_CASH_ADJUSTMENT,
                'work_date' => '2026-10-02',
                'amount' => '-1000',
                'reason' => 'Damaged equipment',
            ])
            ->assertSessionHasNoErrors();

        // Zero is neither an addition nor a deduction.
        $this->asPayrollUser()
            ->post(route('payroll-attendance-adjustments.store'), $this->offsetIdentity() + [
                'adjustment_type' => PayrollAttendanceAdjustment::TYPE_CASH_ADJUSTMENT,
                'work_date' => '2026-10-03',
                'amount' => '0.00',
                'reason' => 'Nothing',
            ])
            ->assertSessionHasErrors('amount');

        $this->assertSame('Salary Adjustment', PayrollAttendanceAdjustment::TYPES['cash_adjustment']);
        $this->assertSame(
            '-₱1,000.00 salary deduction',
            PayrollAttendanceAdjustment::query()->where('amount', -1000)->sole()->adjusted_time_label
        );

        $item = $this->generatePayrollItem();

        $this->assertEquals(500.0, (float) data_get($item->meta, 'manual_adjustments.additions'));
        $this->assertEquals(1000.0, (float) data_get($item->meta, 'manual_adjustments.deductions'));

        // 13 scheduled working days (Sep 26 - Oct 10 minus 2 Sundays), all full days.
        $expectedRegular = 13 * self::DAILY_RATE;
        $this->assertEqualsWithDelta($expectedRegular + 500, (float) $item->gross_pay, 0.01, 'Addition belongs to gross pay.');
        $this->assertEqualsWithDelta(1000.0, (float) $item->other_deductions, 0.01, 'Deduction is taken after gross.');
        $this->assertEqualsWithDelta(
            (float) $item->gross_pay - 1000.0 - (float) $item->total_employee_government_deductions,
            (float) $item->net_pay,
            0.01
        );

        $payslip = app(PayrollPayslipService::class)->build($item->payroll()->firstOrFail());
        $deductionLines = collect($payslip['slipPages']->flatten(1)->first()['deductions'] ?? []);
        $this->assertEqualsWithDelta(1000.0, (float) $deductionLines->firstWhere('label', 'Salary Adjustment')['amount'], 0.01);
    }

    public function test_offset_covered_absence_is_paid_in_generated_payroll(): void
    {
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');
        $this->createSalary();
        $this->workWholeCutoff(
            overtimeDates: ['2026-09-28', '2026-09-29', '2026-09-30', '2026-10-01', '2026-10-02', '2026-10-03', '2026-10-06', '2026-10-07'],
            absentDates: ['2026-10-05']
        );

        $sources = collect(['2026-09-28', '2026-09-29', '2026-09-30', '2026-10-01', '2026-10-02', '2026-10-03'])
            ->map(fn (string $date): array => ['date' => $date, 'hours' => '1'])
            ->all();

        // Six source days before the absence x 1 hr = 6 hrs of the 8 needed.
        $this->assertCount(6, $sources);

        $this->asPayrollUser()
            ->post(route('payroll-attendance-adjustments.store'), $this->offsetIdentity() + [
                'adjustment_type' => PayrollAttendanceAdjustment::TYPE_OFFSET,
                'work_date' => '2026-10-05',
                'offset_sources' => $sources,
                'reason' => 'Pooled excess',
            ])
            ->assertSessionHasNoErrors();

        $offset = PayrollAttendanceAdjustment::query()->where('adjustment_type', 'offset')->sole();
        $this->asPayrollUser()->post(route('payroll-attendance-adjustments.approve', $offset))->assertSessionHasNoErrors();

        $item = $this->generatePayrollItem();
        $target = DailyAttendanceSummary::query()
            ->where('employee_biometric_id', $this->employee->id)
            ->whereDate('work_date', '2026-10-05')
            ->sole();
        $this->assertEquals(6.0, (float) $target->payable_hours);

        // Base pay is payable hours x hourly rate: 12 full days + 6 Offset
        // hours on the absence. Offset is paid as attendance, not separate cash;
        // the uncovered 2 hours are simply not paid.
        $expected = (12 * 8 + 6) * (self::DAILY_RATE / 8);
        $this->assertEqualsWithDelta($expected, (float) $item->regular_pay, 0.01);
        $this->assertEqualsWithDelta($expected, (float) $item->gross_pay, 0.01);
        $this->assertEquals(0.0, (float) data_get($item->meta, 'manual_adjustments.additions'));
    }

    public function test_paid_leave_overtime_and_salary_adjustments_combine_in_one_payroll(): void
    {
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');
        $this->createSalary();
        // Absent Oct 6 (covered by paid sick leave); stays until 19:00 on Oct 7.
        $this->workWholeCutoff(absentDates: ['2026-10-06']);
        $this->punch('2026-10-07 19:00:00');
        app(DailyAttendanceSummaryService::class)->buildForDate('2026-10-07');

        $this->asPayrollUser()
            ->post(route('payroll-attendance-adjustments.store'), $this->offsetIdentity() + [
                'adjustment_type' => PayrollAttendanceAdjustment::TYPE_SICK_LEAVE,
                'date_from' => '2026-10-06',
                'date_to' => '2026-10-06',
                'is_paid' => '1',
                'reason' => 'Medical certificate',
            ])
            ->assertSessionHasNoErrors();

        $this->asPayrollUser()
            ->post(route('payroll-attendance-adjustments.store'), $this->offsetIdentity() + [
                'adjustment_type' => PayrollAttendanceAdjustment::TYPE_OVERTIME,
                'work_date' => '2026-10-07',
                'adjusted_time_in' => '17:00',
                'adjusted_time_out' => '19:00',
                'reason' => 'Month-end inventory',
            ])
            ->assertSessionHasNoErrors();

        $overtime = PayrollAttendanceAdjustment::query()->where('adjustment_type', 'overtime')->sole();
        $this->asPayrollUser()->post(route('payroll-attendance-adjustments.approve', $overtime))->assertSessionHasNoErrors();

        foreach (['500' => '2026-10-01', '-1000' => '2026-10-02'] as $amount => $date) {
            $this->asPayrollUser()
                ->post(route('payroll-attendance-adjustments.store'), $this->offsetIdentity() + [
                    'adjustment_type' => PayrollAttendanceAdjustment::TYPE_CASH_ADJUSTMENT,
                    'work_date' => $date,
                    'amount' => (string) $amount,
                    'reason' => 'Salary adjustment',
                ])
                ->assertSessionHasNoErrors();
        }

        $item = $this->generatePayrollItem();
        $hourly = self::DAILY_RATE / 8;

        // 13 paid days (12 worked + 1 paid sick leave), 2 approved OT hours at
        // 125%, +500 addition; the -1000 is deducted after gross.
        $this->assertEqualsWithDelta(2 * $hourly * 1.25, (float) $item->overtime_pay, 0.01);
        $this->assertEqualsWithDelta(
            13 * self::DAILY_RATE + 2 * $hourly * 1.25 + 500,
            (float) $item->gross_pay,
            0.01
        );
        $this->assertEqualsWithDelta(1000.0, (float) $item->other_deductions, 0.01);
        $this->assertEqualsWithDelta(
            (float) $item->gross_pay - 1000.0 - (float) $item->total_employee_government_deductions,
            (float) $item->net_pay,
            0.01
        );
    }

    public function test_manual_biometrics_saves_replaces_and_feeds_attendance(): void
    {
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');

        $payload = [
            'cutoff_month' => 10,
            'cutoff_year' => 2026,
            'cutoff_type' => 'second',
            'employee_biometric_id' => $this->employee->id,
            'rows' => [
                ['work_date' => '2026-09-28', 'time_in' => '08:00', 'time_out' => '17:00', 'remarks' => 'WFH'],
                ['work_date' => '2026-09-29', 'time_in' => '08:00', 'time_out' => '17:00', 'remarks' => 'WFH'],
                ['work_date' => '2026-09-30', 'time_in' => null, 'time_out' => null, 'remarks' => null],
            ],
        ];

        $this->asPayrollUser()
            ->post(route('manual-biometrics.store'), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $logs = MirasolBiometricsLog::query()->where('device_sn', 'WFH-MANUAL')->orderBy('check_time')->get();
        $this->assertCount(4, $logs);
        $this->assertSame(4, $logs->pluck('crosschex_id')->unique()->count(), 'Each punch needs its own crosschex_id.');
        $this->assertTrue($logs->every(fn (MirasolBiometricsLog $log): bool => $log->employee_no === '4713002'));

        $summary = DailyAttendanceSummary::query()
            ->where('employee_biometric_id', $this->employee->id)
            ->whereDate('work_date', '2026-09-28')
            ->sole();
        $this->assertEquals(8.0, (float) $summary->payable_hours, 'Attendance is rebuilt from the manual punches.');

        // Editing a time replaces the old punch instead of leaving it behind,
        // and clearing a row removes its manual punches.
        $payload['rows'][0]['time_in'] = '09:00';
        $payload['rows'][1]['time_in'] = null;
        $payload['rows'][1]['time_out'] = null;

        $this->asPayrollUser()
            ->post(route('manual-biometrics.store'), $payload)
            ->assertSessionHasNoErrors();

        $logs = MirasolBiometricsLog::query()->where('device_sn', 'WFH-MANUAL')->orderBy('check_time')->get();
        $this->assertSame(
            ['2026-09-28 09:00:00', '2026-09-28 17:00:00'],
            $logs->map(fn (MirasolBiometricsLog $log): string => $log->check_time->format('Y-m-d H:i:s'))->all()
        );

        $summary->refresh();
        $this->assertSame(60, (int) $summary->late_minutes, 'Past the grace threshold, lateness rounds up to 30-minute blocks.');

        $this->asPayrollUser()
            ->getJson(route('manual-biometrics.search-employees', ['q' => 'Bartolo']))
            ->assertOk()
            ->assertJsonPath('0.employee_biometric_id', $this->employee->id);

        $this->asPayrollUser()
            ->get(route('manual-biometrics.index', [
                'cutoff_month' => 10,
                'cutoff_year' => 2026,
                'cutoff_type' => 'second',
                'employee_biometric_id' => $this->employee->id,
            ]))
            ->assertOk()
            ->assertSee('09:00');
    }

    private function setSchedule(WorkdayType $type, string $timeIn, string $timeOut): void
    {
        EmployeePlottingSchedule::query()->where('employee_biometric_id', $this->employee->id)->delete();

        EmployeePlottingSchedule::query()->create([
            'employee_biometric_id' => $this->employee->id,
            'biometric_employee_id' => '4713002',
            'employee_no' => '4713002',
            'employee_name' => 'Divina Bartolo',
            'work_date' => null,
            'shift_name' => 'Regular Shift',
            'workday_type' => $type->value,
            'paid_work_minutes' => $type->paidMinutes(),
            'lunch_break_minutes' => $type->lunchMinutes(),
            'time_in' => $timeIn,
            'time_out' => $timeOut,
            'grace_minutes' => 15,
            'status' => 'scheduled',
            'day_offs' => ['Sunday'],
            'day_off' => 'Sunday',
        ]);
    }

    private function createSalary(): void
    {
        PayrollEmployeeSalary::query()->create([
            'employee_biometric_id' => $this->employee->id,
            'biometric_employee_id' => '4713002',
            'employee_no' => '4713002',
            'employee_name' => 'Divina Bartolo',
            'rate_type' => 'daily',
            'basic_salary' => self::DAILY_RATE,
            'is_active' => true,
        ]);
    }

    /**
     * Regular 08:00-17:00 punches on every working day of the cutoff, with
     * 08:00-18:00 on $overtimeDates and no punches on $absentDates.
     */
    private function workWholeCutoff(array $overtimeDates = [], array $absentDates = []): void
    {
        foreach (CarbonPeriod::create('2026-09-26', '2026-10-10') as $date) {
            $day = $date->toDateString();

            if ($date->isSunday() || in_array($day, $absentDates, true)) {
                continue;
            }

            $this->punch("{$day} 08:00:00");
            $this->punch($day.(in_array($day, $overtimeDates, true) ? ' 18:00:00' : ' 17:00:00'));
        }

        foreach (CarbonPeriod::create('2026-09-26', '2026-10-10') as $date) {
            app(DailyAttendanceSummaryService::class)->buildForDate($date->toDateString());
        }
    }

    private function generatePayrollItem(): PayrollItem
    {
        $payroll = app(PayrollComputationService::class)->generate([
            'cutoff_month' => 10,
            'cutoff_year' => 2026,
            'cutoff_type' => 'second',
            'garage_group' => 1,
        ], $this->user->id);

        return PayrollItem::query()
            ->where('payroll_id', $payroll->id)
            ->where('employee_biometric_id', $this->employee->id)
            ->sole();
    }

    private function punch(string $dateTime): void
    {
        MirasolBiometricsLog::query()->create([
            'crosschex_account' => 'main',
            'crosschex_id' => sha1($dateTime),
            'employee_no' => '4713002',
            'employee_name' => 'Divina Bartolo',
            'check_time' => $dateTime,
            'device_sn' => '0770100024370009',
        ]);
    }

    private function buildDay(string $date): DailyAttendanceSummary
    {
        app(DailyAttendanceSummaryService::class)->buildForDate($date);

        return DailyAttendanceSummary::query()
            ->where('employee_biometric_id', $this->employee->id)
            ->whereDate('work_date', $date)
            ->sole();
    }

    private function offsetIdentity(): array
    {
        return [
            'employee_biometric_id' => $this->employee->id,
            'biometric_employee_id' => '4713002',
            'employee_no' => '4713002',
            'employee_name' => 'Divina Bartolo',
        ];
    }

    private function asPayrollUser(): static
    {
        return $this->actingAs($this->user)
            ->withSession([
                '_token' => 'payroll-test-csrf',
                'unlocked' => true,
                'last_activity_time' => now()->timestamp,
            ])
            ->withHeader('X-CSRF-TOKEN', 'payroll-test-csrf');
    }

    private function makeUser(array $permissions): User
    {
        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $user = User::factory()->create([
            'username' => 'payrolltester',
            'email' => 'payroll@example.com',
            'password' => Hash::make('Password123!Password'),
            'role' => 'Admin',
            'account_status' => 'active',
            'must_change_password' => false,
        ]);

        $role = Role::findOrCreate('Payroll Tester', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
