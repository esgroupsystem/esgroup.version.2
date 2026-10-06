<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payroll\Concerns;

use App\Services\Payroll\PayrollSimulationService;

/** Props of the shared test panel (Payroll Settings pages and the rule / version forms). */
trait SharesPayrollTestOptions
{
    /** @return array<string, mixed> */
    private function testOptions(PayrollSimulationService $simulation): array
    {
        return $simulation->options() + [
            'urls' => [
                'run' => route('payroll-settings.test.run'),
                'contributions' => route('payroll-settings.test.contributions'),
            ],
        ];
    }
}
