@extends('layouts.app')
@section('title', 'Add Employee Salary')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-body-tertiary">
                    <h5 class="mb-0">Add Employee Salary</h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('payroll-employee-salaries.store') }}" method="POST">
                        @csrf

                        <div class="mb-3 position-relative">
                            <label class="form-label">Select Employee from Biometrics</label>
                            <input type="text" id="employeePicker" class="form-control"
                                placeholder="Search employee name or employee no" autocomplete="off">

                            <div id="employeeDropdown" class="card shadow-sm border mt-1 d-none position-absolute w-100"
                                style="z-index: 1050; max-height: 260px; overflow-y: auto;">
                                <div class="list-group list-group-flush">
                                    @foreach ($people as $person)
                                        <button type="button"
                                            class="list-group-item list-group-item-action employee-option"
                                            data-biometric="{{ $person->biometric_employee_id }}"
                                            data-empno="{{ $person->employee_no }}" data-name="{{ $person->employee_name }}"
                                            data-crosschex="{{ $person->crosschex_id }}"
                                            data-search="{{ strtolower(trim(($person->employee_name ?? '') . ' ' . ($person->employee_no ?? '') . ' ' . ($person->crosschex_id ?? ''))) }}">
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $person->employee_name }}</div>
                                                <div class="small text-muted">
                                                    {{ $person->employee_no ?: 'No Employee No' }}
                                                    @if ($person->crosschex_id)
                                                        | Crosschex: {{ $person->crosschex_id }}
                                                    @endif
                                                </div>
                                            </div>
                                        </button>
                                    @endforeach

                                    <div id="employeeNoResult" class="list-group-item text-muted d-none">
                                        No employee found.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="biometric_employee_id" id="biometric_employee_id"
                            value="{{ old('biometric_employee_id') }}">
                        <input type="hidden" name="crosschex_id" id="crosschex_id" value="{{ old('crosschex_id') }}">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Employee No</label>
                                <input type="text" name="employee_no" id="employee_no"
                                    class="form-control @error('employee_no') is-invalid @enderror"
                                    value="{{ old('employee_no') }}">
                                @error('employee_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Employee Name</label>
                                <input type="text" name="employee_name" id="employee_name"
                                    class="form-control @error('employee_name') is-invalid @enderror"
                                    value="{{ old('employee_name') }}" required>
                                @error('employee_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Rate Type</label>
                                <select name="rate_type" id="rate_type"
                                    class="form-select @error('rate_type') is-invalid @enderror">
                                    <option value="daily" {{ old('rate_type') == 'daily' ? 'selected' : '' }}>Daily
                                    </option>
                                    <option value="monthly" {{ old('rate_type') == 'monthly' ? 'selected' : '' }}>Monthly
                                    </option>
                                </select>
                                @error('rate_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Basic Salary</label>
                                <input type="number" step="0.01" name="basic_salary" id="basic_salary"
                                    class="form-control @error('basic_salary') is-invalid @enderror"
                                    value="{{ old('basic_salary', 0) }}" required>
                                @error('basic_salary')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Allowance</label>
                                <input type="number" step="0.01" name="allowance"
                                    class="form-control @error('allowance') is-invalid @enderror"
                                    value="{{ old('allowance', 0) }}">
                                @error('allowance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">OT Rate / Hour</label>
                                <input type="number" step="0.01" name="ot_rate_per_hour" id="ot_rate_per_hour"
                                    class="form-control @error('ot_rate_per_hour') is-invalid @enderror"
                                    value="{{ old('ot_rate_per_hour', 0) }}" readonly>
                                @error('ot_rate_per_hour')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Late Deduction / Minute</label>
                                <input type="number" step="0.0001" name="late_deduction_per_minute"
                                    id="late_deduction_per_minute"
                                    class="form-control @error('late_deduction_per_minute') is-invalid @enderror"
                                    value="{{ old('late_deduction_per_minute', 0) }}" readonly>
                                @error('late_deduction_per_minute')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Undertime Deduction / Minute</label>
                                <input type="number" step="0.0001" name="undertime_deduction_per_minute"
                                    id="undertime_deduction_per_minute"
                                    class="form-control @error('undertime_deduction_per_minute') is-invalid @enderror"
                                    value="{{ old('undertime_deduction_per_minute', 0) }}" readonly>
                                @error('undertime_deduction_per_minute')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Absent Deduction / Day</label>
                                <input type="number" step="0.01" name="absent_deduction_per_day"
                                    id="absent_deduction_per_day"
                                    class="form-control @error('absent_deduction_per_day') is-invalid @enderror"
                                    value="{{ old('absent_deduction_per_day', 0) }}" readonly>
                                @error('absent_deduction_per_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">SSS Loan</label>
                                <input type="number" step="0.01" name="sss_loan"
                                    class="form-control @error('sss_loan') is-invalid @enderror"
                                    value="{{ old('sss_loan', 0) }}">
                                @error('sss_loan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Pagibig Loan</label>
                                <input type="number" step="0.01" name="pagibig_loan"
                                    class="form-control @error('pagibig_loan') is-invalid @enderror"
                                    value="{{ old('pagibig_loan', 0) }}">
                                @error('pagibig_loan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Vale</label>
                                <input type="number" step="0.01" name="vale"
                                    class="form-control @error('vale') is-invalid @enderror"
                                    value="{{ old('vale', 0) }}">
                                @error('vale')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Other Loans Deduction</label>
                                <input type="number" step="0.01" name="other_loans"
                                    class="form-control @error('other_loans') is-invalid @enderror"
                                    value="{{ old('other_loans', 0) }}">
                                @error('other_loans')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror">{{ old('remarks') }}</textarea>
                                @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        {{ old('is_active', 1) ? 'checked' : '' }} id="is_active">
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button class="btn btn-primary">Save Salary</button>
                            <a href="{{ route('payroll-employee-salaries.index') }}" class="btn btn-light">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        #employeeDropdown .list-group-item {
            border-left: 0;
            border-right: 0;
        }

        #employeeDropdown .list-group-item:first-child {
            border-top: 0;
        }

        #employeeDropdown .list-group-item:last-child {
            border-bottom: 0;
        }

        #employeeDropdown .employee-option:hover,
        #employeeDropdown .employee-option.active {
            background-color: rgba(44, 123, 229, 0.08);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const picker = document.getElementById('employeePicker');
            const dropdown = document.getElementById('employeeDropdown');
            const wrapper = picker.closest('.position-relative');
            const options = Array.from(document.querySelectorAll('.employee-option'));
            const noResult = document.getElementById('employeeNoResult');

            const biometricInput = document.getElementById('biometric_employee_id');
            const employeeNoInput = document.getElementById('employee_no');
            const employeeNameInput = document.getElementById('employee_name');
            const crosschexInput = document.getElementById('crosschex_id');

            const rateTypeInput = document.getElementById('rate_type');
            const basicSalaryInput = document.getElementById('basic_salary');
            const otRateInput = document.getElementById('ot_rate_per_hour');
            const lateInput = document.getElementById('late_deduction_per_minute');
            const undertimeInput = document.getElementById('undertime_deduction_per_minute');
            const absentInput = document.getElementById('absent_deduction_per_day');

            function showDropdown() {
                dropdown.classList.remove('d-none');
            }

            function hideDropdown() {
                dropdown.classList.add('d-none');
            }

            function clearSelectedFields() {
                biometricInput.value = '';
                employeeNoInput.value = '';
                employeeNameInput.value = '';
                crosschexInput.value = '';
            }

            function fillEmployee(option) {
                const name = option.dataset.name || '';
                const empno = option.dataset.empno || '';

                picker.value = name + (empno ? ' | ' + empno : '');
                biometricInput.value = option.dataset.biometric || '';
                employeeNoInput.value = empno;
                employeeNameInput.value = name;
                crosschexInput.value = option.dataset.crosschex || '';

                hideDropdown();
            }

            function filterOptions() {
                const keyword = picker.value.trim().toLowerCase();
                let visibleCount = 0;

                options.forEach(option => {
                    const haystack = option.dataset.search || '';
                    const matched = keyword === '' || haystack.includes(keyword);

                    option.classList.toggle('d-none', !matched);

                    if (matched) {
                        visibleCount++;
                    }
                });

                noResult.classList.toggle('d-none', visibleCount !== 0);
            }

            function roundTo(value, decimals) {
                return Number(value).toFixed(decimals);
            }

            function computeSalaryRates() {
                const rateType = rateTypeInput.value;
                const basicSalary = parseFloat(basicSalaryInput.value) || 0;

                const workingDaysPerMonth = 22;
                const hoursPerDay = 8;
                const minutesPerHour = 60;

                if (basicSalary <= 0) {
                    otRateInput.value = roundTo(0, 2);
                    lateInput.value = roundTo(0, 4);
                    undertimeInput.value = roundTo(0, 4);
                    absentInput.value = roundTo(0, 2);
                    return;
                }

                let dailyRate = 0;

                if (rateType === 'monthly') {
                    dailyRate = basicSalary / workingDaysPerMonth;
                } else {
                    dailyRate = basicSalary;
                }

                const hourlyRate = dailyRate / hoursPerDay;
                const perMinuteRate = hourlyRate / minutesPerHour;

                otRateInput.value = roundTo(hourlyRate, 2);
                lateInput.value = roundTo(perMinuteRate, 4);
                undertimeInput.value = roundTo(perMinuteRate, 4);
                absentInput.value = roundTo(dailyRate, 2);
            }

            picker.addEventListener('focus', function() {
                filterOptions();
                showDropdown();
            });

            picker.addEventListener('input', function() {
                clearSelectedFields();
                filterOptions();
                showDropdown();
            });

            options.forEach(option => {
                option.addEventListener('click', function() {
                    fillEmployee(option);
                });
            });

            document.addEventListener('click', function(e) {
                if (!wrapper.contains(e.target)) {
                    hideDropdown();
                }
            });

            basicSalaryInput.addEventListener('input', computeSalaryRates);
            rateTypeInput.addEventListener('change', computeSalaryRates);

            computeSalaryRates();
        });
    </script>
@endsection
