<?php

namespace App\Exports;

use App\Models\Payroll;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PayrollItemsExport implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    public function __construct(protected Payroll $payroll)
    {
    }

    public function title(): string
    {
        return 'Payroll';
    }

    public function headings(): array
    {
        return [
            'Payroll No.',
            'Employee No.',
            'Employee Name',
            'Bio ID',
            'Payable Days',
            'Payable Hours',
            'Regular Pay',
            'Holiday Pay',
            'Rest Day Pay',
            'OT Pay',
            'Other Additions',
            'Gross Pay',
            'SSS',
            'PhilHealth',
            'Pag-IBIG',
            'Tax',
            'Other Deductions',
            'Net Pay',
        ];
    }

    public function array(): array
    {
        return $this->payroll->items->map(function ($item): array {
            return [
                $this->payroll->payroll_number,
                $item->employee_no,
                $item->employee_name,
                $item->biometric_employee_id,
                (float) $item->total_payable_days,
                (float) $item->total_payable_hours,
                (float) $item->regular_pay,
                (float) $item->holiday_pay,
                (float) $item->rest_day_pay,
                (float) $item->overtime_pay,
                (float) $item->other_additions,
                (float) $item->gross_pay,
                (float) $item->sss_employee,
                (float) $item->philhealth_employee,
                (float) $item->pagibig_employee,
                (float) $item->withholding_tax,
                (float) $item->other_deductions,
                (float) $item->net_pay,
            ];
        })->toArray();
    }
}
