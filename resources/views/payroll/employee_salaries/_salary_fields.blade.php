@php
    use Illuminate\Support\Carbon;

    $salary = $salary ?? null;

    $scheduleOptions = [
        'none' => 'No Deduction / Not Applicable',
        'first_cutoff' => '1st Cutoff Only',
        'second_cutoff' => '2nd Cutoff Only',
        'every_cutoff' => 'Every Cutoff',
    ];

    $value = function (string $field, mixed $default = null) use ($salary) {
        return old($field, data_get($salary, $field, $default));
    };

    $dateValue = function (string $field) use ($salary) {
        $old = old($field);

        if ($old !== null) {
            return $old;
        }

        $date = data_get($salary, $field);

        return blank($date) ? '' : Carbon::parse($date)->format('Y-m-d');
    };

    $existingOtherDeductions = collect(old('other_deductions', $salary?->otherDeductions?->map(function ($deduction) {
        return [
            'name' => $deduction->name,
            'total_amount' => $deduction->total_amount,
            'payment_amount' => $deduction->payment_amount,
            'deduction_schedule' => $deduction->deduction_schedule,
            'start_date' => blank($deduction->start_date) ? '' : Carbon::parse($deduction->start_date)->format('Y-m-d'),
            'remarks' => $deduction->remarks,
        ];
    })->toArray() ?? []))->values();

    $loanItems = [
        ['label' => 'SSS Loan', 'prefix' => 'sss_loan', 'help' => 'SSS salary loan or other SSS deduction.'],
        ['label' => 'Pag-IBIG Loan', 'prefix' => 'pagibig_loan', 'help' => 'Pag-IBIG MPL, calamity loan, or other Pag-IBIG deduction.'],
        ['label' => 'PhilHealth Loan', 'prefix' => 'philhealth_loan', 'help' => 'Use only if your company tracks PhilHealth-related deductions.'],
        ['label' => 'Cash Advance / Vale', 'prefix' => 'cash_advance', 'help' => 'Cash advance deduction from payroll.'],
        ['label' => 'Other Loan', 'prefix' => 'other_loan', 'help' => 'Company loan or other employee deduction.'],
    ];
@endphp

<div class="col-md-4">
    <label class="form-label">Employee No</label>
    <input type="text" name="employee_no" id="employee_no" class="form-control {{ $errors->has('employee_no') ? 'is-invalid' : '' }}" value="{{ $value('employee_no') }}" readonly>
    <div class="invalid-feedback">{{ $errors->first('employee_no') }}</div>
</div>

<div class="col-md-8">
    <label class="form-label">Employee Name</label>
    <input type="text" name="employee_name" id="employee_name" class="form-control {{ $errors->has('employee_name') ? 'is-invalid' : '' }}" value="{{ $value('employee_name') }}" required readonly>
    <div class="invalid-feedback">{{ $errors->first('employee_name') }}</div>
</div>

<div class="col-12">
    <hr>
    <h6 class="text-800 mb-1"><span class="fas fa-calculator text-primary me-2"></span>Rate Computation</h6>
    <p class="text-muted small mb-0">OT, late, undertime, and absent deductions are computed from basic salary.</p>
</div>

<div class="col-md-3">
    <label class="form-label">Rate Type</label>
    <select name="rate_type" id="rate_type" class="form-select {{ $errors->has('rate_type') ? 'is-invalid' : '' }} payroll-preview-input">
        <option value="daily" @selected($value('rate_type', 'daily') === 'daily')>Daily</option>
        <option value="monthly" @selected($value('rate_type', 'daily') === 'monthly')>Monthly</option>
    </select>
    <div class="invalid-feedback">{{ $errors->first('rate_type') }}</div>
</div>

<div class="col-md-3">
    <label class="form-label">Basic Salary</label>
    <input type="number" step="0.01" name="basic_salary" id="basic_salary" class="form-control {{ $errors->has('basic_salary') ? 'is-invalid' : '' }} payroll-preview-input" value="{{ $value('basic_salary', 0) }}" required>
    <div class="invalid-feedback">{{ $errors->first('basic_salary') }}</div>
</div>

<div class="col-md-3">
    <label class="form-label">OT Rate / Hour</label>
    <input type="number" step="0.01" name="ot_rate_per_hour" id="ot_rate_per_hour" class="form-control" value="{{ $value('ot_rate_per_hour', 0) }}" readonly>
</div>

<div class="col-md-3">
    <label class="form-label">Absent / Day</label>
    <input type="number" step="0.01" name="absent_deduction_per_day" id="absent_deduction_per_day" class="form-control" value="{{ $value('absent_deduction_per_day', 0) }}" readonly>
</div>

<div class="col-md-3">
    <label class="form-label">Late Deduction / Minute</label>
    <input type="number" step="0.0001" name="late_deduction_per_minute" id="late_deduction_per_minute" class="form-control" value="{{ $value('late_deduction_per_minute', 0) }}" readonly>
</div>

<div class="col-md-3">
    <label class="form-label">Undertime Deduction / Minute</label>
    <input type="number" step="0.0001" name="undertime_deduction_per_minute" id="undertime_deduction_per_minute" class="form-control" value="{{ $value('undertime_deduction_per_minute', 0) }}" readonly>
</div>

<div class="col-12">
    <hr>
    <h6 class="text-800 mb-1"><span class="fas fa-shield-alt text-success me-2"></span>Government Contributions</h6>
    <p class="text-muted small mb-0">Choose when SSS, Pag-IBIG, and PhilHealth will be deducted.</p>
</div>

<div class="col-md-4">
    <label class="form-label">SSS Deduction Schedule</label>
    <select name="sss_contribution_cutoff" id="sss_contribution_cutoff" class="form-select payroll-preview-input">
        @foreach ($scheduleOptions as $key => $label)
            <option value="{{ $key }}" @selected($value('sss_contribution_cutoff', 'first_cutoff') === $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="col-md-4">
    <label class="form-label">Pag-IBIG Deduction Schedule</label>
    <select name="pagibig_contribution_cutoff" id="pagibig_contribution_cutoff" class="form-select payroll-preview-input">
        @foreach ($scheduleOptions as $key => $label)
            <option value="{{ $key }}" @selected($value('pagibig_contribution_cutoff', 'second_cutoff') === $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="col-md-4">
    <label class="form-label">PhilHealth Deduction Schedule</label>
    <select name="philhealth_contribution_cutoff" id="philhealth_contribution_cutoff" class="form-select payroll-preview-input">
        @foreach ($scheduleOptions as $key => $label)
            <option value="{{ $key }}" @selected($value('philhealth_contribution_cutoff', 'second_cutoff') === $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="col-12">
    <div class="row g-3">
        <div class="col-md-4"><div class="border rounded-3 p-3 h-100"><div class="text-muted small">Monthly SSS Employee Share</div><div class="fs-5 fw-bold" id="preview_monthly_sss">0.00</div></div></div>
        <div class="col-md-4"><div class="border rounded-3 p-3 h-100"><div class="text-muted small">Monthly Pag-IBIG Employee Share</div><div class="fs-5 fw-bold" id="preview_monthly_pagibig">0.00</div></div></div>
        <div class="col-md-4"><div class="border rounded-3 p-3 h-100"><div class="text-muted small">Monthly PhilHealth Employee Share</div><div class="fs-5 fw-bold" id="preview_monthly_philhealth">0.00</div></div></div>
    </div>
</div>

<div class="col-12">
    <hr>
    <h6 class="text-800 mb-1"><span class="fas fa-gift text-warning me-2"></span>Allowances</h6>
</div>

<div class="col-md-3">
    <label class="form-label">Regular Allowance</label>
    <input type="number" step="0.01" name="allowance" id="allowance" class="form-control payroll-preview-input {{ $errors->has('allowance') ? 'is-invalid' : '' }}" value="{{ $value('allowance', 0) }}">
    <div class="invalid-feedback">{{ $errors->first('allowance') }}</div>
</div>

<div class="col-md-3">
    <label class="form-label">Allowance Release</label>
    <select name="allowance_release_schedule" id="allowance_release_schedule" class="form-select payroll-preview-input">
        @foreach ($scheduleOptions as $key => $label)
            <option value="{{ $key }}" @selected($value('allowance_release_schedule', 'every_cutoff') === $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="col-md-3">
    <label class="form-label">SIM / Cellular Load Allowance</label>
    <input type="number" step="0.01" name="sim_load_allowance" id="sim_load_allowance" class="form-control payroll-preview-input {{ $errors->has('sim_load_allowance') ? 'is-invalid' : '' }}" value="{{ $value('sim_load_allowance', 0) }}">
    <div class="invalid-feedback">{{ $errors->first('sim_load_allowance') }}</div>
</div>

<div class="col-md-3">
    <label class="form-label">SIM Load Release</label>
    <select name="sim_load_release_schedule" id="sim_load_release_schedule" class="form-select payroll-preview-input">
        @foreach ($scheduleOptions as $key => $label)
            <option value="{{ $key }}" @selected($value('sim_load_release_schedule', 'every_cutoff') === $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="col-12">
    <hr>
    <h6 class="text-800 mb-1"><span class="fas fa-hand-holding-usd text-danger me-2"></span>Loans and Cash Advance</h6>
    <p class="text-muted small mb-0">Input total amount, deduction amount per selected cutoff, schedule, and start date.</p>
</div>

<div class="col-12">
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th style="min-width: 170px;">Deduction Type</th>
                    <th style="min-width: 140px;">Total Amount</th>
                    <th style="min-width: 160px;">Deduction Amount</th>
                    <th style="min-width: 190px;">Schedule</th>
                    <th style="min-width: 150px;">Start Date</th>
                    <th style="min-width: 170px;">Estimated Last Payment</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($loanItems as $item)
                    @php($prefix = $item['prefix'])
                    <tr>
                        <td><div class="fw-semibold">{{ $item['label'] }}</div><div class="text-muted small">{{ $item['help'] }}</div></td>
                        <td><input type="number" step="0.01" name="{{ $prefix }}_total_amount" id="{{ $prefix }}_total_amount" class="form-control form-control-sm payroll-preview-input" value="{{ $value($prefix . '_total_amount', 0) }}"></td>
                        <td><input type="number" step="0.01" name="{{ $prefix }}_payment_amount" id="{{ $prefix }}_payment_amount" class="form-control form-control-sm payroll-preview-input" value="{{ $value($prefix . '_payment_amount', 0) }}"></td>
                        <td>
                            <select name="{{ $prefix }}_deduction_schedule" id="{{ $prefix }}_deduction_schedule" class="form-select form-select-sm payroll-preview-input">
                                @foreach ($scheduleOptions as $key => $label)
                                    <option value="{{ $key }}" @selected($value($prefix . '_deduction_schedule', 'none') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="date" name="{{ $prefix }}_start_date" id="{{ $prefix }}_start_date" class="form-control form-control-sm payroll-preview-input" value="{{ $dateValue($prefix . '_start_date') }}"></td>
                        <td><span class="badge bg-subtle-primary text-primary border" id="{{ $prefix }}_last_payment">—</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="col-12">
    <div class="card border shadow-sm">
        <div class="card-header bg-body-tertiary d-flex flex-column flex-md-row justify-content-between gap-2">
            <div>
                <h6 class="mb-1"><span class="fas fa-plus-circle text-primary me-2"></span>Additional Other Loans / Deductions</h6>
                <p class="mb-0 text-muted small">Add extra deductions such as uniform deduction, damage charge, cooperative loan, or other payroll deduction.</p>
            </div>
            <button type="button" class="btn btn-primary btn-sm align-self-start" id="addOtherDeductionBtn"><span class="fas fa-plus me-1"></span>Add Deduction</button>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 180px;">Name</th>
                            <th style="min-width: 130px;">Total Amount</th>
                            <th style="min-width: 140px;">Deduction / Cutoff</th>
                            <th style="min-width: 180px;">Schedule</th>
                            <th style="min-width: 140px;">Start Date</th>
                            <th style="min-width: 160px;">Estimated Last Payment</th>
                            <th style="min-width: 180px;">Remarks</th>
                            <th width="60" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="otherDeductionsBody">
                        @foreach ($existingOtherDeductions as $index => $deduction)
                            <tr class="other-deduction-row">
                                <td><input type="text" name="other_deductions[{{ $index }}][name]" class="form-control form-control-sm other-deduction-input payroll-preview-input" placeholder="e.g. Uniform Deduction" value="{{ $deduction['name'] ?? '' }}"></td>
                                <td><input type="number" step="0.01" name="other_deductions[{{ $index }}][total_amount]" class="form-control form-control-sm other-deduction-total other-deduction-input payroll-preview-input" value="{{ $deduction['total_amount'] ?? 0 }}"></td>
                                <td><input type="number" step="0.01" name="other_deductions[{{ $index }}][payment_amount]" class="form-control form-control-sm other-deduction-payment other-deduction-input payroll-preview-input" value="{{ $deduction['payment_amount'] ?? 0 }}"></td>
                                <td>
                                    <select name="other_deductions[{{ $index }}][deduction_schedule]" class="form-select form-select-sm other-deduction-schedule other-deduction-input payroll-preview-input">
                                        @foreach ($scheduleOptions as $key => $label)
                                            <option value="{{ $key }}" @selected(($deduction['deduction_schedule'] ?? 'none') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="date" name="other_deductions[{{ $index }}][start_date]" class="form-control form-control-sm other-deduction-start other-deduction-input payroll-preview-input" value="{{ $deduction['start_date'] ?? '' }}"></td>
                                <td><span class="badge bg-subtle-primary text-primary border other-deduction-last">—</span></td>
                                <td><input type="text" name="other_deductions[{{ $index }}][remarks]" class="form-control form-control-sm other-deduction-input" placeholder="Optional" value="{{ $deduction['remarks'] ?? '' }}"></td>
                                <td class="text-center"><button type="button" class="btn btn-sm btn-danger p-1 remove-other-deduction" title="Remove"><span class="fas fa-trash-alt"></span></button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div id="otherDeductionsEmptyState" class="text-center text-muted small py-3 {{ $existingOtherDeductions->isNotEmpty() ? 'd-none' : '' }}">No additional other deduction added yet.</div>
        </div>
    </div>
</div>

<template id="otherDeductionTemplate">
    <tr class="other-deduction-row">
        <td><input type="text" name="other_deductions[__INDEX__][name]" class="form-control form-control-sm other-deduction-input payroll-preview-input" placeholder="e.g. Uniform Deduction"></td>
        <td><input type="number" step="0.01" name="other_deductions[__INDEX__][total_amount]" class="form-control form-control-sm other-deduction-total other-deduction-input payroll-preview-input" value="0"></td>
        <td><input type="number" step="0.01" name="other_deductions[__INDEX__][payment_amount]" class="form-control form-control-sm other-deduction-payment other-deduction-input payroll-preview-input" value="0"></td>
        <td>
            <select name="other_deductions[__INDEX__][deduction_schedule]" class="form-select form-select-sm other-deduction-schedule other-deduction-input payroll-preview-input">
                @foreach ($scheduleOptions as $key => $label)
                    <option value="{{ $key }}" @selected($key === 'none')>{{ $label }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="date" name="other_deductions[__INDEX__][start_date]" class="form-control form-control-sm other-deduction-start other-deduction-input payroll-preview-input"></td>
        <td><span class="badge bg-subtle-primary text-primary border other-deduction-last">—</span></td>
        <td><input type="text" name="other_deductions[__INDEX__][remarks]" class="form-control form-control-sm other-deduction-input" placeholder="Optional"></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-danger p-1 remove-other-deduction" title="Remove"><span class="fas fa-trash-alt"></span></button></td>
    </tr>
</template>

<div class="col-12">
    <hr>
    <h6 class="text-800 mb-1"><span class="fas fa-receipt text-info me-2"></span>Live Payroll Preview</h6>
</div>

<div class="col-md-4"><div class="card border-0 bg-body-tertiary h-100"><div class="card-body"><div class="text-muted small">Monthly Basic Salary Equivalent</div><div class="fs-4 fw-bold" id="preview_monthly_basic">0.00</div><div class="small text-muted">Daily employees use basic salary × 22 days.</div></div></div></div>
<div class="col-md-4"><div class="card border-primary h-100"><div class="card-header py-2 bg-primary text-white">1st Cutoff Preview</div><div class="card-body small"><div class="d-flex justify-content-between"><span>Gross Preview</span><strong id="first_gross">0.00</strong></div><div class="d-flex justify-content-between"><span>Total Deductions</span><strong id="first_deductions">0.00</strong></div><hr><div class="d-flex justify-content-between fs-6"><span>Estimated Net</span><strong id="first_net">0.00</strong></div></div></div></div>
<div class="col-md-4"><div class="card border-info h-100"><div class="card-header py-2 bg-info text-white">2nd Cutoff Preview</div><div class="card-body small"><div class="d-flex justify-content-between"><span>Gross Preview</span><strong id="second_gross">0.00</strong></div><div class="d-flex justify-content-between"><span>Total Deductions</span><strong id="second_deductions">0.00</strong></div><hr><div class="d-flex justify-content-between fs-6"><span>Estimated Net</span><strong id="second_net">0.00</strong></div></div></div></div>

<div class="col-12"><hr></div>

<div class="col-md-12">
    <label class="form-label">Remarks</label>
    <textarea name="remarks" rows="3" class="form-control {{ $errors->has('remarks') ? 'is-invalid' : '' }}">{{ $value('remarks') }}</textarea>
    <div class="invalid-feedback">{{ $errors->first('remarks') }}</div>
</div>

<div class="col-md-12">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked((bool) $value('is_active', 1))>
        <label class="form-check-label" for="is_active">Active</label>
    </div>
</div>

<style>
    #employeeDropdown .list-group-item { border-left: 0; border-right: 0; }
    #employeeDropdown .employee-option:hover,
    #employeeDropdown .employee-option.active { background-color: rgba(44, 123, 229, 0.08); }
</style>

@include('payroll.employee_salaries._salary_preview_script')
