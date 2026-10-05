<?php

declare(strict_types=1);

namespace App\Http\Resources\Payroll;

use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One payroll run on the Payroll list (`payroll/payrolls/index`). Load `generator` and the items count.
 *
 * @mixin Payroll
 */
final class PayrollRowResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payroll_number' => $this->payroll_number,
            'cutoff_label' => $this->cutoff_label,
            'period_start' => $this->period_start?->format('M d, Y'),
            'period_end' => $this->period_end?->format('M d, Y'),
            'contribution_label' => $this->contribution_label,
            'items_count' => (int) ($this->items_count ?? 0),
            'group_label' => $this->garage_group_label,
            'is_finalized' => $this->status === 'finalized',
            'generator_name' => $this->generator->full_name ?? $this->generator->name ?? 'N/A',
            'generated_at' => $this->generated_at?->timezone('Asia/Manila')->format('M d, Y h:i A'),
            'urls' => [
                'show' => route('payroll.show', $this->resource),
                'destroy' => route('payroll.destroy', $this->resource),
            ],
        ];
    }
}
