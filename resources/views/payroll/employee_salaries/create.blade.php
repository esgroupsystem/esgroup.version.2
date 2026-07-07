@extends('layouts.app')
@section('title', 'Add Employee Salary')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-body-tertiary d-flex flex-column flex-lg-row justify-content-between gap-2">
                    <div>
                        <h5 class="mb-1">
                            <span class="fas fa-user-plus text-primary me-2"></span>
                            Add Employee Salary Profile
                        </h5>
                        <p class="mb-0 text-muted small">
                            Setup salary rate, government deductions, allowances, loans, cash advance, and cutoff rules using employee_biometric_id.
                        </p>
                    </div>

                    <a href="{{ route('payroll-employee-salaries.index') }}" class="btn btn-light btn-sm align-self-start">
                        <span class="fas fa-arrow-left me-1"></span> Back
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('payroll-employee-salaries.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            @include('payroll.employee_salaries._identity_create', ['people' => $people])
                            @include('payroll.employee_salaries._salary_fields')
                        </div>

                        <div class="mt-4 d-flex flex-wrap gap-2">
                            <button class="btn btn-primary">
                                <span class="fas fa-save me-1"></span>
                                Save Salary Profile
                            </button>
                            <a href="{{ route('payroll-employee-salaries.index') }}" class="btn btn-light">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
