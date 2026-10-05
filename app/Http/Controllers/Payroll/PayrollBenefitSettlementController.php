<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\UpdatePayrollBenefitSettlementRequest;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Services\Payroll\BenefitSettlementService;
use Illuminate\Http\RedirectResponse;

/**
 * The government-benefit settlement form on a closing-cutoff payroll item.
 */
final class PayrollBenefitSettlementController extends Controller
{
    public function __construct(
        private readonly BenefitSettlementService $settlements,
    ) {}

    public function store(UpdatePayrollBenefitSettlementRequest $request, Payroll $payroll, PayrollItem $item): RedirectResponse
    {
        $userId = $request->user()?->getKey();
        $this->settlements->save($payroll, $item, $request->validated(), $userId === null ? null : (int) $userId);

        return redirect()
            ->route('payroll.items.show', [$payroll, $item])
            ->with('success', 'Government benefit settlement updated and payroll item recalculated.');
    }
}
