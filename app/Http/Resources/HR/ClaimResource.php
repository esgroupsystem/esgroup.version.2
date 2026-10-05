<?php

declare(strict_types=1);

namespace App\Http\Resources\HR;

use App\Models\Claim;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One claim on the SSS / Maternity / Paternity page (`hr/claims/index`). Load `employee` first.
 *
 * @mixin Claim
 */
final class ClaimResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => (string) $this->employee_id,
            'employee' => $this->employee?->full_name,
            'claim_type' => $this->claim_type,
            'status' => $this->status,
            'reference_no' => $this->reference_no,
            ...collect(array_keys(Claim::DATE_FIELDS))->mapWithKeys(fn (string $field): array => [
                $field => $this->{$field}?->format('Y-m-d'),
            ])->all(),
            'amount' => $this->amount !== null ? (string) $this->amount : null,
            'remarks' => $this->remarks,
        ];
    }
}
