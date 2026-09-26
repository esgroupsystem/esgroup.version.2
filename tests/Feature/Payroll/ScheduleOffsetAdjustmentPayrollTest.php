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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
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
            'payroll-attendance-adjustments.delete',
            'payroll.finalize',
            'payroll-plotting.view',
            'payroll-plotting.update',
            'manual-biometrics.view',
            'manual-biometrics.create',
            'attendance-summary.view',
            'attendance-summary.create',
            'attendance-summary.export',
            'payroll.view',
            'payroll.create',
            'payroll.export',
            'payroll.delete',
            'payroll.mirasol',
            'employee-salaries.view',
            'employee-salaries.create',
            'employee-salaries.update',
            'employee-salaries.delete',
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

    public function test_plotting_save_allows_several_employees_to_share_a_day_off(): void
    {
        $colleague = EmployeeBiometric::query()->create([
            'source_key' => 'main:4717035',
            'source_crosschex_account' => 'main',
            'source_employee_no' => '4717035',
            'source_employee_name' => 'Generosa Belarma',
            'display_name' => 'Generosa Belarma',
            'employment_status' => 'active',
            'is_payroll_active' => true,
            'group_name' => 1,
        ]);

        $row = fn (int $id): array => [
            'employee_biometric_id' => $id,
            'status' => 'scheduled',
            'shift_name' => 'Regular Shift',
            'workday_type' => 'eight_hours',
            'time_in' => '08:00',
            'time_out' => '17:00',
            'grace_minutes' => 15,
            'day_offs' => ['Saturday', 'Sunday'],
        ];

        $this->asPayrollUser()
            ->post(route('payroll-plotting.save'), ['schedule' => [$row($this->employee->id), $row($colleague->id)]])
            ->assertSessionHasNoErrors();

        $this->assertSame(
            [['Saturday', 'Sunday'], ['Saturday', 'Sunday']],
            EmployeePlottingSchedule::query()->orderBy('employee_biometric_id')->get()
                ->map(fn (EmployeePlottingSchedule $schedule): array => $schedule->resolvedDayOffs())->all()
        );
    }

    public function test_attendance_rebuild_works_with_several_scheduled_employees(): void
    {
        // Lazy loading is strict outside production and Laravel only enforces
        // it when a query returns several models; one schedule never hit it.
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');
        $colleague = EmployeeBiometric::query()->create([
            'source_key' => 'main:4717035',
            'source_crosschex_account' => 'main',
            'source_employee_no' => '4717035',
            'source_employee_name' => 'Generosa Belarma',
            'display_name' => 'Generosa Belarma',
            'employment_status' => 'active',
            'is_payroll_active' => true,
            'group_name' => 1,
        ]);
        EmployeePlottingSchedule::query()->create([
            'employee_biometric_id' => $colleague->id,
            'employee_no' => '4717035',
            'employee_name' => 'Generosa Belarma',
            'shift_name' => 'Regular Shift',
            'workday_type' => 'eight_hours',
            'time_in' => '08:00',
            'time_out' => '17:00',
            'status' => 'scheduled',
            'day_offs' => ['Sunday'],
        ]);
        PayrollAttendanceAdjustment::query()->create([
            'employee_biometric_id' => $colleague->id,
            'employee_name' => 'Generosa Belarma',
            'adjustment_type' => PayrollAttendanceAdjustment::TYPE_SICK_LEAVE,
            'work_date' => '2026-09-28',
            'date_from' => '2026-09-28',
            'date_to' => '2026-09-28',
            'is_paid' => true,
            'status' => PayrollAttendanceAdjustment::STATUS_APPROVED,
            'reason' => 'Fever',
        ]);
        PayrollAttendanceAdjustment::query()->create([
            'employee_biometric_id' => $this->employee->id,
            'employee_name' => 'Divina Bartolo',
            'adjustment_type' => PayrollAttendanceAdjustment::TYPE_SICK_LEAVE,
            'work_date' => '2026-09-28',
            'date_from' => '2026-09-28',
            'date_to' => '2026-09-28',
            'is_paid' => true,
            'status' => PayrollAttendanceAdjustment::STATUS_APPROVED,
            'reason' => 'Fever',
        ]);

        app(DailyAttendanceSummaryService::class)->buildForDate('2026-09-28');

        $this->assertSame(2, DailyAttendanceSummary::query()->whereDate('work_date', '2026-09-28')->count());
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

        // Forms always post their blank Offset source rows; they must be
        // ignored for non-Offset types.
        $this->asPayrollUser()
            ->post(route('payroll-attendance-adjustments.store'), $this->offsetIdentity() + [
                'adjustment_type' => PayrollAttendanceAdjustment::TYPE_CASH_ADJUSTMENT,
                'work_date' => '2026-10-03',
                'amount' => '100',
                'offset_sources' => [['date' => '', 'hours' => '']],
                'reason' => 'Blank offset rows posted by the form',
            ])
            ->assertSessionHasNoErrors();
        PayrollAttendanceAdjustment::query()->where('amount', 100)->sole()->delete();

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

        Storage::fake('local');
        $this->asPayrollUser()
            ->post(route('payroll-attendance-adjustments.store'), $this->offsetIdentity() + [
                'adjustment_type' => PayrollAttendanceAdjustment::TYPE_OVERTIME,
                'work_date' => '2026-10-07',
                'adjusted_time_in' => '17:00',
                'adjusted_time_out' => '19:00',
                'reason' => 'Month-end inventory',
                'ot_form' => UploadedFile::fake()->create('ot-form.pdf', 120, 'application/pdf'),
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

    public function test_adjustment_pages_render_react_list_and_form(): void
    {
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');

        $this->asPayrollUser()
            ->post(route('payroll-attendance-adjustments.store'), $this->offsetIdentity() + [
                'adjustment_type' => PayrollAttendanceAdjustment::TYPE_CASH_ADJUSTMENT,
                'work_date' => '2026-10-01',
                'amount' => '-250',
                'reason' => 'Uniform',
            ])
            ->assertSessionHasNoErrors();

        $adjustment = PayrollAttendanceAdjustment::query()->sole();

        $this->asPayrollUser()
            ->get(route('payroll-attendance-adjustments.index'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('payroll/adjustments/index')
                ->where('adjustments.total', 1)
                ->where('adjustments.data.0.type_label', 'Salary Adjustment')
                ->where('adjustments.data.0.adjusted_time_label', '-₱250.00 salary deduction')
                ->where('adjustments.data.0.employee.employee_no', '4713002')
                ->where('can.approve', true)
            );

        $this->asPayrollUser()
            ->get(route('payroll-attendance-adjustments.create'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('payroll/adjustments/form')
                ->where('adjustment', null)
                ->where('people.0.employee_biometric_id', $this->employee->id)
                ->where('types.cash_adjustment', 'Salary Adjustment')
            );

        $this->asPayrollUser()
            ->get(route('payroll-attendance-adjustments.edit', $adjustment))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('payroll/adjustments/form')
                ->where('adjustment.id', $adjustment->id)
                ->where('adjustment.amount', '-250.00')
                ->where('adjustment.adjustment_type', 'cash_adjustment')
                ->where('urls.submit', route('payroll-attendance-adjustments.update', $adjustment))
            );

        // Editing through PUT (what the React form sends) still works.
        $this->asPayrollUser()
            ->put(route('payroll-attendance-adjustments.update', $adjustment), $this->offsetIdentity() + [
                'adjustment_type' => PayrollAttendanceAdjustment::TYPE_CASH_ADJUSTMENT,
                'work_date' => '2026-10-01',
                'amount' => '-300',
                'reason' => 'Uniform',
            ])
            ->assertSessionHasNoErrors();

        $this->assertEquals(-300.0, (float) $adjustment->fresh()->amount);
    }

    public function test_payroll_pages_render_with_computed_values(): void
    {
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');
        $this->createSalary();
        $this->workWholeCutoff();
        $item = $this->generatePayrollItem();
        $payroll = $item->payroll()->firstOrFail();
        $page = fn () => $this->asPayrollUser()->withSession([
            '_token' => 'payroll-test-csrf', 'unlocked' => true, 'last_activity_time' => now()->timestamp,
            'payroll_allowed_groups' => 'all',
        ]);

        $page()->get(route('payroll.index'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $p) => $p
                ->component('payroll/payrolls/index')
                ->where('payrolls.total', 1)
                ->where('payrolls.data.0.items_count', 1)
                ->where('payrolls.data.0.is_finalized', false));

        $page()->get(route('payroll.create'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $p) => $p
                ->component('payroll/payrolls/create')
                ->has('defaults.cutoff_month')
                ->has('payrollGroups.1'));

        $page()->get(route('payroll.show', $payroll))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $p) => $p
                ->component('payroll/payrolls/show')
                ->where('items.0.employee_no', '4713002')
                ->where('items.0.gross', fn ($gross) => abs($gross - 13 * self::DAILY_RATE) < 0.01)
                ->where('totals.employees', 1));

        $page()->get(route('payroll.items.show', [$payroll, $item]))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $p) => $p
                ->component('payroll/items/show')
                ->where('item.gross_pay', fn ($gross) => abs($gross - 13 * self::DAILY_RATE) < 0.01)
                ->has('auditRows', 15)
                ->where('fileAdjustment.people.0.employee_biometric_id', $this->employee->id)
                ->missing('fileAdjustment.types.typhoon_disaster_3h')
                ->where('can.recompute', true));
    }

    public function test_finalized_payroll_shows_in_benefits_records(): void
    {
        Permission::findOrCreate('benefits-records.view', 'web');
        $this->user->roles->first()->givePermissionTo('benefits-records.view');
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');
        $this->createSalary();
        $this->workWholeCutoff();
        $payroll = $this->generatePayrollItem()->payroll()->firstOrFail();

        $this->asPayrollUser()->post(route('payroll.finalize', $payroll))->assertSessionHasNoErrors();
        $this->assertSame('finalized', $payroll->fresh()->status);

        $filters = ['month' => 10, 'year' => 2026];

        // Contributions post when the closing (11-25) cutoff is finalized, so
        // after only the opening 26-10 cutoff the employee is still pending.
        $this->asPayrollUser()->get(route('benefits-records.index', $filters))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $p) => $p
                ->component('payroll/benefits-records/index')
                ->where('periodLabel', 'October 2026')
                ->where('employees.data.0.name', 'Bartolo, Divina')
                ->where('employees.data.0.posted', false)
                ->has('employees.data.0.programs', 3)
                ->where('kpis.active', 1)
                ->where('kpis.not_posted', 1));

        $this->asPayrollUser()->get(route('benefits-records.overall', $filters))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $p) => $p
                ->component('payroll/benefits-records/overall')
                ->has('rows', 0)
                ->has('totals.sss_total')
                ->where('urls.print', route('benefits-records.print', $filters)));

        $this->asPayrollUser()->get(route('benefits-records.print', $filters))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $p) => $p
                ->component('payroll/benefits-records/print')
                ->where('period', 'October 2026')
                ->where('rows.0.name', 'Bartolo, Divina')
                ->where('rows.0.summary.posted', false)
                ->has('totals.grand_total')
                ->where('urls.back', route('benefits-records.overall', $filters)));
    }

    public function test_employee_salary_pages_render_and_update(): void
    {
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');
        $this->createSalary();
        $salary = PayrollEmployeeSalary::query()->sole();
        $client = fn () => $this->asPayrollUser()->withSession([
            '_token' => 'payroll-test-csrf', 'unlocked' => true, 'last_activity_time' => now()->timestamp,
        ]);

        $client()->get(route('payroll-employee-salaries.index'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $p) => $p
                ->component('payroll/employee-salaries/index')
                ->where('salaries.data.0.employee_no', '4713002')
                ->where('salaries.data.0.basic_salary', fn ($value) => (float) $value === self::DAILY_RATE));

        $client()->get(route('payroll-employee-salaries.create'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $p) => $p
                ->component('payroll/employee-salaries/form')
                ->where('salary', null)
                ->where('people.0.employee_biometric_id', $this->employee->id)
                ->has('sssRules.minimum_msc'));

        $edit = $client()->get(route('payroll-employee-salaries.edit', $salary));
        $edit->assertOk()->assertInertia(fn (\Inertia\Testing\AssertableInertia $p) => $p
            ->component('payroll/employee-salaries/form')
            ->where('values.rate_type', 'daily')
            ->where('workday.paid_hours', 8)
            ->where('urls.submit', route('payroll-employee-salaries.update', $salary)));

        // Submit exactly what the React form sends, with a raise and one extra deduction.
        $values = $edit->viewData('page')['props']['values'];
        $values['basic_salary'] = '900';
        $values['paid_day_off'] = true;
        $values['other_deductions'] = [[
            'name' => 'Uniform', 'total_amount' => '1000', 'payment_amount' => '250',
            'deduction_schedule' => 'every_cutoff', 'start_date' => '2026-10-01', 'remarks' => '',
        ]];

        $client()->put(route('payroll-employee-salaries.update', $salary), $values)->assertSessionHasNoErrors();

        $salary->refresh()->load('otherDeductions');
        $this->assertEquals(900.0, (float) $salary->basic_salary);
        $this->assertEqualsWithDelta(112.5, (float) $salary->ot_rate_per_hour, 0.01, 'OT rate = 900 / 8 paid hours.');
        $this->assertSame(['Uniform'], $salary->otherDeductions->pluck('name')->all());
        $this->assertTrue($salary->paid_day_off);
    }

    public function test_salary_sync_from_biometrics_is_post_only(): void
    {
        $this->asPayrollUser()->get(route('payroll-employee-salaries.sync'))->assertStatus(405);
        $this->assertSame(0, PayrollEmployeeSalary::query()->count());

        $this->asPayrollUser()
            ->post(route('payroll-employee-salaries.sync'))
            ->assertRedirect(route('payroll-employee-salaries.index'));

        $this->assertSame(1, PayrollEmployeeSalary::query()->where('employee_biometric_id', $this->employee->id)->where('paid_day_off', true)->count());
    }

    public function test_attendance_summary_page_renders_and_rebuilds(): void
    {
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');
        $this->punch('2026-09-28 08:00:00');
        $this->punch('2026-09-28 17:00:00');

        $cutoff = ['cutoff_month' => 10, 'cutoff_year' => 2026, 'cutoff_type' => 'second', 'group_name' => '1'];

        $this->asPayrollUser()
            ->post(route('attendance-summary.rebuild'), $cutoff)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->asPayrollUser()
            ->get(route('attendance-summary.index', $cutoff + ['search' => 'Bartolo']))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('payroll/attendance-summary/index')
                ->where('filters.cutoff_type', 'second')
                ->where('summaries.data.0.employee_no', '4713002')
                ->where('summaries.data.0.schedule.kind', 'fixed')
                ->where('summaries.data.0.schedule.time_in', '08:00 AM')
                ->where('summaries.data.0.schedule.has_lunch', true)
                ->has('stats.eligible_employees')
                ->where('can.rebuild', true)
            );

        // The payroll export is a React print page: one card per employee with the cutoff rows.
        $this->asPayrollUser()
            ->get(route('attendance-summary.export-payroll', $cutoff))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('payroll/attendance-summary/export')
                ->where('groupLabel', 'Mirasol / Balintawak Payroll')
                ->where('employees.0.employee_no', '4713002')
                ->where('employees.0.records', fn ($records) => collect($records)->contains(fn (array $record): bool => $record['in'] === '08:00 AM' && $record['out'] === '05:00 PM'))
                ->has('stats.eligible_employees'));
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
            ->assertInertia(fn (Assert $page) => $page
                ->component('payroll/manual-biometrics/index')
                ->where('selectedEmployee.employee_biometric_id', $this->employee->id)
                ->where('cutoffRows', fn ($rows) => collect($rows)->contains(fn ($row) => $row['time_in'] === '09:00' && $row['has_manual_log']))
                ->where('recentLogs', fn ($logs) => collect($logs)->contains('state', 'Check In')));
    }

    public function test_saves_made_inside_a_modal_stay_on_the_page_underneath(): void
    {
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');
        $this->createSalary();
        $this->workWholeCutoff();
        $base = route('payroll.index', ['search' => 'PR']);

        // "Generate payroll" from the modal: the browser stays on the list,
        // and the modal learns the new payroll's URL so it can open it.
        $response = $this->asPayrollUser()
            ->withSession(['payroll_allowed_groups' => 'all', '_token' => 'payroll-test-csrf', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-Modal-Base', $base)
            ->post(route('payroll.store'), ['cutoff_month' => 10, 'cutoff_year' => 2026, 'cutoff_type' => 'second', 'garage_group' => 1, 'rebuild_summary' => false]);

        $payroll = \App\Models\Payroll::query()->sole();
        $response->assertRedirect($base)->assertSessionHas('modal_redirect', route('payroll.show', $payroll));

        // Without the header nothing changes (test headers persist, so clear them first).
        $this->flushHeaders();
        $adjustment = PayrollAttendanceAdjustment::query()->create($this->offsetIdentity() + [
            'adjustment_type' => PayrollAttendanceAdjustment::TYPE_CASH_ADJUSTMENT,
            'work_date' => '2026-10-01',
            'amount' => -100,
            'reason' => 'Test',
            'status' => PayrollAttendanceAdjustment::STATUS_APPROVED,
        ]);
        $this->asPayrollUser()->delete(route('payroll-attendance-adjustments.destroy', $adjustment))
            ->assertRedirect(route('payroll-attendance-adjustments.index'))
            ->assertSessionMissing('modal_redirect');

        // A base on another host is ignored (no open redirect).
        $this->flushHeaders();
        $this->asPayrollUser()
            ->withSession(['payroll_allowed_groups' => 'all', '_token' => 'payroll-test-csrf', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-Modal-Base', 'https://evil.example/steal')
            ->post(route('payroll.finalize', $payroll))
            ->assertRedirect()
            ->assertSessionMissing('modal_redirect');
    }

    public function test_adjustment_list_filters_by_status_and_group(): void
    {
        foreach ([PayrollAttendanceAdjustment::STATUS_PENDING, PayrollAttendanceAdjustment::STATUS_APPROVED] as $status) {
            PayrollAttendanceAdjustment::query()->create($this->offsetIdentity() + [
                'adjustment_type' => PayrollAttendanceAdjustment::TYPE_OVERTIME,
                'work_date' => '2026-10-02',
                'reason' => "OT {$status}",
                'status' => $status,
            ]);
        }

        $this->asPayrollUser()
            ->get(route('payroll-attendance-adjustments.index', ['status' => 'pending', 'group_name' => (string) $this->employee->group_name]))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->where('adjustments.total', 1)
                ->where('adjustments.data.0.status', 'pending')
                ->where('filters.status', 'pending')
                ->where('filters.group_name', (string) $this->employee->group_name)
                ->has('groups.1'));

        $this->asPayrollUser()
            ->get(route('payroll-attendance-adjustments.index', ['group_name' => '999']))
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page->where('adjustments.total', 0));
    }

    public function test_payroll_list_filters_by_group(): void
    {
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');
        $this->createSalary();
        $this->workWholeCutoff();
        $this->generatePayrollItem();
        $page = fn () => $this->asPayrollUser()->withSession([
            '_token' => 'payroll-test-csrf', 'unlocked' => true, 'last_activity_time' => now()->timestamp, 'payroll_allowed_groups' => 'all',
        ]);

        $page()->get(route('payroll.index', ['garage_group' => '1']))
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $p) => $p->where('payrolls.total', 1)->where('filters.garage_group', '1')->has('payrollGroups.2'));
        $page()->get(route('payroll.index', ['garage_group' => '2']))
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $p) => $p->where('payrolls.total', 0));
    }

    public function test_overtime_needs_the_approved_ot_form_and_biometric_proof(): void
    {
        Storage::fake('local');
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');
        $this->punch('2026-10-07 08:00:00');
        $this->punch('2026-10-07 19:00:00');
        $overtime = fn (array $extra = []) => $extra + $this->offsetIdentity() + [
            'adjustment_type' => PayrollAttendanceAdjustment::TYPE_OVERTIME,
            'work_date' => '2026-10-07',
            'adjusted_time_in' => '17:00',
            'adjusted_time_out' => '19:00',
            'reason' => 'Month-end inventory',
        ];

        // No OT form.
        $this->asPayrollUser()->post(route('payroll-attendance-adjustments.store'), $overtime())
            ->assertSessionHasErrors('ot_form');

        // Wrong file type.
        $this->asPayrollUser()->post(route('payroll-attendance-adjustments.store'), $overtime(['ot_form' => UploadedFile::fake()->create('ot.exe', 10)]))
            ->assertSessionHasErrors('ot_form');

        // OT past the last biometric punch (19:00) is refused.
        $this->asPayrollUser()->post(route('payroll-attendance-adjustments.store'), $overtime([
            'adjusted_time_out' => '21:00',
            'ot_form' => UploadedFile::fake()->image('ot.jpg'),
        ]))->assertSessionHasErrors('adjusted_time_out');

        // No logs at all that day.
        $this->asPayrollUser()->post(route('payroll-attendance-adjustments.store'), $overtime([
            'work_date' => '2026-10-08',
            'ot_form' => UploadedFile::fake()->image('ot.jpg'),
        ]))->assertSessionHasErrors('adjusted_time_out');
        $this->assertSame(0, PayrollAttendanceAdjustment::query()->count());

        // Covered by logs + form attached: saved, file kept privately.
        $this->asPayrollUser()->post(route('payroll-attendance-adjustments.store'), $overtime(['ot_form' => UploadedFile::fake()->image('signed-ot.jpg')]))
            ->assertSessionHasNoErrors();
        $saved = PayrollAttendanceAdjustment::query()->sole();
        $this->assertSame('signed-ot.jpg', $saved->attachment_name);
        Storage::disk('local')->assertExists($saved->attachment_path);

        // The same hours cannot be filed twice.
        $this->asPayrollUser()->post(route('payroll-attendance-adjustments.store'), $overtime([
            'adjusted_time_in' => '18:00',
            'ot_form' => UploadedFile::fake()->image('ot.jpg'),
        ]))->assertSessionHasErrors('adjusted_time_out');

        // Editing keeps the saved form (no new upload needed) and ignores its own hours.
        $this->asPayrollUser()->put(route('payroll-attendance-adjustments.update', $saved), $overtime(['adjusted_time_out' => '18:30']))
            ->assertSessionHasNoErrors();
        $this->assertSame('18:30', substr((string) $saved->fresh()->adjusted_time_out, 0, 5));

        // Replacing the form via POST + _method=put deletes the old file.
        $oldPath = $saved->attachment_path;
        $this->asPayrollUser()->post(route('payroll-attendance-adjustments.update', $saved), $overtime([
            '_method' => 'put',
            'ot_form' => UploadedFile::fake()->create('new-ot.pdf', 50, 'application/pdf'),
        ]))->assertSessionHasNoErrors();
        $saved->refresh();
        $this->assertSame('new-ot.pdf', $saved->attachment_name);
        Storage::disk('local')->assertMissing($oldPath);

        // The form opens for users who can view adjustments, and not for others.
        $this->asPayrollUser()->get(route('payroll-attendance-adjustments.attachment', $saved))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->actingAs(User::factory()->create(['account_status' => 'active', 'must_change_password' => false]))
            ->withSession(['unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->get(route('payroll-attendance-adjustments.attachment', $saved))
            ->assertForbidden();

        // Live checker returns the same verdict as the save.
        $this->asPayrollUser()
            ->getJson(route('payroll-attendance-adjustments.overtime-check', $this->offsetIdentity() + [
                'work_date' => '2026-10-07',
                'adjusted_time_in' => '17:00',
                'adjusted_time_out' => '21:00',
            ]))
            ->assertOk()
            ->assertJsonPath('ok', false)
            ->assertJsonPath('minutes', 240)
            ->assertJsonPath('punch_in', '08:00 AM');
    }

    public function test_adjustment_list_shows_who_approved_and_status_counts(): void
    {
        foreach ([PayrollAttendanceAdjustment::STATUS_PENDING, PayrollAttendanceAdjustment::STATUS_PENDING, PayrollAttendanceAdjustment::STATUS_REJECTED] as $status) {
            PayrollAttendanceAdjustment::query()->create($this->offsetIdentity() + [
                'adjustment_type' => PayrollAttendanceAdjustment::TYPE_OVERTIME,
                'work_date' => '2026-10-02',
                'adjusted_time_in' => '17:00',
                'adjusted_time_out' => '18:00',
                'reason' => "OT {$status}",
                'status' => $status,
            ]);
        }

        $pending = PayrollAttendanceAdjustment::query()->where('status', 'pending')->first();
        $this->asPayrollUser()->post(route('payroll-attendance-adjustments.approve', $pending))->assertSessionHasNoErrors();
        $approver = $this->user->full_name ?: $this->user->name;

        $this->asPayrollUser()
            ->get(route('payroll-attendance-adjustments.index', ['status' => 'approved']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('adjustments.total', 1)
                ->where('adjustments.data.0.decision.by', $approver)
                ->where('stats.status_pending', 1)
                ->where('stats.status_approved', 1)
                ->where('stats.status_rejected', 1));
    }

    public function test_hr_employee_links_to_biometric_record_both_ways(): void
    {
        $hrUser = $this->makeHrUser();
        $hr = \App\Models\Employee::query()->create(['employee_id' => 'HR-1', 'full_name' => 'Divina Bartolo', 'status' => 'Active']);
        $other = \App\Models\Employee::query()->create(['employee_id' => 'HR-2', 'full_name' => 'Someone Else', 'status' => 'Active']);
        $session = ['unlocked' => true, 'last_activity_time' => now()->timestamp];

        // Profile suggests the name match first.
        $this->actingAs($hrUser)->withSession($session)
            ->get(route('employees.staff.show', $hr))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('biometric', null)
                ->where('biometricOptions.0.value', (string) $this->employee->id));

        $this->actingAs($hrUser)->withSession($session)
            ->put(route('employees.biometric-link.update', $hr), ['employee_biometric_id' => $this->employee->id])
            ->assertSessionHasNoErrors();
        $this->assertSame($this->employee->id, $hr->fresh()->employee_biometric_id);

        // One biometric record belongs to one employee only.
        $this->actingAs($hrUser)->withSession($session)
            ->put(route('employees.biometric-link.update', $other), ['employee_biometric_id' => $this->employee->id])
            ->assertSessionHasErrors('employee_biometric_id');

        $this->actingAs($hrUser)->withSession($session)
            ->get(route('employees.staff.show', $hr))
            ->assertInertia(fn (Assert $page) => $page->where('biometric.id', $this->employee->id));

        // Unlink from the profile.
        $this->actingAs($hrUser)->withSession($session)
            ->put(route('employees.biometric-link.update', $hr), ['employee_biometric_id' => null])
            ->assertSessionHasNoErrors();
        $this->assertNull($hr->fresh()->employee_biometric_id);

        // Link from the Biometric Employees edit form, and see it in the list.
        $this->actingAs($hrUser)->withSession($session)
            ->put(route('biometrics.employees.update', $this->employee), [
                'employment_status' => 'active',
                'group_name' => (string) $this->employee->group_name,
                'is_payroll_active' => '1',
                'display_name' => 'Divina Bartolo',
                'hr_employee_id' => (string) $other->id,
            ])
            ->assertSessionHasNoErrors();
        $this->assertSame($this->employee->id, $other->fresh()->employee_biometric_id);

        $this->actingAs($hrUser)->withSession($session)
            ->get(route('biometrics.employees.index'))
            ->assertInertia(fn (Assert $page) => $page->where('employees.data.0.hr_employee.name', 'Someone Else'));

        $this->actingAs($hrUser)->withSession($session)
            ->get(route('biometrics.employees.edit', $this->employee))
            ->assertInertia(fn (Assert $page) => $page->where('values.hr_employee_id', (string) $other->id));

        // Users without employees.update cannot link.
        $this->asPayrollUser()
            ->put(route('employees.biometric-link.update', $hr), ['employee_biometric_id' => $this->employee->id])
            ->assertForbidden();
    }

    private function makeHrUser(): User
    {
        $permissions = ['employees.view', 'employees.update', 'biometrics.view', 'biometrics.edit', 'biometrics.update'];
        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }
        $user = User::factory()->create(['role' => 'Admin', 'account_status' => 'active', 'must_change_password' => false]);
        $role = Role::findOrCreate('HR Link Tester', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }

    public function test_paid_day_off_pays_unworked_sundays_for_daily_employees(): void
    {
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');
        $this->createSalary(paidDayOff: true);
        $this->workWholeCutoff();

        $item = $this->generatePayrollItem();

        // Sep 26 - Oct 10: 13 worked days + 2 unworked Sundays paid 1 day each.
        $this->assertEqualsWithDelta(15 * self::DAILY_RATE, (float) $item->regular_pay, 0.01);
        $this->assertSame(2, data_get($item->meta, 'day_off.days'));
        $this->assertEqualsWithDelta(2 * self::DAILY_RATE, (float) data_get($item->meta, 'day_off.amount'), 0.01);
    }

    public function test_not_paid_day_off_keeps_daily_pay_to_days_worked(): void
    {
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');
        $this->createSalary(paidDayOff: false);
        $this->workWholeCutoff();

        $item = $this->generatePayrollItem();

        $this->assertEqualsWithDelta(13 * self::DAILY_RATE, (float) $item->regular_pay, 0.01);
        $this->assertSame(0, data_get($item->meta, 'day_off.days'));
    }

    public function test_paid_day_off_needs_minimum_valid_log_days(): void
    {
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');
        $this->createSalary(paidDayOff: true);
        // Only 2 worked days in the cutoff: below the 3-day minimum.
        $this->workRange('2026-09-28', '2026-09-29');
        foreach (CarbonPeriod::create('2026-09-26', '2026-10-10') as $date) {
            app(DailyAttendanceSummaryService::class)->buildForDate($date->toDateString());
        }

        $item = $this->generatePayrollItem();

        $this->assertEqualsWithDelta(2 * self::DAILY_RATE, (float) $item->regular_pay, 0.01);
        $this->assertSame(0, data_get($item->meta, 'day_off.days'));
    }

    public function test_monthly_employee_with_unpaid_day_off_is_paid_per_day_worked_including_the_31st(): void
    {
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');
        $this->createSalary('monthly', 30000, paidDayOff: false);
        // Oct 26 - Nov 10, 2026: 14 working days incl. Saturday Oct 31.
        $this->workRange('2026-10-26', '2026-11-10');

        $item = $this->generateItemFor(11);
        $daily = 30000 * 12 / 365;

        $this->assertEqualsWithDelta(round(14 * $daily, 2), (float) $item->regular_pay, 0.05);
        $this->assertFalse((bool) data_get($item->meta, 'attendance_deductions_are_deducted_from_monthly_base'));
    }

    public function test_monthly_employee_with_paid_day_off_keeps_half_month_salary(): void
    {
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');
        $this->createSalary('monthly', 30000, paidDayOff: true);
        $this->workRange('2026-10-26', '2026-11-10');

        $item = $this->generateItemFor(11);

        $this->assertEqualsWithDelta(15000, (float) $item->regular_pay, 0.01);
    }

    public function test_employee_rate_form_saves_day_off_setting(): void
    {
        $this->asPayrollUser()
            ->get(route('payroll-employee-salaries.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('values.paid_day_off', true));

        $this->createSalary(paidDayOff: true);
        $salary = PayrollEmployeeSalary::query()->sole();

        $this->asPayrollUser()
            ->get(route('payroll-employee-salaries.index'))
            ->assertInertia(fn (Assert $page) => $page->where('salaries.data.0.paid_day_off', true));

        $this->asPayrollUser()
            ->get(route('payroll-employee-salaries.edit', $salary))
            ->assertInertia(fn (Assert $page) => $page->where('values.paid_day_off', true));
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

    /** Existing daily employees are "Day off: Not paid" (see the paid_day_off migration). */
    private function createSalary(string $rateType = 'daily', float $basic = self::DAILY_RATE, bool $paidDayOff = false): void
    {
        PayrollEmployeeSalary::query()->create([
            'employee_biometric_id' => $this->employee->id,
            'biometric_employee_id' => '4713002',
            'employee_no' => '4713002',
            'employee_name' => 'Divina Bartolo',
            'rate_type' => $rateType,
            'basic_salary' => $basic,
            'paid_day_off' => $paidDayOff,
            'is_active' => true,
        ]);
    }

    /** 08:00-17:00 punches on every non-Sunday from $from to $to, then build the summaries. */
    private function workRange(string $from, string $to): void
    {
        foreach (CarbonPeriod::create($from, $to) as $date) {
            if (! $date->isSunday()) {
                $this->punch($date->toDateString().' 08:00:00');
                $this->punch($date->toDateString().' 17:00:00');
            }
        }

        foreach (CarbonPeriod::create($from, $to) as $date) {
            app(DailyAttendanceSummaryService::class)->buildForDate($date->toDateString());
        }
    }

    private function generateItemFor(int $month): PayrollItem
    {
        $payroll = app(PayrollComputationService::class)->generate([
            'cutoff_month' => $month,
            'cutoff_year' => 2026,
            'cutoff_type' => 'second',
            'garage_group' => 1,
        ], $this->user->id);

        return PayrollItem::query()->where('payroll_id', $payroll->id)->where('employee_biometric_id', $this->employee->id)->sole();
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
