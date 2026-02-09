<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],

            'claim_type' => ['required', 'string', Rule::in(['SSS', 'MATERNITY', 'PATERNITY'])],
            'status' => ['required', 'string', Rule::in([
                'Draft','Ongoing','Approved','Requested','Released','Rejected'
            ])],

            'reference_no' => ['nullable', 'string', 'max:100'],

            'date_of_notification' => ['nullable', 'date'],
            'date_filed' => ['nullable', 'date', 'after_or_equal:date_of_notification'],
            'approval_date' => ['nullable', 'date', 'after_or_equal:date_filed'],
            'fund_request_date' => ['nullable', 'date', 'after_or_equal:approval_date'],
            'fund_released_date' => ['nullable', 'date', 'after_or_equal:fund_request_date'],

            'amount' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'date_filed.after_or_equal' => 'Date Filed must be on or after Date of Notification.',
            'approval_date.after_or_equal' => 'Approval Date must be on or after Date Filed.',
            'fund_request_date.after_or_equal' => 'Fund Request Date must be on or after Approval Date.',
            'fund_released_date.after_or_equal' => 'Fund Released Date must be on or after Fund Request Date.',
        ];
    }
}
