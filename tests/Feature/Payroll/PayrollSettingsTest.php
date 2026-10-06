<?php

declare(strict_types=1);

namespace Tests\Feature\Payroll;

use App\Enums\WorkdayType;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Models\MirasolBiometricsLog;
use App\Models\Payroll;
use App\Models\PayrollAuditLog;
use App\Models\PayrollEmployeeSalary;
use App\Models\PayrollItem;
use App\Models\PayrollRule;
use App\Models\PayrollSettingVersion;
use App\Models\User;
use App\Repositories\Contracts\Payroll\PayrollRuleRepositoryInterface;
use App\Repositories\Contracts\Payroll\PayrollSettingVersionRepositoryInterface;
use App\Repositories\Payroll\PayrollRuleRepository;
use App\Repositories\Payroll\PayrollSettingVersionRepository;
use App\Services\Payroll\DailyAttendanceSummaryService;
use App\Services\Payroll\GovernmentDeductionService;
use App\Services\Payroll\PayrollComputationService;
use App\Services\Payroll\PayrollPayslipService;
use App\Services\Payroll\PayrollSettingsService;
use App\Support\Payroll\PayrollSettingCatalog;
use Carbon\CarbonPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Payroll Settings: versioned rates, custom rules and the test computation.
 *
 * Cutoff under test: business 1st cutoff (legacy key "second") of Oct 2026,
 * Sat Sep 26 - Sat Oct 10, 2026. Daily rate 800, Sunday off, 08:00-17:00 every other day.
 */
final class PayrollSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private EmployeeBiometric $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->makeUser(['payroll-settings.view', 'payroll-settings.manage', 'payroll.mirasol', 'payroll.view', 'payroll.create']);

        $this->employee = EmployeeBiometric::query()->create([
            'source_key' => 'main:5510001',
            'source_crosschex_account' => 'main',
            'source_employee_no' => '5510001',
            'source_employee_name' => 'Rosa Manalo',
            'display_name' => 'Rosa Manalo',
            'employment_status' => 'active',
            'is_payroll_active' => true,
            'group_name' => 1,
        ]);
    }

    public function test_repositories_are_bound_and_the_starting_version_matches_the_config_files(): void
    {
        $this->assertInstanceOf(PayrollSettingVersionRepository::class, app(PayrollSettingVersionRepositoryInterface::class));
        $this->assertInstanceOf(PayrollRuleRepository::class, app(PayrollRuleRepositoryInterface::class));

        $version = PayrollSettingVersion::query()->sole();
        $this->assertSame('2000-01-01', $version->effective_from->toDateString());
        $this->assertSame([], PayrollSettingCatalog::changes(PayrollSettingCatalog::defaults(), (array) $version->values));

        // Every config key written by the settings has the same value as the shipped config files.
        $files = ['payroll' => require config_path('payroll.php'), 'sss' => require config_path('sss.php')];
        foreach (PayrollSettingCatalog::toConfig(PayrollSettingCatalog::defaults()) as $path => $value) {
            [$file, $key] = explode('.', $path, 2);
            $shipped = data_get($files[$file], $key);
            $this->assertNotNull($shipped, "{$path} is missing from config/{$file}.php");
            $this->assertEquals($shipped, $value, "{$path} differs from config/{$file}.php");
        }
    }

    public function test_pages_render(): void
    {
        $version = PayrollSettingVersion::query()->sole();
        $rule = $this->rule(['name' => 'Rice allowance', 'code' => 'rice']);

        $this->signedIn($this->user)->get(route('payroll-settings.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('payroll/settings/index')
            ->has('versions', 1)
            ->where('versions.0.is_current', true)
            ->where('current.id', $version->id)
            ->has('sections', 7)
            ->where('can.manage', true));

        $this->signedIn($this->user)->get(route('payroll-settings.versions.create', ['from' => $version->id]))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('payroll/settings/form')
            ->where('version', null)
            ->where('values.values.overtime_regular', 1.25)
            ->has('test.employees', 1)
            ->has('test.groups'));

        $this->signedIn($this->user)->get(route('payroll-settings.versions.edit', $version))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('payroll/settings/form')
            ->where('version.id', $version->id)
            ->where('values.effective_from', '2000-01-01'));

        $this->signedIn($this->user)->get(route('payroll-settings.rules.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('payroll/settings/rules')
            ->has('rules', 1)
            ->where('rules.0.description', '₱500.00 per cutoff'));

        $this->signedIn($this->user)->get(route('payroll-settings.rules.create'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('payroll/settings/rule-form')
            ->where('rule', null)
            ->has('options.variables'));

        $this->signedIn($this->user)->get(route('payroll-settings.rules.edit', $rule))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('payroll/settings/rule-form')
            ->where('values.code', 'rice')
            ->where('values.amount', '500'));

        $this->signedIn($this->user)->get(route('payroll-settings.test.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('payroll/settings/test')
            ->where('current.label', 'Starting rules'));
    }

    public function test_view_only_users_cannot_change_and_others_cannot_open(): void
    {
        $viewer = $this->makeUser(['payroll-settings.view', 'payroll.mirasol'], 'viewer');
        $this->signedIn($viewer)->get(route('payroll-settings.index'))->assertOk()->assertInertia(fn (Assert $page) => $page->where('can.manage', false));
        $this->signedIn($viewer)->get(route('payroll-settings.versions.create'))->assertForbidden();
        $this->signedIn($viewer)->post(route('payroll-settings.rules.store'), [])->assertForbidden();

        $outsider = $this->makeUser(['payroll.view'], 'outsider');
        $this->signedIn($outsider)->get(route('payroll-settings.index'))->assertForbidden();
        $this->signedIn($outsider)->postJson(route('payroll-settings.test.run'), [])->assertForbidden();
    }

    public function test_a_version_can_be_created_updated_and_deleted(): void
    {
        $values = PayrollSettingCatalog::defaults();
        $values['philhealth_rate'] = 0.06;

        $this->signedIn($this->user)->post(route('payroll-settings.versions.store'), [
            'effective_from' => '2027-01-01',
            'label' => 'PhilHealth 2027',
            'values' => $values,
        ])->assertRedirect(route('payroll-settings.index'))->assertSessionHas('success');

        $version = PayrollSettingVersion::query()->where('label', 'PhilHealth 2027')->sole();
        $this->assertEqualsWithDelta(0.06, $version->values['philhealth_rate'], 0.0000001);
        $this->assertTrue(PayrollAuditLog::query()->where('module', 'settings')->where('action', 'created')->exists());

        $settings = app(PayrollSettingsService::class);
        $this->assertSame('Starting rules', $settings->versionFor('2026-12-31')['label']);
        $this->assertSame('PhilHealth 2027', $settings->versionFor('2027-01-01')['label']);

        // Same date twice is refused.
        $this->signedIn($this->user)->post(route('payroll-settings.versions.store'), [
            'effective_from' => '2027-01-01',
            'label' => 'Duplicate',
            'values' => $values,
        ])->assertSessionHasErrors('effective_from');

        // Cross-field checks.
        $bad = $values;
        $bad['philhealth_floor'] = 200000;
        $this->signedIn($this->user)->put(route('payroll-settings.versions.update', $version), [
            'effective_from' => '2027-01-01',
            'label' => 'PhilHealth 2027',
            'values' => $bad,
        ])->assertSessionHasErrors('values.philhealth_ceiling');

        $this->signedIn($this->user)->put(route('payroll-settings.versions.update', $version), [
            'effective_from' => '2027-02-01',
            'label' => 'PhilHealth Feb 2027',
            'values' => $values,
        ])->assertRedirect(route('payroll-settings.index'));
        $this->assertSame('2027-02-01', $version->fresh()->effective_from->toDateString());

        $this->signedIn($this->user)->delete(route('payroll-settings.versions.destroy', $version))->assertRedirect(route('payroll-settings.index'));
        $this->assertModelMissing($version);

        // The last version cannot be deleted.
        $this->signedIn($this->user)
            ->delete(route('payroll-settings.versions.destroy', PayrollSettingVersion::query()->sole()))
            ->assertSessionHasErrors('version');
    }

    public function test_settings_apply_by_payroll_period_and_put_the_config_back(): void
    {
        $settings = app(PayrollSettingsService::class);
        $this->version('2026-09-01', ['overtime_regular' => 1.5]);

        // One field writes every config key that holds the same rate.
        $seen = $settings->using('2026-09-26', fn (): array => [
            (float) config('payroll.premiums.overtime_multiplier'),
            (float) config('payroll.overtime.regular_multiplier'),
        ]);
        $this->assertSame([1.5, 1.5], $seen);
        $this->assertSame(1.25, (float) config('payroll.premiums.overtime_multiplier'));

        $before = $settings->using('2026-08-31', fn (): float => (float) config('payroll.premiums.overtime_multiplier'));
        $this->assertSame(1.25, $before);

        $draft = $settings->usingValues(['night_differential_percent' => 0.2], 'Draft', fn (): float => (float) config('payroll.premiums.night_differential_percent'));
        $this->assertSame(0.2, $draft);
        $this->assertNotSame(0.2, (float) config('payroll.premiums.night_differential_percent'));
    }

    public function test_government_rates_come_from_settings(): void
    {
        $government = app(GovernmentDeductionService::class);
        $base = $government->compute(['monthly_basic' => 20000, 'sss_monthly_basic' => 20000, 'philhealth_monthly_basic' => 20000, 'pagibig_monthly_basic' => 20000]);
        $this->assertSame(500.0, $base['philhealth_employee']);
        $this->assertSame(200.0, $base['pagibig_employee']);
        $this->assertSame(1000.0, $base['sss_employee']);

        $changed = app(PayrollSettingsService::class)->usingValues(
            ['philhealth_rate' => 0.06, 'pagibig_max_fund_salary' => 5000, 'sss_employee_rate' => 0.06],
            'Draft',
            fn (): array => $government->compute(['monthly_basic' => 20000, 'sss_monthly_basic' => 20000, 'philhealth_monthly_basic' => 20000, 'pagibig_monthly_basic' => 20000])
        );
        $this->assertSame(600.0, $changed['philhealth_employee']);
        $this->assertSame(100.0, $changed['pagibig_employee']);
        $this->assertSame(1200.0, $changed['sss_employee']);
    }

    public function test_generated_payroll_uses_the_version_of_its_period(): void
    {
        $this->prepareCutoff();

        // Starts after the period (Sep 26), so it must not be used.
        $this->version('2026-10-11', ['philhealth_rate' => 0.06]);
        $item = $this->generate();
        // Daily 800 → monthly 800 × 365 ÷ 12 = 24,333.33 × 5% = 1,216.67 ÷ 2 (employee share).
        $this->assertSame(608.33, (float) $item->philhealth_employee);
        $this->assertSame('Starting rules', data_get($item->payroll->meta, 'settings.label'));

        Payroll::query()->delete();
        PayrollSettingVersion::query()->where('effective_from', '2026-10-11')->update(['effective_from' => '2026-09-01']);
        app(PayrollSettingsService::class)->forget();

        // 24,333.33 × 6% = 1,460 ÷ 2 (employee share) = 730.
        $item = $this->generate();
        $this->assertSame(730.0, (float) $item->philhealth_employee);
        $this->assertEqualsWithDelta(0.06, data_get($item->payroll->meta, 'settings.values.philhealth_rate'), 0.0000001);
        $this->assertSame(data_get($item->meta, 'settings_version.version_id'), data_get($item->payroll->meta, 'settings.version_id'));

        // Config is back to today's values after the run.
        $this->assertSame(0.05, (float) config('payroll.government.philhealth.premium_rate'));
    }

    public function test_custom_rules_add_to_gross_and_deduct_from_net(): void
    {
        $this->prepareCutoff();
        $plain = $this->generate();
        Payroll::query()->delete();

        $this->rule(['name' => 'Rice allowance', 'code' => 'rice', 'amount' => 500]);
        $this->rule(['name' => 'Perfect attendance', 'code' => 'perfect', 'method' => 'formula', 'amount' => null, 'formula' => 'IF(days_absent = 0, 1000, 0)', 'sort_order' => 2]);
        $this->rule(['name' => 'Canteen', 'code' => 'canteen', 'kind' => PayrollRule::KIND_DEDUCTION, 'method' => 'percent', 'amount' => 10, 'base' => 'basic_pay']);
        $this->rule(['name' => 'Co-op share', 'code' => 'coop', 'kind' => PayrollRule::KIND_DEDUCTION, 'method' => 'formula', 'amount' => null, 'formula' => 'rice * 10%', 'sort_order' => 5]);
        $this->rule(['name' => 'Other group only', 'code' => 'gonzales_only', 'amount' => 999, 'payroll_groups' => ['2']]);
        $this->rule(['name' => 'Switched off', 'code' => 'off_rule', 'amount' => 777, 'is_active' => false]);
        $this->rule(['name' => '2nd cutoff only', 'code' => 'second_half', 'amount' => 333, 'cutoff' => 'first']);

        $item = $this->generate();
        $earnings = collect(data_get($item->meta, 'custom_rules.earnings'))->keyBy('code');
        $deductions = collect(data_get($item->meta, 'custom_rules.deductions'))->keyBy('code');

        $this->assertSame(['rice', 'perfect'], $earnings->keys()->all());
        $this->assertSame(500.0, (float) $earnings['rice']['amount']);
        $this->assertSame(1000.0, (float) $earnings['perfect']['amount']);
        $this->assertEqualsWithDelta((float) $plain->gross_pay + 1500, (float) $item->gross_pay, 0.001);
        $this->assertEqualsWithDelta((float) $plain->other_additions + 1500, (float) $item->other_additions, 0.001);

        $this->assertSame(['canteen', 'coop'], $deductions->keys()->all());
        $this->assertEqualsWithDelta(round((float) $item->regular_pay * 0.10, 2), (float) $deductions['canteen']['amount'], 0.001);
        $this->assertSame(50.0, (float) $deductions['coop']['amount']);
        $this->assertEqualsWithDelta(
            (float) $item->gross_pay - (float) $item->other_deductions - (float) $item->total_employee_government_deductions,
            (float) $item->net_pay,
            0.001
        );

        // Item page lists each rule by name.
        $this->signedIn($this->user)->get(route('payroll.items.show', [$item->payroll_id, $item->id]))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('customRules.earnings', 2)
            ->where('customRules.earnings.0.name', 'Rice allowance')
            ->where('customRules.deductions.1.amount', 50)
            ->where('settingsVersion', 'Starting rules'));

        // Payslip shows them as their own lines and still adds up to gross / net.
        $slip = collect(app(PayrollPayslipService::class)->build($item->payroll)['slipPages']->flatten(1))->first();
        $earnings = collect($slip['earnings'])->keyBy('label');
        $deductions = collect($slip['deductions'])->keyBy('label');
        $this->assertSame(500.0, (float) $earnings['Rice allowance']['amount']);
        $this->assertSame(1000.0, (float) $earnings['Perfect attendance']['amount']);
        $this->assertSame(50.0, (float) $deductions['Co-op share']['amount']);
        $this->assertEqualsWithDelta((float) $item->gross_pay, collect($slip['earnings'])->sum('amount'), 0.01);
        $this->assertEqualsWithDelta((float) $item->gross_pay - (float) $item->net_pay, collect($slip['deductions'])->sum('amount'), 0.01);
    }

    public function test_rules_can_be_created_checked_switched_and_deleted(): void
    {
        $this->signedIn($this->user)->post(route('payroll-settings.rules.store'), $this->ruleInput([
            'name' => 'Trip incentive',
            'code' => 'trip',
            'method' => 'per_unit',
            'amount' => 150,
            'unit' => 'days_worked',
        ]))->assertRedirect(route('payroll-settings.rules.index'))->assertSessionHas('success');
        $trip = PayrollRule::query()->where('code', 'trip')->sole();
        $this->assertSame('₱150.00 × days worked', $trip->describe());

        // Bad formula, unknown value, code that is a value name, duplicate code, self reference.
        $this->signedIn($this->user)->post(route('payroll-settings.rules.store'), $this->ruleInput(['code' => 'a1', 'method' => 'formula', 'formula' => 'IF(1, 2)']))
            ->assertSessionHasErrors('formula');
        $this->signedIn($this->user)->post(route('payroll-settings.rules.store'), $this->ruleInput(['code' => 'a2', 'method' => 'formula', 'formula' => 'salary * 2']))
            ->assertSessionHasErrors('formula');
        $this->signedIn($this->user)->post(route('payroll-settings.rules.store'), $this->ruleInput(['code' => 'basic_pay']))
            ->assertSessionHasErrors('code');
        $this->signedIn($this->user)->post(route('payroll-settings.rules.store'), $this->ruleInput(['code' => 'trip']))
            ->assertSessionHasErrors('code');
        $this->signedIn($this->user)->post(route('payroll-settings.rules.store'), $this->ruleInput(['code' => 'Bad Code']))
            ->assertSessionHasErrors('code');
        $this->signedIn($this->user)->post(route('payroll-settings.rules.store'), $this->ruleInput(['code' => 'loop', 'method' => 'formula', 'formula' => 'loop + 1']))
            ->assertSessionHasErrors('formula');
        // net_pay is only known for deductions.
        $this->signedIn($this->user)->post(route('payroll-settings.rules.store'), $this->ruleInput(['code' => 'a3', 'method' => 'formula', 'formula' => 'net_pay * 1%']))
            ->assertSessionHasErrors('formula');

        // A deduction that uses the trip code.
        $this->signedIn($this->user)->post(route('payroll-settings.rules.store'), $this->ruleInput([
            'name' => 'Trip fund',
            'code' => 'trip_fund',
            'kind' => 'deduction',
            'method' => 'formula',
            'formula' => 'trip * 2% + net_pay * 0',
        ]))->assertSessionHasNoErrors();

        $this->signedIn($this->user)->postJson(route('payroll-settings.rules.check'), ['formula' => 'trip * 2', 'kind' => 'earning'])
            ->assertOk()->assertJson(['ok' => true]);
        $this->signedIn($this->user)->postJson(route('payroll-settings.rules.check'), ['formula' => 'nope', 'kind' => 'earning'])
            ->assertOk()->assertJson(['ok' => false]);

        $this->signedIn($this->user)->put(route('payroll-settings.rules.update', $trip), $this->ruleInput([
            'name' => 'Trip incentive',
            'code' => 'trip',
            'method' => 'fixed',
            'amount' => 200,
            'payroll_groups' => ['1'],
            'employee_biometric_ids' => [$this->employee->id],
        ]))->assertRedirect(route('payroll-settings.rules.index'));
        $trip->refresh();
        $this->assertSame(['1'], $trip->payroll_groups);
        $this->assertSame([$this->employee->id], $trip->employee_biometric_ids);
        $this->assertNull($trip->unit);

        $this->signedIn($this->user)->put(route('payroll-settings.rules.active', $trip), ['is_active' => false])->assertSessionHas('success');
        $this->assertFalse($trip->fresh()->is_active);

        // In use by "Trip fund", so it cannot be deleted yet.
        $this->signedIn($this->user)->delete(route('payroll-settings.rules.destroy', $trip))->assertSessionHasErrors('rule');
        $this->signedIn($this->user)->delete(route('payroll-settings.rules.destroy', PayrollRule::query()->where('code', 'trip_fund')->sole()))->assertSessionHasNoErrors();
        $this->signedIn($this->user)->delete(route('payroll-settings.rules.destroy', $trip))->assertRedirect(route('payroll-settings.rules.index'));
        $this->assertSoftDeleted($trip);
    }

    public function test_the_test_run_computes_and_compares_without_saving_anything(): void
    {
        $this->prepareCutoff();
        $counts = fn (): array => [Payroll::withTrashed()->count(), PayrollItem::query()->count(), PayrollAuditLog::query()->count()];
        $before = $counts();

        $values = PayrollSettingCatalog::defaults();
        $values['philhealth_rate'] = 0.06;

        $response = $this->signedIn($this->user)->postJson(route('payroll-settings.test.run'), [
            'employee_biometric_id' => $this->employee->id,
            'cutoff_month' => 10,
            'cutoff_year' => 2026,
            'cutoff_type' => 'second',
            'compare_values' => $values,
            'compare_rule' => $this->ruleInput(['name' => 'Draft bonus', 'code' => '', 'amount' => 250]),
        ])->assertOk();

        $response->assertJsonPath('scope', 'employee')
            ->assertJsonPath('baseline.label', 'Starting rules (from Jan 01, 2000)')
            ->assertJsonPath('baseline.items.0.name', 'Rosa Manalo');

        $baseline = collect($response->json('baseline.items.0.lines'))->keyBy('key');
        $candidate = collect($response->json('candidate.items.0.lines'))->keyBy('key');
        $this->assertSame(608.33, (float) $baseline['philhealth']['amount']);
        $this->assertSame(730.0, (float) $candidate['philhealth']['amount']);
        $this->assertSame(250.0, (float) $candidate['rule:draft_rule']['amount']);
        $this->assertFalse($baseline->has('rule:draft_rule'));
        $this->assertEqualsWithDelta((float) $response->json('baseline.items.0.gross') + 250, (float) $response->json('candidate.items.0.gross'), 0.001);
        $this->assertEqualsWithDelta((float) $response->json('baseline.items.0.net') + 250 - 121.67, (float) $response->json('candidate.items.0.net'), 0.02);

        $this->assertSame($before, $counts(), 'The test run must not leave any rows behind.');

        // Whole group.
        $this->signedIn($this->user)->postJson(route('payroll-settings.test.run'), [
            'garage_group' => '1',
            'cutoff_month' => 10,
            'cutoff_year' => 2026,
            'cutoff_type' => 'second',
        ])->assertOk()->assertJsonPath('scope', 'employee')->assertJsonCount(1, 'baseline.items');

        // A group the user may not see.
        $this->signedIn($this->user)->postJson(route('payroll-settings.test.run'), [
            'garage_group' => '2',
            'cutoff_month' => 10,
            'cutoff_year' => 2026,
            'cutoff_type' => 'second',
        ])->assertUnprocessable()->assertJsonValidationErrors('garage_group');

        $this->assertSame($before, $counts());
    }

    public function test_contribution_calculator(): void
    {
        $response = $this->signedIn($this->user)->postJson(route('payroll-settings.test.contributions'), ['salary' => 20000])->assertOk();
        $response->assertJsonPath('sss.employee', 1000)
            ->assertJsonPath('philhealth.employee', 500)
            ->assertJsonPath('pagibig.employee', 200);
        $this->assertCount(61, $response->json('sss_table'));

        $values = PayrollSettingCatalog::defaults();
        $values['pagibig_max_fund_salary'] = 5000;
        $this->signedIn($this->user)->postJson(route('payroll-settings.test.contributions'), ['salary' => 20000, 'values' => $values])
            ->assertOk()->assertJsonPath('pagibig.employee', 100);
    }

    private function prepareCutoff(): void
    {
        EmployeePlottingSchedule::query()->create([
            'employee_biometric_id' => $this->employee->id,
            'biometric_employee_id' => '5510001',
            'employee_no' => '5510001',
            'employee_name' => 'Rosa Manalo',
            'work_date' => null,
            'shift_name' => 'Regular Shift',
            'workday_type' => WorkdayType::EightHours->value,
            'paid_work_minutes' => WorkdayType::EightHours->paidMinutes(),
            'lunch_break_minutes' => WorkdayType::EightHours->lunchMinutes(),
            'time_in' => '08:00',
            'time_out' => '17:00',
            'grace_minutes' => 15,
            'status' => 'scheduled',
            'day_offs' => ['Sunday'],
            'day_off' => 'Sunday',
        ]);

        PayrollEmployeeSalary::query()->create([
            'employee_biometric_id' => $this->employee->id,
            'biometric_employee_id' => '5510001',
            'employee_no' => '5510001',
            'employee_name' => 'Rosa Manalo',
            'rate_type' => 'daily',
            'basic_salary' => 800,
            'paid_day_off' => false,
            'is_active' => true,
        ]);

        foreach (CarbonPeriod::create('2026-09-26', '2026-10-10') as $date) {
            if ($date->isSunday()) {
                continue;
            }

            foreach (['08:00:00', '17:00:00'] as $time) {
                MirasolBiometricsLog::query()->create([
                    'crosschex_account' => 'main',
                    'crosschex_id' => sha1($date->toDateString().$time),
                    'employee_no' => '5510001',
                    'employee_name' => 'Rosa Manalo',
                    'check_time' => $date->toDateString().' '.$time,
                    'device_sn' => '0770100024370009',
                ]);
            }
        }

        foreach (CarbonPeriod::create('2026-09-26', '2026-10-10') as $date) {
            app(DailyAttendanceSummaryService::class)->buildForDate($date->toDateString());
        }
    }

    private function generate(): PayrollItem
    {
        $payroll = app(PayrollComputationService::class)->generate([
            'cutoff_month' => 10,
            'cutoff_year' => 2026,
            'cutoff_type' => 'second',
            'garage_group' => 1,
        ], $this->user->id);

        return PayrollItem::query()->with('payroll')->where('payroll_id', $payroll->id)->where('employee_biometric_id', $this->employee->id)->sole();
    }

    /** @param  array<string, mixed>  $changes */
    private function version(string $from, array $changes): PayrollSettingVersion
    {
        return app(PayrollSettingsService::class)->create([
            'effective_from' => $from,
            'label' => 'Test '.$from,
            'values' => array_merge(PayrollSettingCatalog::defaults(), $changes),
        ], $this->user->id);
    }

    /** @param  array<string, mixed>  $attributes */
    private function rule(array $attributes): PayrollRule
    {
        return PayrollRule::query()->create(array_merge([
            'name' => 'Rule',
            'code' => 'rule',
            'kind' => PayrollRule::KIND_EARNING,
            'method' => 'fixed',
            'amount' => 500,
            'cutoff' => 'every',
            'rate_type' => 'all',
            'is_active' => true,
            'sort_order' => 1,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function ruleInput(array $overrides): array
    {
        return array_merge([
            'name' => 'Rule',
            'code' => 'rule',
            'kind' => 'earning',
            'method' => 'fixed',
            'amount' => 100,
            'base' => 'basic_pay',
            'unit' => 'days_worked',
            'formula' => '',
            'cutoff' => 'every',
            'rate_type' => 'all',
            'payroll_groups' => [],
            'employee_biometric_ids' => [],
            'effective_from' => '',
            'effective_to' => '',
            'is_active' => true,
            'sort_order' => 0,
            'notes' => '',
        ], $overrides);
    }

    private function signedIn(User $user): static
    {
        return $this->actingAs($user)
            ->withSession(['_token' => 'settings-test-csrf', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'settings-test-csrf');
    }

    /** @param  list<string>  $permissions */
    private function makeUser(array $permissions, string $name = 'settingsadmin'): User
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $user = User::factory()->create([
            'username' => $name,
            'email' => $name.'@example.com',
            'password' => Hash::make('Password123!Password'),
            'role' => 'Admin',
            'account_status' => 'active',
            'must_change_password' => false,
        ]);

        $role = Role::findOrCreate('Role '.$name, 'web');
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
