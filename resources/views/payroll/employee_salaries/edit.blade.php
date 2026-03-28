@extends('layouts.app')
@section('title', 'Edit Employee Salary')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-body-tertiary">
                    <h5 class="mb-0">Edit Employee Salary</h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('payroll-employee-salaries.update', $salary) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Biometric Employee ID</label>
                                <input type="text" class="form-control" value="{{ $salary->biometric_employee_id }}"
                                    readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Employee No</label>
                                <input type="text" name="employee_no"
                                    class="form-control @error('employee_no') is-invalid @enderror"
                                    value="{{ old('employee_no', $salary->employee_no) }}">
                                @error('employee_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Crosschex ID</label>
                                <input type="text" name="crosschex_id"
                                    class="form-control @error('crosschex_id') is-invalid @enderror"
                                    value="{{ old('crosschex_id', $salary->crosschex_id) }}">
                                @error('crosschex_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Employee Name</label>
                                <input type="text" name="employee_name"
                                    class="form-control @error('employee_name') is-invalid @enderror"
                                    value="{{ old('employee_name', $salary->employee_name) }}" required>
                                @error('employee_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Rate Type</label>
                                <select name="rate_type" id="rate_type"
                                    class="form-select @error('rate_type') is-invalid @enderror">
                                    <option value="daily"
                                        {{ old('rate_type', $salary->rate_type) == 'daily' ? 'selected' : '' }}>Daily
                                    </option>
                                    <option value="monthly"
                                        {{ old('rate_type', $salary->rate_type) == 'monthly' ? 'selected' : '' }}>Monthly
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
                                    value="{{ old('basic_salary', $salary->basic_salary) }}" required>
                                @error('basic_salary')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Allowance</label>
                                <input type="number" step="0.01" name="allowance"
                                    class="form-control @error('allowance') is-invalid @enderror"
                                    value="{{ old('allowance', $salary->allowance) }}">
                                @error('allowance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">OT Rate / Hour</label>
                                <input type="number" step="0.01" name="ot_rate_per_hour" id="ot_rate_per_hour"
                                    class="form-control @error('ot_rate_per_hour') is-invalid @enderror"
                                    value="{{ old('ot_rate_per_hour', $salary->ot_rate_per_hour) }}" readonly>
                                @error('ot_rate_per_hour')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Late Deduction / Minute</label>
                                <input type="number" step="0.0001" name="late_deduction_per_minute"
                                    id="late_deduction_per_minute"
                                    class="form-control @error('late_deduction_per_minute') is-invalid @enderror"
                                    value="{{ old('late_deduction_per_minute', $salary->late_deduction_per_minute) }}"
                                    readonly>
                                @error('late_deduction_per_minute')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Undertime Deduction / Minute</label>
                                <input type="number" step="0.0001" name="undertime_deduction_per_minute"
                                    id="undertime_deduction_per_minute"
                                    class="form-control @error('undertime_deduction_per_minute') is-invalid @enderror"
                                    value="{{ old('undertime_deduction_per_minute', $salary->undertime_deduction_per_minute) }}"
                                    readonly>
                                @error('undertime_deduction_per_minute')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Absent Deduction / Day</label>
                                <input type="number" step="0.01" name="absent_deduction_per_day"
                                    id="absent_deduction_per_day"
                                    class="form-control @error('absent_deduction_per_day') is-invalid @enderror"
                                    value="{{ old('absent_deduction_per_day', $salary->absent_deduction_per_day) }}"
                                    readonly>
                                @error('absent_deduction_per_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">SSS Loan</label>
                                <input type="number" step="0.01" name="sss_loan"
                                    class="form-control @error('sss_loan') is-invalid @enderror"
                                    value="{{ old('sss_loan', $salary->sss_loan ?? 0) }}">
                                @error('sss_loan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Pagibig Loan</label>
                                <input type="number" step="0.01" name="pagibig_loan"
                                    class="form-control @error('pagibig_loan') is-invalid @enderror"
                                    value="{{ old('pagibig_loan', $salary->pagibig_loan ?? 0) }}">
                                @error('pagibig_loan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Vale</label>
                                <input type="number" step="0.01" name="vale"
                                    class="form-control @error('vale') is-invalid @enderror"
                                    value="{{ old('vale', $salary->vale ?? 0) }}">
                                @error('vale')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Other Loans Deduction</label>
                                <input type="number" step="0.01" name="other_loans"
                                    class="form-control @error('other_loans') is-invalid @enderror"
                                    value="{{ old('other_loans', $salary->other_loans ?? 0) }}">
                                @error('other_loans')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror">{{ old('remarks', $salary->remarks) }}</textarea>
                                @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        id="is_active" {{ old('is_active', $salary->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button class="btn btn-primary">Update Salary</button>
                            <a href="{{ route('payroll-employee-salaries.index') }}" class="btn btn-light">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rateTypeInput = document.getElementById('rate_type');
            const basicSalaryInput = document.getElementById('basic_salary');
            const otRateInput = document.getElementById('ot_rate_per_hour');
            const lateInput = document.getElementById('late_deduction_per_minute');
            const undertimeInput = document.getElementById('undertime_deduction_per_minute');
            const absentInput = document.getElementById('absent_deduction_per_day');

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

            basicSalaryInput.addEventListener('input', computeSalaryRates);
            rateTypeInput.addEventListener('change', computeSalaryRates);

            computeSalaryRates();
        });
    </script>
@endsection
