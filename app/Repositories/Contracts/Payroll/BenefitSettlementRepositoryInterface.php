<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Payroll;

use App\Models\PayrollBenefitSettlement;

/** Manual government-benefit settlements on closing-cutoff payroll items (one per item). */
interface BenefitSettlementRepositoryInterface
{
    /** The item's settlement, or a new unsaved one. */
    public function forItem(int $payrollItemId): PayrollBenefitSettlement;

    public function save(PayrollBenefitSettlement $settlement): void;
}
