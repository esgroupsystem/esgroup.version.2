<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Payroll\Concerns\SharesPayrollTestOptions;
use App\Http\Requests\Payroll\CheckPayrollFormulaRequest;
use App\Http\Requests\Payroll\PayrollRuleRequest;
use App\Http\Resources\Payroll\PayrollRuleFormResource;
use App\Http\Resources\Payroll\PayrollRuleResource;
use App\Models\PayrollRule;
use App\Services\Payroll\PayrollRuleService;
use App\Services\Payroll\PayrollSimulationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Payroll → Payroll Settings → Rules (custom earnings and deductions).
 */
final class PayrollRuleController extends Controller
{
    use SharesPayrollTestOptions;

    public function __construct(
        private readonly PayrollRuleService $rules,
        private readonly PayrollSimulationService $simulation,
    ) {}

    public function index(Request $request): Response
    {
        $canManage = $request->user()->can('payroll-settings.manage');

        return Inertia::render('payroll/settings/rules', [
            'rules' => $this->rules->list()->map(fn (PayrollRule $rule): array => PayrollRuleResource::make($rule)->resolve($request) + [
                'urls' => $canManage ? [
                    'edit' => route('payroll-settings.rules.edit', $rule),
                    'destroy' => route('payroll-settings.rules.destroy', $rule),
                    'active' => route('payroll-settings.rules.active', $rule),
                ] : null,
            ])->values(),
            'can' => ['manage' => $canManage],
            'urls' => [
                'create' => route('payroll-settings.rules.create'),
                'settings' => route('payroll-settings.index'),
                'rules' => route('payroll-settings.rules.index'),
                'test' => route('payroll-settings.test.index'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->form($request, null);
    }

    public function store(PayrollRuleRequest $request): RedirectResponse
    {
        $rule = $this->rules->create($request->validated(), $request->user()?->id);

        return redirect()->route('payroll-settings.rules.index')->with('success', sprintf('Rule "%s" created. It applies to payrolls generated or recomputed from now on.', $rule->name));
    }

    public function edit(Request $request, PayrollRule $rule): Response
    {
        return $this->form($request, $rule);
    }

    public function update(PayrollRuleRequest $request, PayrollRule $rule): RedirectResponse
    {
        $this->rules->update($rule, $request->validated(), $request->user()?->id);

        return redirect()->route('payroll-settings.rules.index')->with('success', 'Rule updated. Finalized payrolls keep their old amounts.');
    }

    public function active(Request $request, PayrollRule $rule): RedirectResponse
    {
        $active = $request->boolean('is_active');
        $this->rules->setActive($rule, $active, $request->user()?->id);

        return back()->with('success', sprintf('Rule "%s" %s.', $rule->name, $active ? 'turned on' : 'turned off'));
    }

    public function destroy(PayrollRule $rule): RedirectResponse
    {
        $this->rules->delete($rule);

        return redirect()->route('payroll-settings.rules.index')->with('success', 'Rule deleted. Finalized payrolls keep their old amounts.');
    }

    public function checkFormula(CheckPayrollFormulaRequest $request): JsonResponse
    {
        $formula = trim((string) $request->validated('formula'));

        if ($formula === '') {
            return response()->json(['ok' => false, 'message' => 'Write the formula.', 'names' => []]);
        }

        return response()->json($this->rules->checkFormula(
            $formula,
            (string) $request->validated('kind'),
            $request->validated('rule_id') ? (int) $request->validated('rule_id') : null,
        ));
    }

    private function form(Request $request, ?PayrollRule $rule): Response
    {
        $options = $this->testOptions($this->simulation);

        return Inertia::render('payroll/settings/rule-form', [
            'rule' => $rule ? ['id' => $rule->id, 'name' => $rule->name] : null,
            'values' => PayrollRuleFormResource::make($rule)->resolve($request),
            'options' => [
                'kinds' => self::options(PayrollRule::KINDS),
                'methods' => self::options(PayrollRule::METHODS),
                'cutoffs' => self::options(PayrollRule::CUTOFFS),
                'rateTypes' => self::options(PayrollRule::RATE_TYPES),
                'bases' => self::options(PayrollRule::moneyVariables()),
                'units' => self::options(PayrollRule::unitVariables()),
                'variables' => collect(PayrollRule::VARIABLES)->map(fn (array $variable, string $name): array => ['name' => $name] + $variable)->values(),
                'otherRules' => $this->rules->list()
                    ->reject(fn (PayrollRule $other): bool => $rule !== null && $other->id === $rule->id)
                    ->map(fn (PayrollRule $other): array => ['code' => $other->code, 'name' => $other->name, 'kind' => $other->kind])
                    ->values(),
                'groups' => $options['groups'],
                'employees' => $options['employees'],
            ],
            'test' => $options,
            'urls' => [
                'index' => route('payroll-settings.rules.index'),
                'submit' => $rule ? route('payroll-settings.rules.update', $rule) : route('payroll-settings.rules.store'),
                'check' => route('payroll-settings.rules.check'),
            ],
        ]);
    }

    /**
     * @param  array<string, string>  $labels
     * @return list<array{value: string, label: string}>
     */
    private static function options(array $labels): array
    {
        return collect($labels)->map(fn (string $label, string $value): array => ['value' => $value, 'label' => $label])->values()->all();
    }
}
