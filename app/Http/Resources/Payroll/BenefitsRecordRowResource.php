<?php

declare(strict_types=1);

namespace App\Http\Resources\Payroll;

use App\Models\EmployeeBiometric;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One person on Benefits Records (`payroll/benefits-records/index`): due / collected /
 * employer amounts per program. Wraps a BenefitRecordsService::buildIndex() row
 * (`employee`, `summary`, `identifiers`).
 */
final class BenefitsRecordRowResource extends JsonResource
{
    /** Program => [identifier key, due, collected, employer, total] summary keys. */
    private const PROGRAMS = [
        'SSS' => ['sss', 'sss_employee_total', 'sss_employee_collected', 'sss_employer_total', 'sss_total_contribution'],
        'PhilHealth' => ['philhealth', 'philhealth_employee', 'philhealth_employee_collected', 'philhealth_employer', 'philhealth_total'],
        'Pag-IBIG' => ['pagibig', 'pagibig_employee', 'pagibig_employee_collected', 'pagibig_employer', 'pagibig_total'],
    ];

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var EmployeeBiometric $person */
        $person = $this->resource['employee'];
        $summary = $this->resource['summary'];

        return [
            'id' => $person->id,
            'name' => $person->payroll_display_name,
            'employee_no' => $person->effective_employee_no,
            'company' => $person->company?->name,
            'group_label' => $person->payroll_group_label,
            'posted' => (bool) $summary['posted'],
            'settlement_status' => (string) ($summary['settlement_status'] ?? 'not_posted'),
            'settlement_mode' => (string) data_get($summary, 'settlement_meta.mode', ''),
            'payroll_numbers' => $summary['payroll_numbers'],
            'programs' => collect(self::PROGRAMS)->map(fn (array $keys, string $name): array => [
                'name' => $name,
                'id' => $this->resource['identifiers'][$keys[0]] ?? null,
                'due' => (float) ($summary[$keys[1]] ?? 0),
                'collected' => (float) ($summary[$keys[2]] ?? 0),
                'employer' => (float) ($summary[$keys[3]] ?? 0),
                'total' => (float) ($summary[$keys[4]] ?? 0),
            ])->values()->all(),
            'employee_total' => (float) $summary['employee_total'],
            'employee_collected_total' => (float) $summary['employee_collected_total'],
            'employer_total' => (float) $summary['employer_total'],
            'grand_total' => (float) $summary['grand_total'],
            'unrecovered' => (float) $summary['employee_share_unrecovered'],
        ];
    }
}
