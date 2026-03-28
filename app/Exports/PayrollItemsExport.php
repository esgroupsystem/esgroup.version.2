<?php

namespace App\Exports;

use App\Models\Payroll;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PayrollItemsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(protected Payroll $payroll)
    {
    }

    public function collection()
    {
        return $this->payroll->items()
            ->orderBy('employee_name')
            ->get()
            ->map(function ($item) {
                return [
                    'Employee No' => $item->employee_no,
                    'Biometric ID' => $item->biometric_employee_id,
                    'Employee Name' => $item->employee_name,
                    'Daily Rate' => $item->daily_rate,
                    'Payable Days' => $item->total_payable_days,
                    'Payable Hours' => $item->total_payable_hours,
                    'Late Minutes' => $item->total_late_minutes,
                    'Undertime Minutes' => $item->total_undertime_minutes,
                    'Overtime Minutes' => $item->total_overtime_minutes,
                    'Absent Days' => $item->total_absent_days,
                    'Gross Pay' => $item->gross_pay,
                    'Late Deduction' => $item->late_deduction,
                    'Undertime Deduction' => $item->undertime_deduction,
                    'Absence Deduction' => $item->absence_deduction,
                    'Overtime Pay' => $item->overtime_pay,
                    'Holiday Pay' => $item->holiday_pay,
                    'Rest Day Pay' => $item->rest_day_pay,
                    'Leave Pay' => $item->leave_pay,
                    'SSS Employee' => $item->sss_employee,
                    'PhilHealth Employee' => $item->philhealth_employee,
                    'PagIBIG Employee' => $item->pagibig_employee,
                    'Withholding Tax' => $item->withholding_tax,
                    'Govt Deductions Total' => $item->total_employee_government_deductions,
                    'Other Additions' => $item->other_additions,
                    'Other Deductions' => $item->other_deductions,
                    'Net Pay' => $item->net_pay,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Employee No',
            'Biometric ID',
            'Employee Name',
            'Daily Rate',
            'Payable Days',
            'Payable Hours',
            'Late Minutes',
            'Undertime Minutes',
            'Overtime Minutes',
            'Absent Days',
            'Gross Pay',
            'Late Deduction',
            'Undertime Deduction',
            'Absence Deduction',
            'Overtime Pay',
            'Holiday Pay',
            'Rest Day Pay',
            'Leave Pay',
            'SSS Employee',
            'PhilHealth Employee',
            'PagIBIG Employee',
            'Withholding Tax',
            'Govt Deductions Total',
            'Other Additions',
            'Other Deductions',
            'Net Pay',
        ];
    }
}