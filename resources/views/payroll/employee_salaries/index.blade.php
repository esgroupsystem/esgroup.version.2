@extends('layouts.app')
@section('title', 'Employee Salary Master')

@section('content')
<div class="container-fluid" data-layout="container">
    <div class="content">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Employee Salary Master</h5>
                    <small class="text-muted">Based on biometrics employee records</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('payroll-employee-salaries.sync') }}" class="btn btn-sm btn-warning">
                        <span class="fas fa-sync-alt me-1"></span> Sync from Biometrics
                    </a>
                    <a href="{{ route('payroll-employee-salaries.create') }}" class="btn btn-sm btn-primary">
                        <span class="fas fa-plus me-1"></span> Add Salary
                    </a>
                </div>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('payroll-employee-salaries.index') }}" class="row g-2 mb-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                            placeholder="Search employee name / employee no / biometric id"
                            value="{{ $search }}">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary">Search</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th>Employee No</th>
                                <th>Rate Type</th>
                                <th>Basic Salary</th>
                                <th>Allowance</th>
                                <th>OT / Hr</th>
                                <th>Status</th>
                                <th width="140">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($salaries as $salary)
                                <tr>
                                    <td>{{ $salary->employee_name }}</td>
                                    <td>{{ $salary->employee_no ?: '—' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($salary->rate_type) }}</span>
                                    </td>
                                    <td>{{ number_format($salary->basic_salary, 2) }}</td>
                                    <td>{{ number_format($salary->allowance, 2) }}</td>
                                    <td>{{ number_format($salary->ot_rate_per_hour, 2) }}</td>
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
                                                method="POST"
                                                onsubmit="return confirm('Delete this salary record?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No salary records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $salaries->links('pagination.custom') }}
            </div>
        </div>
    </div>
</div>
@endsection