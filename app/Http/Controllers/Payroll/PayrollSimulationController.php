<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Payroll\Concerns\SharesPayrollTestOptions;
use App\Http\Requests\Payroll\PayrollContributionRequest;
use App\Http\Requests\Payroll\PayrollSimulationRequest;
use App\Services\Payroll\PayrollSettingsService;
use App\Services\Payroll\PayrollSimulationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Payroll → Payroll Settings → Test computation. Runs the real payroll engine and
 * throws the result away (nothing is saved).
 */
final class PayrollSimulationController extends Controller
{
    use SharesPayrollTestOptions;

    public function __construct(
        private readonly PayrollSimulationService $simulation,
        private readonly PayrollSettingsService $settings,
    ) {}

    public function index(Request $request): Response
    {
        $current = $this->settings->versionFor(null);

        return Inertia::render('payroll/settings/test', [
            'test' => $this->testOptions($this->simulation),
            'current' => ['label' => $current['label'], 'effective_from' => $current['effective_from']],
            'urls' => [
                'settings' => route('payroll-settings.index'),
                'rules' => route('payroll-settings.rules.index'),
                'test' => route('payroll-settings.test.index'),
            ],
        ]);
    }

    public function run(PayrollSimulationRequest $request): JsonResponse
    {
        return response()->json($this->simulation->run($request->validated()));
    }

    public function contributions(PayrollContributionRequest $request): JsonResponse
    {
        $values = $request->validated('values');

        return response()->json($this->simulation->contributions(
            (float) $request->validated('salary'),
            is_array($values) ? $values : null,
        ));
    }
}
