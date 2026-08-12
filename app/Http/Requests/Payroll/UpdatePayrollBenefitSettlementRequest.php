<?php

namespace App\Http\Requests\Payroll;

use App\Models\PayrollBenefitSettlement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePayrollBenefitSettlementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('payroll-benefit-settlements.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'mode' => ['required', Rule::in(PayrollBenefitSettlement::MODES)],
            'sss_employee_reimbursement' => ['nullable', 'numeric', 'min:0'],
            'philhealth_employee_reimbursement' => ['nullable', 'numeric', 'min:0'],
            'pagibig_employee_reimbursement' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'min:8', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sss_employee_reimbursement' => $this->input('sss_employee_reimbursement', 0),
            'philhealth_employee_reimbursement' => $this->input('philhealth_employee_reimbursement', 0),
            'pagibig_employee_reimbursement' => $this->input('pagibig_employee_reimbursement', 0),
        ]);
    }
}
