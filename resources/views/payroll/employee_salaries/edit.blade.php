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
                                <input type="text" name="employee_no" class="form-control"
                                    value="{{ old('employee_no', $salary->employee_no) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Crosschex ID</label>
                                <input type="text" name="crosschex_id" class="form-control"
                                    value="{{ old('crosschex_id', $salary->crosschex_id) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Employee Name</label>
                                <input type="text" name="employee_name" class="form-control"
                                    value="{{ old('employee_name', $salary->employee_name) }}" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Rate Type</label>
                                <select name="rate_type" class="form-select">
                                    <option value="daily"
                                        {{ old('rate_type', $salary->rate_type) == 'daily' ? 'selected' : '' }}>Daily
                                    </option>
                                    <option value="monthly"
                                        {{ old('rate_type', $salary->rate_type) == 'monthly' ? 'selected' : '' }}>Monthly
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Basic Salary</label>
                                <input type="number" step="0.01" name="basic_salary" class="form-control"
                                    value="{{ old('basic_salary', $salary->basic_salary) }}" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Allowance</label>
                                <input type="number" step="0.01" name="allowance" class="form-control"
                                    value="{{ old('allowance', $salary->allowance) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">OT Rate / Hour</label>
                                <input type="number" step="0.01" name="ot_rate_per_hour" class="form-control"
                                    value="{{ old('ot_rate_per_hour', $salary->ot_rate_per_hour) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Late Deduction / Minute</label>
                                <input type="number" step="0.0001" name="late_deduction_per_minute" class="form-control"
                                    value="{{ old('late_deduction_per_minute', $salary->late_deduction_per_minute) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Undertime Deduction / Minute</label>
                                <input type="number" step="0.0001" name="undertime_deduction_per_minute"
                                    class="form-control"
                                    value="{{ old('undertime_deduction_per_minute', $salary->undertime_deduction_per_minute) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Absent Deduction / Day</label>
                                <input type="number" step="0.01" name="absent_deduction_per_day" class="form-control"
                                    value="{{ old('absent_deduction_per_day', $salary->absent_deduction_per_day) }}">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" rows="3" class="form-control">{{ old('remarks', $salary->remarks) }}</textarea>
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

                        <div class="mt-3 d-flex gap-2">
                            <button class="btn btn-primary">Update Salary</button>
                            <a href="{{ route('payroll-employee-salaries.index') }}" class="btn btn-light">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
