@extends('layouts.app')
@section('title', 'Employee Salary Master')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">

            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
            @endif

            <div class="card shadow-sm border-0">
                <div
                    class="card-header bg-body-tertiary d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div>
                        <h5 class="mb-0">Employee Salary Master</h5>
                        <small class="text-muted">Based on biometrics employee records</small>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('payroll-employee-salaries.sync') }}" class="btn btn-warning btn-sm">
                            <span class="fas fa-sync-alt me-1"></span> Sync from Biometrics
                        </a>
                        <a href="{{ route('payroll-employee-salaries.create') }}" class="btn btn-primary btn-sm">
                            <span class="fas fa-plus me-1"></span> Add Salary
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('payroll-employee-salaries.index') }}" class="row g-2 mb-3">
                        <div class="col-md-5">
                            <input type="text" name="search" class="form-control"
                                placeholder="Search employee name / employee no / biometric id / crosschex id"
                                value="{{ $search }}">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary">
                                <span class="fas fa-search me-1"></span> Search
                            </button>
                        </div>
                        @if ($search)
                            <div class="col-auto">
                                <a href="{{ route('payroll-employee-salaries.index') }}" class="btn btn-light">
                                    Clear
                                </a>
                            </div>
                        @endif
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 220px;">Employee</th>
                                    <th style="min-width: 110px;">Employee No</th>
                                    <th style="min-width: 100px;">Rate Type</th>
                                    <th class="text-end" style="min-width: 120px;">Basic Salary</th>
                                    <th class="text-end" style="min-width: 110px;">Allowance</th>
                                    <th class="text-end" style="min-width: 110px;">OT / Hr</th>
                                    <th class="text-end" style="min-width: 120px;">Late / Min</th>
                                    <th class="text-end" style="min-width: 140px;">Undertime / Min</th>
                                    <th class="text-end" style="min-width: 130px;">Absent / Day</th>
                                    <th class="text-end" style="min-width: 110px;">SSS Loan</th>
                                    <th class="text-end" style="min-width: 120px;">Pagibig Loan</th>
                                    <th class="text-end" style="min-width: 90px;">Vale</th>
                                    <th class="text-end" style="min-width: 120px;">Other Loans</th>
                                    <th style="min-width: 90px;">Status</th>
                                    <th width="150">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($salaries as $salary)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $salary->employee_name }}</div>
                                        </td>
                                        <td>{{ $salary->employee_no ?: '—' }}</td>
                                        <td>
                                            <span
                                                class="badge {{ $salary->rate_type === 'monthly' ? 'bg-primary' : 'bg-info' }}">
                                                {{ ucfirst($salary->rate_type) }}
                                            </span>
                                        </td>
                                        <td class="text-end">{{ number_format((float) $salary->basic_salary, 2) }}</td>
                                        <td class="text-end">{{ number_format((float) $salary->allowance, 2) }}</td>
                                        <td class="text-end">{{ number_format((float) $salary->ot_rate_per_hour, 2) }}</td>
                                        <td class="text-end">
                                            {{ number_format((float) $salary->late_deduction_per_minute, 4) }}</td>
                                        <td class="text-end">
                                            {{ number_format((float) $salary->undertime_deduction_per_minute, 4) }}</td>
                                        <td class="text-end">
                                            {{ number_format((float) $salary->absent_deduction_per_day, 2) }}</td>
                                        <td class="text-end">{{ number_format((float) ($salary->sss_loan ?? 0), 2) }}</td>
                                        <td class="text-end">{{ number_format((float) ($salary->pagibig_loan ?? 0), 2) }}
                                        </td>
                                        <td class="text-end">{{ number_format((float) ($salary->vale ?? 0), 2) }}</td>
                                        <td class="text-end">{{ number_format((float) ($salary->other_loans ?? 0), 2) }}
                                        </td>
                                        <td>
                                            @if ($salary->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('payroll-employee-salaries.edit', $salary) }}"
                                                    class="btn btn-sm btn-primary">
                                                    Edit
                                                </a>

                                                <form action="{{ route('payroll-employee-salaries.destroy', $salary) }}"
                                                    method="POST" onsubmit="return confirm('Delete this salary record?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="15" class="text-center text-muted py-4">No salary records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $salaries->links('pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
