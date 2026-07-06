@php
    $salary = $salary ?? null;
    $isEdit = filled($salary);

    $scheduleOptions = [
        'none' => 'No Deduction / Not Applicable',
        'first_cutoff' => '1st Cutoff Only',
        'second_cutoff' => '2nd Cutoff Only',
        'every_cutoff' => 'Every Cutoff',
    ];

    $value = function (string $field, mixed $default = null) use ($salary) {
        return old($field, $salary->{$field} ?? $default);
    };

    $dateValue = function (string $field) use ($salary) {
        $old = old($field);

        if ($old !== null) {
            return $old;
        }

        $date = $salary->{$field} ?? null;

        return $date ? optional($date)->format('Y-m-d') : null;
    };

    $loanItems = [
        [
            'label' => 'SSS Loan',
            'prefix' => 'sss_loan',
            'help' => 'SSS salary loan or other SSS loan deduction.',
        ],
        [
            'label' => 'Pag-IBIG Loan',
            'prefix' => 'pagibig_loan',
            'help' => 'Pag-IBIG MPL, calamity loan, or other Pag-IBIG deduction.',
        ],
        [
            'label' => 'PhilHealth Loan',
            'prefix' => 'philhealth_loan',
            'help' => 'Use only if your company tracks PhilHealth-related employee deductions.',
        ],
        [
            'label' => 'Cash Advance / Vale',
            'prefix' => 'cash_advance',
            'help' => 'Cash advance deduction from payroll.',
        ],
        [
            'label' => 'Other Loan',
            'prefix' => 'other_loan',
            'help' => 'Company loan or other employee deduction.',
        ],
    ];
@endphp

@php
    $existingOtherDeductions = collect(
        old(
            'other_deductions',
            $salary?->otherDeductions
                ?->map(function ($deduction) {
                    return [
                        'name' => $deduction->name,
                        'total_amount' => $deduction->total_amount,
                        'payment_amount' => $deduction->payment_amount,
                        'deduction_schedule' => $deduction->deduction_schedule,
                        'start_date' => optional($deduction->start_date)->format('Y-m-d'),
                        'remarks' => $deduction->remarks,
                    ];
                })
                ->toArray() ?? [],
        ),
    )->values();
    $resolvePerson = function ($person) {
        $canonicalId = $person->employee_biometric_id ?? $person->id ?? null;

        $legacyId = $person->biometric_employee_id
            ?? $person->legacy_biometric_employee_id
            ?? $person->source_employee_id
            ?? $person->source_crosschex_id
            ?? $person->source_employee_no
            ?? null;

        $employeeNo = $person->employee_no
            ?? $person->effective_employee_no
            ?? $person->display_employee_no
            ?? $person->source_employee_no
            ?? $person->source_employee_id
            ?? null;

        $employeeName = $person->employee_name
            ?? $person->effective_name
            ?? $person->display_name
            ?? $person->source_employee_name
            ?? $person->source_crosschex_account_name
            ?? 'Unknown Employee';

        $crosschexId = $person->crosschex_id
            ?? $person->source_crosschex_id
            ?? null;

        return [
            'canonical_id' => $canonicalId,
            'legacy_id' => $legacyId,
            'employee_no' => $employeeNo,
            'employee_name' => $employeeName,
            'crosschex_id' => $crosschexId,
        ];
    };
@endphp

<div class="row g-3">
    @if (!$isEdit)
        <div class="col-12">
            <div class="card border-0 bg-body-tertiary">
                <div class="card-body">
                    <label class="form-label fw-semibold">Select Employee from Biometrics</label>
                    <div class="position-relative">
                        <input type="text" id="employeePicker" class="form-control"
                            placeholder="Search employee name / employee no / CrossChex ID" autocomplete="off">

                        <div id="employeeDropdown" class="card shadow-sm border mt-1 d-none position-absolute w-100"
                            style="z-index: 1050; max-height: 280px; overflow-y: auto;">
                            <div class="list-group list-group-flush">
                                @foreach ($people as $person)
                                    @php($resolvedPerson = $resolvePerson($person))

                                    @continue(empty($resolvedPerson['canonical_id']))

                                    <button type="button"
                                        class="list-group-item list-group-item-action employee-option"
                                        data-employee-biometric-id="{{ $resolvedPerson['canonical_id'] }}"
                                        data-biometric="{{ $resolvedPerson['legacy_id'] }}"
                                        data-empno="{{ $resolvedPerson['employee_no'] }}"
                                        data-name="{{ $resolvedPerson['employee_name'] }}"
                                        data-crosschex="{{ $resolvedPerson['crosschex_id'] }}"
                                        data-search="{{ strtolower(trim(($resolvedPerson['canonical_id'] ?? '') . ' ' . ($resolvedPerson['employee_name'] ?? '') . ' ' . ($resolvedPerson['employee_no'] ?? '') . ' ' . ($resolvedPerson['legacy_id'] ?? '') . ' ' . ($resolvedPerson['crosschex_id'] ?? ''))) }}">
                                        <div class="fw-semibold text-dark">{{ $resolvedPerson['employee_name'] }}</div>
                                        <div class="small text-muted">
                                            Bio ID: {{ $resolvedPerson['canonical_id'] }}
                                            |
                                            {{ $resolvedPerson['employee_no'] ?: 'No Employee No' }}
                                            @if ($resolvedPerson['legacy_id'])
                                                | Legacy: {{ $resolvedPerson['legacy_id'] }}
                                            @endif
                                            @if ($resolvedPerson['crosschex_id'])
                                                | CrossChex: {{ $resolvedPerson['crosschex_id'] }}
                                            @endif
                                        </div>
                                    </button>
                                @endforeach

                                <div id="employeeNoResult" class="list-group-item text-muted d-none">
                                    No employee found.
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="employee_biometric_id" id="employee_biometric_id"
                        value="{{ old('employee_biometric_id') }}">

                    <input type="hidden" name="biometric_employee_id" id="biometric_employee_id"
                        value="{{ old('biometric_employee_id') }}">

                    <input type="hidden" name="crosschex_id" id="crosschex_id" value="{{ old('crosschex_id') }}">

                    @error('employee_biometric_id')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror

                    @error('biometric_employee_id')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    @else
        <div class="col-md-4">
            <label class="form-label">Canonical Bio ID</label>
            <input type="text" class="form-control" value="{{ $salary->employee_biometric_id }}" readonly>
            <input type="hidden" name="employee_biometric_id" id="employee_biometric_id" value="{{ $salary->employee_biometric_id }}">
            <div class="form-text">employee_biometric_id → employee_biometrics.id</div>
        </div>

        <div class="col-md-4">
            <label class="form-label">Legacy Biometric ID</label>
            <input type="text" class="form-control" value="{{ $salary->biometric_employee_id }}" readonly>
            <input type="hidden" name="biometric_employee_id" id="biometric_employee_id" value="{{ $salary->biometric_employee_id }}">
        </div>

        <div class="col-md-4">
            <label class="form-label">CrossChex ID</label>
            <input type="text" name="crosschex_id" class="form-control @error('crosschex_id') is-invalid @enderror"
                value="{{ $value('crosschex_id') }}" readonly>
            @error('crosschex_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    @endif

    <div class="col-md-4">
        <label class="form-label">Employee No</label>
        <input type="text" name="employee_no" id="employee_no"
            class="form-control @error('employee_no') is-invalid @enderror" value="{{ $value('employee_no') }}"
            readonly>
        @error('employee_no')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-{{ $isEdit ? '4' : '8' }}">
        <label class="form-label">Employee Name</label>
        <input type="text" name="employee_name" id="employee_name"
            class="form-control @error('employee_name') is-invalid @enderror" value="{{ $value('employee_name') }}"
            required readonly>
        @error('employee_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <hr>
        <h6 class="text-800 mb-1">
            <span class="fas fa-calculator text-primary me-2"></span>
            Rate Computation
        </h6>
        <p class="text-muted small mb-0">
            The system will compute OT per hour, late per minute, undertime per minute, and absent per day.
        </p>
    </div>

    <div class="col-md-3">
        <label class="form-label">Rate Type</label>
        <select name="rate_type" id="rate_type" class="form-select @error('rate_type') is-invalid @enderror">
            <option value="daily" @selected($value('rate_type', 'daily') === 'daily')>Daily</option>
            <option value="monthly" @selected($value('rate_type', 'daily') === 'monthly')>Monthly</option>
        </select>
        @error('rate_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Basic Salary</label>
        <input type="number" step="0.01" name="basic_salary" id="basic_salary"
            class="form-control @error('basic_salary') is-invalid @enderror" value="{{ $value('basic_salary', 0) }}"
            required>
        @error('basic_salary')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">OT Rate / Hour</label>
        <input type="number" step="0.01" name="ot_rate_per_hour" id="ot_rate_per_hour" class="form-control"
            value="{{ $value('ot_rate_per_hour', 0) }}" readonly>
    </div>

    <div class="col-md-3">
        <label class="form-label">Absent / Day</label>
        <input type="number" step="0.01" name="absent_deduction_per_day" id="absent_deduction_per_day"
            class="form-control" value="{{ $value('absent_deduction_per_day', 0) }}" readonly>
    </div>

    <div class="col-md-3">
        <label class="form-label">Late Deduction / Minute</label>
        <input type="number" step="0.0001" name="late_deduction_per_minute" id="late_deduction_per_minute"
            class="form-control" value="{{ $value('late_deduction_per_minute', 0) }}" readonly>
    </div>

    <div class="col-md-3">
        <label class="form-label">Undertime Deduction / Minute</label>
        <input type="number" step="0.0001" name="undertime_deduction_per_minute"
            id="undertime_deduction_per_minute" class="form-control"
            value="{{ $value('undertime_deduction_per_minute', 0) }}" readonly>
    </div>

    <div class="col-12">
        <hr>
        <h6 class="text-800 mb-1">
            <span class="fas fa-shield-alt text-success me-2"></span>
            Government Contributions
        </h6>
        <p class="text-muted small mb-0">
            Choose when SSS, Pag-IBIG, and PhilHealth will be deducted.
        </p>
    </div>

    <div class="col-md-4">
        <label class="form-label">SSS Deduction Schedule</label>
        <select name="sss_contribution_cutoff" id="sss_contribution_cutoff"
            class="form-select payroll-preview-input">
            @foreach ($scheduleOptions as $key => $label)
                <option value="{{ $key }}" @selected($value('sss_contribution_cutoff', 'first_cutoff') === $key)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Pag-IBIG Deduction Schedule</label>
        <select name="pagibig_contribution_cutoff" id="pagibig_contribution_cutoff"
            class="form-select payroll-preview-input">
            @foreach ($scheduleOptions as $key => $label)
                <option value="{{ $key }}" @selected($value('pagibig_contribution_cutoff', 'second_cutoff') === $key)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">PhilHealth Deduction Schedule</label>
        <select name="philhealth_contribution_cutoff" id="philhealth_contribution_cutoff"
            class="form-select payroll-preview-input">
            @foreach ($scheduleOptions as $key => $label)
                <option value="{{ $key }}" @selected($value('philhealth_contribution_cutoff', 'second_cutoff') === $key)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-12">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="border rounded-3 p-3 h-100">
                    <div class="text-muted small">Monthly SSS Employee Share</div>
                    <div class="fs-5 fw-bold" id="preview_monthly_sss">0.00</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded-3 p-3 h-100">
                    <div class="text-muted small">Monthly Pag-IBIG Employee Share</div>
                    <div class="fs-5 fw-bold" id="preview_monthly_pagibig">0.00</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded-3 p-3 h-100">
                    <div class="text-muted small">Monthly PhilHealth Employee Share</div>
                    <div class="fs-5 fw-bold" id="preview_monthly_philhealth">0.00</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <hr>
        <h6 class="text-800 mb-1">
            <span class="fas fa-gift text-warning me-2"></span>
            Allowances
        </h6>
        <p class="text-muted small mb-0">
            Allowance can be half-half every cutoff or whole paid on selected cutoff.
        </p>
    </div>

    <div class="col-md-3">
        <label class="form-label">Regular Allowance</label>
        <input type="number" step="0.01" name="allowance" id="allowance"
            class="form-control payroll-preview-input @error('allowance') is-invalid @enderror"
            value="{{ $value('allowance', 0) }}">
        @error('allowance')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Allowance Release</label>
        <select name="allowance_release_schedule" id="allowance_release_schedule"
            class="form-select payroll-preview-input">
            @foreach ($scheduleOptions as $key => $label)
                <option value="{{ $key }}" @selected($value('allowance_release_schedule', 'every_cutoff') === $key)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">SIM / Cellular Load Allowance</label>
        <input type="number" step="0.01" name="sim_load_allowance" id="sim_load_allowance"
            class="form-control payroll-preview-input @error('sim_load_allowance') is-invalid @enderror"
            value="{{ $value('sim_load_allowance', 0) }}">
        @error('sim_load_allowance')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">SIM Load Release</label>
        <select name="sim_load_release_schedule" id="sim_load_release_schedule"
            class="form-select payroll-preview-input">
            @foreach ($scheduleOptions as $key => $label)
                <option value="{{ $key }}" @selected($value('sim_load_release_schedule', 'every_cutoff') === $key)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-12">
        <hr>
        <h6 class="text-800 mb-1">
            <span class="fas fa-hand-holding-usd text-danger me-2"></span>
            Loans and Cash Advance
        </h6>
        <p class="text-muted small mb-0">
            Input total loan or CA, deduction amount per selected cutoff, schedule, and start date.
        </p>
    </div>

    <div class="col-12">
        <div class="card border shadow-sm">
            <div class="card-header bg-body-tertiary d-flex flex-column flex-md-row justify-content-between gap-2">
                <div>
                    <h6 class="mb-1">
                        <span class="fas fa-plus-circle text-primary me-2"></span>
                        Additional Other Loans / Deductions
                    </h6>
                    <p class="mb-0 text-muted small">
                        Add extra deductions anytime, such as company loan, uniform deduction, damage charge,
                        cooperative loan, or other payroll deduction.
                    </p>
                </div>

                <button type="button" class="btn btn-primary btn-sm align-self-start" id="addOtherDeductionBtn">
                    <span class="fas fa-plus me-1"></span>
                    Add Deduction
                </button>
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
                                    <td>
                                        <input type="text" name="other_deductions[{{ $index }}][name]"
                                            class="form-control form-control-sm other-deduction-input payroll-preview-input"
                                            placeholder="e.g. Uniform Deduction"
                                            value="{{ $deduction['name'] ?? '' }}">
                                    </td>

                                    <td>
                                        <input type="number" step="0.01"
                                            name="other_deductions[{{ $index }}][total_amount]"
                                            class="form-control form-control-sm other-deduction-total other-deduction-input payroll-preview-input"
                                            value="{{ $deduction['total_amount'] ?? 0 }}">
                                    </td>

                                    <td>
                                        <input type="number" step="0.01"
                                            name="other_deductions[{{ $index }}][payment_amount]"
                                            class="form-control form-control-sm other-deduction-payment other-deduction-input payroll-preview-input"
                                            value="{{ $deduction['payment_amount'] ?? 0 }}">
                                    </td>

                                    <td>
                                        <select name="other_deductions[{{ $index }}][deduction_schedule]"
                                            class="form-select form-select-sm other-deduction-schedule other-deduction-input payroll-preview-input">
                                            @foreach ($scheduleOptions as $key => $label)
                                                <option value="{{ $key }}" @selected(($deduction['deduction_schedule'] ?? 'none') === $key)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <input type="date"
                                            name="other_deductions[{{ $index }}][start_date]"
                                            class="form-control form-control-sm other-deduction-start other-deduction-input payroll-preview-input"
                                            value="{{ $deduction['start_date'] ?? '' }}">
                                    </td>

                                    <td>
                                        <span class="badge bg-subtle-primary text-primary border other-deduction-last">
                                            —
                                        </span>
                                    </td>

                                    <td>
                                        <input type="text" name="other_deductions[{{ $index }}][remarks]"
                                            class="form-control form-control-sm other-deduction-input"
                                            placeholder="Optional" value="{{ $deduction['remarks'] ?? '' }}">
                                    </td>

                                    <td class="text-center">
                                        <button type="button"
                                            class="btn btn-sm btn-danger p-1 remove-other-deduction" title="Remove">
                                            <span class="fas fa-trash-alt"></span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div id="otherDeductionsEmptyState"
                    class="text-center text-muted small py-3 {{ $existingOtherDeductions->isNotEmpty() ? 'd-none' : '' }}">
                    No additional other deduction added yet.
                </div>
            </div>
        </div>
    </div>

    <template id="otherDeductionTemplate">
        <tr class="other-deduction-row">
            <td>
                <input type="text" name="other_deductions[__INDEX__][name]"
                    class="form-control form-control-sm other-deduction-input payroll-preview-input"
                    placeholder="e.g. Uniform Deduction">
            </td>

            <td>
                <input type="number" step="0.01" name="other_deductions[__INDEX__][total_amount]"
                    class="form-control form-control-sm other-deduction-total other-deduction-input payroll-preview-input"
                    value="0">
            </td>

            <td>
                <input type="number" step="0.01" name="other_deductions[__INDEX__][payment_amount]"
                    class="form-control form-control-sm other-deduction-payment other-deduction-input payroll-preview-input"
                    value="0">
            </td>

            <td>
                <select name="other_deductions[__INDEX__][deduction_schedule]"
                    class="form-select form-select-sm other-deduction-schedule other-deduction-input payroll-preview-input">
                    @foreach ($scheduleOptions as $key => $label)
                        <option value="{{ $key }}" @selected($key === 'none')>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </td>

            <td>
                <input type="date" name="other_deductions[__INDEX__][start_date]"
                    class="form-control form-control-sm other-deduction-start other-deduction-input payroll-preview-input">
            </td>

            <td>
                <span class="badge bg-subtle-primary text-primary border other-deduction-last">
                    —
                </span>
            </td>

            <td>
                <input type="text" name="other_deductions[__INDEX__][remarks]"
                    class="form-control form-control-sm other-deduction-input" placeholder="Optional">
            </td>

            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger p-1 remove-other-deduction" title="Remove">
                    <span class="fas fa-trash-alt"></span>
                </button>
            </td>
        </tr>
    </template>
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
                        @php
                            $prefix = $item['prefix'];
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $item['label'] }}</div>
                                <div class="text-muted small">{{ $item['help'] }}</div>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="{{ $prefix }}_total_amount"
                                    id="{{ $prefix }}_total_amount"
                                    class="form-control form-control-sm payroll-preview-input"
                                    value="{{ $value($prefix . '_total_amount', 0) }}">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="{{ $prefix }}_payment_amount"
                                    id="{{ $prefix }}_payment_amount"
                                    class="form-control form-control-sm payroll-preview-input"
                                    value="{{ $value($prefix . '_payment_amount', 0) }}">
                            </td>
                            <td>
                                <select name="{{ $prefix }}_deduction_schedule"
                                    id="{{ $prefix }}_deduction_schedule"
                                    class="form-select form-select-sm payroll-preview-input">
                                    @foreach ($scheduleOptions as $key => $label)
                                        <option value="{{ $key }}" @selected($value($prefix . '_deduction_schedule', 'none') === $key)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="date" name="{{ $prefix }}_start_date"
                                    id="{{ $prefix }}_start_date"
                                    class="form-control form-control-sm payroll-preview-input"
                                    value="{{ $dateValue($prefix . '_start_date') }}">
                            </td>
                            <td>
                                <span class="badge bg-subtle-primary text-primary border"
                                    id="{{ $prefix }}_last_payment">
                                    —
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-12">
        <hr>
        <h6 class="text-800 mb-1">
            <span class="fas fa-receipt text-info me-2"></span>
            Live Payroll Preview
        </h6>
        <p class="text-muted small mb-0">
            This preview is based on salary master only. Actual payroll can still adjust using attendance, late,
            undertime, OT, absence, and holiday rules.
        </p>
    </div>

    <div class="col-md-4">
        <div class="card border-0 bg-body-tertiary h-100">
            <div class="card-body">
                <div class="text-muted small">Monthly Basic Salary Equivalent</div>
                <div class="fs-4 fw-bold" id="preview_monthly_basic">0.00</div>
                <div class="small text-muted">
                    Daily employees use basic salary × 22 days.
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-primary h-100">
            <div class="card-header py-2 bg-primary text-white">
                1st Cutoff Preview
            </div>
            <div class="card-body small">
                <div class="d-flex justify-content-between">
                    <span>Gross Preview</span>
                    <strong id="first_gross">0.00</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Total Deductions</span>
                    <strong id="first_deductions">0.00</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between fs-6">
                    <span>Estimated Net</span>
                    <strong id="first_net">0.00</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-info h-100">
            <div class="card-header py-2 bg-info text-white">
                2nd Cutoff Preview
            </div>
            <div class="card-body small">
                <div class="d-flex justify-content-between">
                    <span>Gross Preview</span>
                    <strong id="second_gross">0.00</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Total Deductions</span>
                    <strong id="second_deductions">0.00</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between fs-6">
                    <span>Estimated Net</span>
                    <strong id="second_net">0.00</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <hr>
    </div>

    <div class="col-md-12">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror">{{ $value('remarks') }}</textarea>
        @error('remarks')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                @checked($value('is_active', 1))>
            <label class="form-check-label" for="is_active">
                Active
            </label>
        </div>
    </div>
</div>

<style>
    #employeeDropdown .list-group-item {
        border-left: 0;
        border-right: 0;
    }

    #employeeDropdown .employee-option:hover,
    #employeeDropdown .employee-option.active {
        background-color: rgba(44, 123, 229, 0.08);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const workingDaysPerMonth = 22;
        const hoursPerDay = 8;
        const minutesPerHour = 60;

        const loanPrefixes = [
            'sss_loan',
            'pagibig_loan',
            'philhealth_loan',
            'cash_advance',
            'other_loan'
        ];

        function input(id) {
            return document.getElementById(id);
        }

        function numberValue(id) {
            const el = input(id);

            if (!el) {
                return 0;
            }

            return parseFloat(el.value) || 0;
        }

        function stringValue(id, fallback = 'none') {
            const el = input(id);

            if (!el) {
                return fallback;
            }

            return el.value || fallback;
        }

        function money(value) {
            return Number(value || 0).toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function setText(id, value) {
            const el = input(id);

            if (el) {
                el.textContent = value;
            }
        }

        function setInputValue(id, value, decimals = 2) {
            const el = input(id);

            if (el) {
                el.value = Number(value || 0).toFixed(decimals);
            }
        }

        function monthlyBasicSalary() {
            const rateType = stringValue('rate_type', 'daily');
            const basicSalary = numberValue('basic_salary');

            if (basicSalary <= 0) {
                return 0;
            }

            return rateType === 'monthly' ?
                basicSalary :
                basicSalary * workingDaysPerMonth;
        }

        function computeSalaryRates() {
            const rateType = stringValue('rate_type', 'daily');
            const basicSalary = numberValue('basic_salary');

            let dailyRate = 0;

            if (basicSalary > 0) {
                dailyRate = rateType === 'monthly' ?
                    basicSalary / workingDaysPerMonth :
                    basicSalary;
            }

            const hourlyRate = dailyRate / hoursPerDay;
            const perMinuteRate = hourlyRate / minutesPerHour;

            setInputValue('ot_rate_per_hour', hourlyRate, 2);
            setInputValue('late_deduction_per_minute', perMinuteRate, 4);
            setInputValue('undertime_deduction_per_minute', perMinuteRate, 4);
            setInputValue('absent_deduction_per_day', dailyRate, 2);
        }

        function sssMonthlySalaryCredit(monthlySalary) {
            if (monthlySalary <= 0) {
                return 0;
            }

            if (monthlySalary < 5250) {
                return 5000;
            }

            if (monthlySalary >= 34750) {
                return 35000;
            }

            return Math.round(monthlySalary / 500) * 500;
        }

        function sssEmployeeShare(monthlySalary) {
            return sssMonthlySalaryCredit(monthlySalary) * 0.05;
        }

        function pagibigEmployeeShare(monthlySalary) {
            if (monthlySalary <= 0) {
                return 0;
            }

            const baseSalary = Math.min(monthlySalary, 10000);
            const rate = baseSalary <= 1500 ? 0.01 : 0.02;

            return baseSalary * rate;
        }

        function philhealthEmployeeShare(monthlySalary) {
            if (monthlySalary <= 0) {
                return 0;
            }

            const baseSalary = Math.min(Math.max(monthlySalary, 10000), 100000);
            const totalPremium = baseSalary * 0.05;

            return totalPremium / 2;
        }

        function monthlyToCutoff(monthlyAmount, schedule, cutoff) {
            if (monthlyAmount <= 0 || schedule === 'none') {
                return 0;
            }

            if (schedule === 'every_cutoff') {
                return monthlyAmount / 2;
            }

            if (schedule === 'first_cutoff' && cutoff === 'first') {
                return monthlyAmount;
            }

            if (schedule === 'second_cutoff' && cutoff === 'second') {
                return monthlyAmount;
            }

            return 0;
        }

        function fixedDeductionToCutoff(paymentAmount, schedule, cutoff) {
            if (paymentAmount <= 0 || schedule === 'none') {
                return 0;
            }

            if (schedule === 'every_cutoff') {
                return paymentAmount;
            }

            if (schedule === 'first_cutoff' && cutoff === 'first') {
                return paymentAmount;
            }

            if (schedule === 'second_cutoff' && cutoff === 'second') {
                return paymentAmount;
            }

            return 0;
        }

        function nextCutoffDate(afterDate, schedule) {
            const allowedDays = schedule === 'first_cutoff' ? [25] :
                schedule === 'second_cutoff' ? [11] : [11, 25];

            const candidates = [];

            for (let monthOffset = 0; monthOffset <= 24; monthOffset++) {
                allowedDays.forEach(function(day) {
                    const candidate = new Date(afterDate.getFullYear(), afterDate.getMonth() +
                        monthOffset, day);

                    if (candidate > afterDate) {
                        candidates.push(candidate);
                    }
                });
            }

            candidates.sort(function(a, b) {
                return a - b;
            });

            return candidates[0] || afterDate;
        }

        function estimatedLastPaymentDate(prefix) {
            const totalAmount = numberValue(`${prefix}_total_amount`);
            const paymentAmount = numberValue(`${prefix}_payment_amount`);
            const schedule = stringValue(`${prefix}_deduction_schedule`, 'none');
            const startDateValue = stringValue(`${prefix}_start_date`, '');

            if (totalAmount <= 0 || paymentAmount <= 0 || schedule === 'none') {
                return '—';
            }

            const paymentCount = Math.ceil(totalAmount / paymentAmount);
            let cursor = startDateValue ?
                new Date(startDateValue + 'T00:00:00') :
                new Date();

            cursor.setDate(cursor.getDate() - 1);

            for (let i = 0; i < paymentCount; i++) {
                cursor = nextCutoffDate(cursor, schedule);
            }

            return cursor.toLocaleDateString('en-PH', {
                year: 'numeric',
                month: 'short',
                day: '2-digit'
            });
        }

        function loanDeductionTotal(cutoff) {
            let total = 0;

            loanPrefixes.forEach(function(prefix) {
                total += fixedDeductionToCutoff(
                    numberValue(`${prefix}_payment_amount`),
                    stringValue(`${prefix}_deduction_schedule`, 'none'),
                    cutoff
                );

                setText(`${prefix}_last_payment`, estimatedLastPaymentDate(prefix));
            });

            total += otherDeductionsTotal(cutoff);

            return total;
        }

        function rowNumberValue(row, selector) {
            const el = row.querySelector(selector);

            if (!el) {
                return 0;
            }

            return parseFloat(el.value) || 0;
        }

        function rowStringValue(row, selector, fallback = 'none') {
            const el = row.querySelector(selector);

            if (!el) {
                return fallback;
            }

            return el.value || fallback;
        }

        function estimatedLastPaymentDateByValues(totalAmount, paymentAmount, schedule, startDateValue) {
            if (totalAmount <= 0 || paymentAmount <= 0 || schedule === 'none') {
                return '—';
            }

            const paymentCount = Math.ceil(totalAmount / paymentAmount);
            let cursor = startDateValue ?
                new Date(startDateValue + 'T00:00:00') :
                new Date();

            cursor.setDate(cursor.getDate() - 1);

            for (let i = 0; i < paymentCount; i++) {
                cursor = nextCutoffDate(cursor, schedule);
            }

            return cursor.toLocaleDateString('en-PH', {
                year: 'numeric',
                month: 'short',
                day: '2-digit'
            });
        }

        function otherDeductionsTotal(cutoff) {
            let total = 0;

            document.querySelectorAll('.other-deduction-row').forEach(function(row) {
                const totalAmount = rowNumberValue(row, '.other-deduction-total');
                const paymentAmount = rowNumberValue(row, '.other-deduction-payment');
                const schedule = rowStringValue(row, '.other-deduction-schedule', 'none');
                const startDate = rowStringValue(row, '.other-deduction-start', '');

                total += fixedDeductionToCutoff(paymentAmount, schedule, cutoff);

                const lastPayment = row.querySelector('.other-deduction-last');

                if (lastPayment) {
                    lastPayment.textContent = estimatedLastPaymentDateByValues(
                        totalAmount,
                        paymentAmount,
                        schedule,
                        startDate
                    );
                }
            });

            return total;
        }

        function refreshOtherDeductionsEmptyState() {
            const emptyState = input('otherDeductionsEmptyState');
            const rows = document.querySelectorAll('.other-deduction-row');

            if (emptyState) {
                emptyState.classList.toggle('d-none', rows.length > 0);
            }
        }

        function bindOtherDeductionRow(row) {
            row.querySelectorAll('.other-deduction-input').forEach(function(element) {
                element.addEventListener('input', computePreview);
                element.addEventListener('change', computePreview);
            });

            const removeButton = row.querySelector('.remove-other-deduction');

            if (removeButton) {
                removeButton.addEventListener('click', function() {
                    row.remove();
                    refreshOtherDeductionsEmptyState();
                    computePreview();
                });
            }
        }

        function addOtherDeductionRow() {
            const body = input('otherDeductionsBody');
            const template = input('otherDeductionTemplate');

            if (!body || !template) {
                return;
            }

            const index = Date.now();
            const html = template.innerHTML.replaceAll('__INDEX__', index);

            body.insertAdjacentHTML('beforeend', html);

            const row = body.querySelector('.other-deduction-row:last-child');

            if (row) {
                bindOtherDeductionRow(row);
            }

            refreshOtherDeductionsEmptyState();
            computePreview();
        }

        function setupOtherDeductions() {
            const addButton = input('addOtherDeductionBtn');

            if (addButton) {
                addButton.addEventListener('click', addOtherDeductionRow);
            }

            document.querySelectorAll('.other-deduction-row').forEach(function(row) {
                bindOtherDeductionRow(row);
            });

            refreshOtherDeductionsEmptyState();
        }

        function computePreview() {
            computeSalaryRates();

            const monthlyBasic = monthlyBasicSalary();

            const monthlySss = sssEmployeeShare(monthlyBasic);
            const monthlyPagibig = pagibigEmployeeShare(monthlyBasic);
            const monthlyPhilhealth = philhealthEmployeeShare(monthlyBasic);

            const firstGovernment =
                monthlyToCutoff(monthlySss, stringValue('sss_contribution_cutoff'), 'first') +
                monthlyToCutoff(monthlyPagibig, stringValue('pagibig_contribution_cutoff'), 'first') +
                monthlyToCutoff(monthlyPhilhealth, stringValue('philhealth_contribution_cutoff'), 'first');

            const secondGovernment =
                monthlyToCutoff(monthlySss, stringValue('sss_contribution_cutoff'), 'second') +
                monthlyToCutoff(monthlyPagibig, stringValue('pagibig_contribution_cutoff'), 'second') +
                monthlyToCutoff(monthlyPhilhealth, stringValue('philhealth_contribution_cutoff'), 'second');

            const firstAllowance =
                monthlyToCutoff(numberValue('allowance'), stringValue('allowance_release_schedule'), 'first') +
                monthlyToCutoff(numberValue('sim_load_allowance'), stringValue('sim_load_release_schedule'),
                    'first');

            const secondAllowance =
                monthlyToCutoff(numberValue('allowance'), stringValue('allowance_release_schedule'), 'second') +
                monthlyToCutoff(numberValue('sim_load_allowance'), stringValue('sim_load_release_schedule'),
                    'second');

            const firstLoans = loanDeductionTotal('first');
            const secondLoans = loanDeductionTotal('second');

            const firstGross = (monthlyBasic / 2) + firstAllowance;
            const secondGross = (monthlyBasic / 2) + secondAllowance;

            const firstDeductions = firstGovernment + firstLoans;
            const secondDeductions = secondGovernment + secondLoans;

            setText('preview_monthly_basic', money(monthlyBasic));
            setText('preview_monthly_sss', money(monthlySss));
            setText('preview_monthly_pagibig', money(monthlyPagibig));
            setText('preview_monthly_philhealth', money(monthlyPhilhealth));

            setText('first_gross', money(firstGross));
            setText('first_deductions', money(firstDeductions));
            setText('first_net', money(firstGross - firstDeductions));

            setText('second_gross', money(secondGross));
            setText('second_deductions', money(secondDeductions));
            setText('second_net', money(secondGross - secondDeductions));
        }

        function setupEmployeePicker() {
            const picker = input('employeePicker');
            const dropdown = input('employeeDropdown');

            if (!picker || !dropdown) {
                return;
            }

            const wrapper = picker.closest('.position-relative');
            const options = Array.from(document.querySelectorAll('.employee-option'));
            const noResult = input('employeeNoResult');

            const employeeBiometricInput = input('employee_biometric_id');
            const biometricInput = input('biometric_employee_id');
            const employeeNoInput = input('employee_no');
            const employeeNameInput = input('employee_name');
            const crosschexInput = input('crosschex_id');

            function showDropdown() {
                dropdown.classList.remove('d-none');
            }

            function hideDropdown() {
                dropdown.classList.add('d-none');
            }

            function clearSelectedFields() {
                if (employeeBiometricInput) {
                    employeeBiometricInput.value = '';
                }

                biometricInput.value = '';
                employeeNoInput.value = '';
                employeeNameInput.value = '';
                crosschexInput.value = '';
            }

            function filterOptions() {
                const keyword = picker.value.trim().toLowerCase();
                let visibleCount = 0;

                options.forEach(function(option) {
                    const haystack = option.dataset.search || '';
                    const matched = keyword === '' || haystack.includes(keyword);

                    option.classList.toggle('d-none', !matched);

                    if (matched) {
                        visibleCount++;
                    }
                });

                if (noResult) {
                    noResult.classList.toggle('d-none', visibleCount !== 0);
                }
            }

            options.forEach(function(option) {
                option.addEventListener('click', function() {
                    const name = option.dataset.name || '';
                    const empno = option.dataset.empno || '';

                    picker.value = name + (empno ? ' | ' + empno : '');
                    if (employeeBiometricInput) {
                        employeeBiometricInput.value = option.dataset.employeeBiometricId || '';
                    }

                    biometricInput.value = option.dataset.biometric || '';
                    employeeNoInput.value = empno;
                    employeeNameInput.value = name;
                    crosschexInput.value = option.dataset.crosschex || '';

                    hideDropdown();
                });
            });

            picker.addEventListener('focus', function() {
                filterOptions();
                showDropdown();
            });

            picker.addEventListener('input', function() {
                clearSelectedFields();
                filterOptions();
                showDropdown();
            });

            document.addEventListener('click', function(event) {
                if (!wrapper.contains(event.target)) {
                    hideDropdown();
                }
            });
        }

        document.querySelectorAll('.payroll-preview-input, #rate_type, #basic_salary').forEach(function(
            element) {
            element.addEventListener('input', computePreview);
            element.addEventListener('change', computePreview);
        });

        setupEmployeePicker();
        setupOtherDeductions();
        computePreview();
    });
</script>
