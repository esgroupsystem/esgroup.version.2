<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Repositories\Contracts\Payroll\BenefitSettlementRepositoryInterface;
use App\Repositories\Contracts\Payroll\PayrollRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Manual government-benefit settlement on a closing-cutoff (11-25) draft item: how much of the
 * opening cutoff's employee share to reimburse. Saving re-runs the monthly true-up at once.
 */
final class BenefitSettlementService
{
    /** Program => [request field, item column, liability key, label]. */
    private const PROGRAMS = [
        'sss' => ['sss_employee_reimbursement', 'sss_employee', 'SSS'],
        'philhealth' => ['philhealth_employee_reimbursement', 'philhealth_employee', 'PhilHealth'],
        'pagibig' => ['pagibig_employee_reimbursement', 'pagibig_employee', 'Pag-IBIG'],
    ];

    public function __construct(
        private readonly PayrollRepositoryInterface $payrolls,
        private readonly BenefitSettlementRepositoryInterface $settlements,
        private readonly PayrollGroupAccessService $groups,
        private readonly MonthlyGovernmentContributionService $contributions,
        private readonly MonthlyGovernmentReconciliationService $reconciliation,
    ) {}

    /**
     * @param  array{mode: string, sss_employee_reimbursement: mixed, philhealth_employee_reimbursement: mixed, pagibig_employee_reimbursement: mixed, reason: string}  $data
     *
     * @throws ValidationException when the payroll is not an editable closing draft or a reimbursement is too high
     */
    public function save(Payroll $payroll, PayrollItem $item, array $data, ?int $userId): void
    {
        abort_if((int) $item->payroll_id !== (int) $payroll->id, 404);
        abort_unless($this->groups->allows($payroll->garage_group), 403);

        if ($payroll->status !== 'draft') {
            throw ValidationException::withMessages(['payroll' => 'Benefit settlement can only be changed while payroll is still in Draft status.']);
        }
        if ((string) $payroll->cutoff_type !== 'first') {
            throw ValidationException::withMessages(['payroll' => 'Benefit settlement actions are available on the business 2nd cutoff (11-25), where the monthly government contribution is reconciled.']);
        }

        DB::transaction(function () use ($payroll, $item, $data, $userId): void {
            $openingPayroll = $this->payrolls->finalizedOpeningFor($payroll);
            $opening = $openingPayroll ? $this->payrolls->matchingItemIn($openingPayroll, $item) : null;

            $liability = $this->contributions->compute(
                (float) ($opening->gross_pay ?? 0),
                (float) $item->gross_pay,
                (float) ($item->monthly_rate ?: ($opening->monthly_rate ?? 0)),
            );

            foreach (self::PROGRAMS as [$field, $column, $label]) {
                $this->assertWithinOpeningDeduction($field, (float) $data[$field], (float) ($opening->{$column} ?? 0), (float) ($liability[$column] ?? 0), $label);
            }

            $settlement = $this->settlements->forItem($item->id);
            if (! $settlement->exists) {
                $settlement->created_by = $userId;
            }
            $settlement->fill([
                'payroll_id' => $payroll->id,
                'employee_biometric_id' => $item->employee_biometric_id,
                'mode' => $data['mode'],
                'sss_employee_reimbursement' => round((float) $data['sss_employee_reimbursement'], 2),
                'philhealth_employee_reimbursement' => round((float) $data['philhealth_employee_reimbursement'], 2),
                'pagibig_employee_reimbursement' => round((float) $data['pagibig_employee_reimbursement'], 2),
                'reason' => $data['reason'],
                'updated_by' => $userId,
                'meta' => [
                    'opening_payroll_item_id' => $opening?->id,
                    'opening_collected' => collect(self::PROGRAMS)->map(fn (array $program): float => round((float) ($opening->{$program[1]} ?? 0), 2))->all(),
                    'monthly_statutory_employee_share' => collect(self::PROGRAMS)->map(fn (array $program): float => round((float) ($liability[$program[1]] ?? 0), 2))->all(),
                ],
            ]);
            $this->settlements->save($settlement);

            // Re-run the closing-cutoff true-up so the screen shows the effect before finalizing.
            $payroll->unsetRelation('items');
            $this->reconciliation->reconcileClosingCutoff($payroll, false, 'benefit_settlement_updated');
        });
    }

    /**
     * Over-withholding in the opening cutoff is already refunded by the automatic true-up, so a
     * manual reimbursement may only cover the rest of the opening deduction.
     */
    private function assertWithinOpeningDeduction(string $field, float $requested, float $openingCollected, float $monthlyLiability, string $label): void
    {
        $openingCollected = max(0, round($openingCollected, 2));
        $monthlyLiability = max(0, round($monthlyLiability, 2));
        $automaticCredit = max(0, round($openingCollected - $monthlyLiability, 2));
        $maximum = max(0, round($openingCollected - $automaticCredit, 2));

        if (round($requested, 2) > $maximum) {
            throw ValidationException::withMessages([$field => sprintf(
                '%s reimbursement cannot exceed PHP %s. Any opening-cutoff over-withholding is already refunded automatically by the monthly true-up.',
                $label,
                number_format($maximum, 2),
            )]);
        }
    }
}
