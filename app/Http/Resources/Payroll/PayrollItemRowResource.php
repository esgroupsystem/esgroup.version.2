<?php

declare(strict_types=1);

namespace App\Http\Resources\Payroll;

use App\Models\Payroll;
use App\Models\PayrollItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One employee row of a payroll (`payroll/payrolls/show`), with the audit badges that flag rows
 * needing review: missing summary, no regular / gross / net / payable, heavy deductions,
 * additions and adjustments.
 *
 * @mixin PayrollItem
 */
final class PayrollItemRowResource extends JsonResource
{
    public function __construct(
        PayrollItem $item,
        private readonly Payroll $payroll,
    ) {
        parent::__construct($item);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $additions = (float) ($this->holiday_pay ?? 0) + (float) ($this->rest_day_pay ?? 0) + (float) ($this->overtime_pay ?? 0)
            + (float) ($this->night_differential_pay ?? 0) + (float) ($this->leave_pay ?? 0) + (float) ($this->other_additions ?? 0);
        $government = (float) ($this->total_employee_government_deductions ?? 0);
        $otherDeductions = (float) ($this->other_deductions ?? 0);
        $regular = (float) ($this->regular_pay ?? 0);
        $gross = (float) ($this->gross_pay ?? 0);
        $net = (float) ($this->net_pay ?? 0);
        $payableDays = (float) ($this->total_payable_days ?? 0);
        $payableHours = (float) ($this->total_payable_hours ?? 0);
        $settlementOnly = (bool) data_get($this->meta, 'closing_benefit_settlement_only', false);
        $tags = collect(data_get($this->meta, 'adjustment_tags', []));

        $badges = $this->badges($settlementOnly, $regular, $gross, $net, $payableDays, $payableHours, $government + $otherDeductions, $additions, $tags->all());
        $tones = array_column($badges, 'tone');

        return [
            'id' => $this->id,
            'name' => $this->payroll_display_name,
            'employee_no' => $this->employee_no,
            'settlement_only' => $settlementOnly,
            'tags' => $tags->take(3)->map(fn ($tag): array => [
                'label' => (string) data_get($tag, 'label', 'Adjustment'),
                'amount' => (float) data_get($tag, 'amount', 0),
            ])->values(),
            'payable_days' => $payableDays,
            'regular' => $regular,
            'additions' => $additions,
            'government' => $government,
            'other_deductions' => $otherDeductions,
            'gross' => $gross,
            'net' => $net,
            'badges' => $badges === [] ? [['label' => 'OK', 'tone' => 'success']] : $badges,
            'severity' => in_array('danger', $tones, true) ? 'danger' : (in_array('warning', $tones, true) ? 'warning' : 'ok'),
            'url' => route('payroll.items.show', [$this->payroll, $this->resource]),
        ];
    }

    /**
     * @param  list<mixed>  $tags
     * @return list<array{label: string, tone: string}>
     */
    private function badges(bool $settlementOnly, float $regular, float $gross, float $net, float $payableDays, float $payableHours, float $deductions, float $additions, array $tags): array
    {
        $badges = [];
        $missingDays = (int) data_get($this->meta, 'attendance_summary_coverage.missing_days', 0);

        if ($settlementOnly) {
            $badges[] = ['label' => 'Benefit Settlement Only', 'tone' => 'info'];
        } elseif ((bool) data_get($this->meta, 'safe_zero_pay', false)) {
            $badges[] = ['label' => 'No Summary', 'tone' => 'danger'];
        } elseif ($missingDays > 0) {
            $badges[] = ['label' => 'Summary Gap '.$missingDays.'d', 'tone' => 'warning'];
        }

        if (! $settlementOnly) {
            if ($regular <= 0) {
                $badges[] = ['label' => 'No Regular', 'tone' => 'danger'];
            }
            if ($gross <= 0) {
                $badges[] = ['label' => 'No Gross', 'tone' => 'danger'];
            }
            if ($net <= 0) {
                $badges[] = ['label' => 'No Net', 'tone' => 'danger'];
            }
            if ($payableDays <= 0 && $payableHours <= 0) {
                $badges[] = ['label' => 'No Payable', 'tone' => 'warning'];
            }
        } elseif ($net < -0.009) {
            $badges[] = ['label' => 'Negative Settlement', 'tone' => 'danger'];
        }

        if ($gross > 0 && $deductions > $gross * 0.6) {
            $badges[] = ['label' => 'High Deduct.', 'tone' => 'warning'];
        }
        if ($additions > 0) {
            $badges[] = ['label' => 'Additions', 'tone' => 'info'];
        }

        $paid = collect($tags)->filter(fn ($tag): bool => (bool) data_get($tag, 'paid_this_cutoff', false))->count();
        if ($paid > 0) {
            $badges[] = ['label' => 'ADJ Paid '.$paid, 'tone' => 'primary'];
        } elseif ($tags !== []) {
            $badges[] = ['label' => 'ADJ '.count($tags), 'tone' => 'info'];
        }

        return $badges;
    }
}
