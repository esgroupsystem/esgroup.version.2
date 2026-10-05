<?php

declare(strict_types=1);

namespace App\Repositories\Payroll;

use App\Models\PayrollBenefitSettlement;
use App\Repositories\Contracts\Payroll\BenefitSettlementRepositoryInterface;

final class BenefitSettlementRepository implements BenefitSettlementRepositoryInterface
{
    public function forItem(int $payrollItemId): PayrollBenefitSettlement
    {
        return PayrollBenefitSettlement::query()->firstOrNew(['payroll_item_id' => $payrollItemId]);
    }

    public function save(PayrollBenefitSettlement $settlement): void
    {
        $settlement->save();
    }
}
