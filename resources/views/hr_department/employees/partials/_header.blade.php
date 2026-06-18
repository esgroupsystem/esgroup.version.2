@php
    $employeesCollection = collect($employees->items());

    $totalEmployees = $employeeStats['total'] ?? ($employees->total() ?? $employeesCollection->count());

    $activeEmployees =
        $employeeStats['active'] ??
        $employeesCollection
            ->filter(fn($e) => in_array($e->status ?? 'Active', ['Active', 'Active(Re-Entry)'], true))
            ->count();

    $inactiveEmployees =
        $employeeStats['inactive'] ??
        $employeesCollection
            ->filter(
                fn($e) => in_array(
                    $e->status ?? '',
                    ['Inactive', 'Resigned', 'Terminated', 'End of Contract', 'Retired', 'Retrench'],
                    true,
                ),
            )
            ->count();

    $suspendedEmployees =
        $employeeStats['suspended'] ??
        $employeesCollection->filter(fn($e) => ($e->status ?? '') === 'Suspended')->count();

    $companyCount = $employeeStats['companies'] ?? $employeesCollection->pluck('company')->filter()->unique()->count();

    $garageCount = $employeeStats['garages'] ?? $employeesCollection->pluck('garage')->filter()->unique()->count();
@endphp

<style>
    .employees-hero-card {
        overflow: hidden;
        border: 0;
        box-shadow: 0 .125rem .45rem rgba(0, 0, 0, .06);
    }

    .employees-hero-title {
        font-size: 1.35rem;
        font-weight: 700;
    }

    .employee-kpi-card {
        border: 1px solid rgba(216, 226, 239, .9);
        border-radius: .65rem;
        background: #fff;
        height: 100%;
    }

    .employee-kpi-card .card-body {
        padding: .75rem .85rem;
    }

    .employee-kpi-card h5 {
        font-size: 1rem;
        margin-bottom: .1rem;
    }

    .employee-kpi-card p {
        font-size: .72rem;
    }

    .employee-kpi-icon {
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
</style>

<div class="card employees-hero-card mb-3">
    <div class="bg-holder d-none d-lg-block bg-card"
        style="background-image:url(/assets/img/icons/spot-illustrations/corner-4.png); opacity:.9;">
    </div>

    <div class="card-body position-relative">
        <div class="row g-3 align-items-center">
            <div class="col-lg-5">
                <h3 class="employees-hero-title mb-1 text-900">
                    Employees Directory
                </h3>

                <p class="text-600 mb-2">
                    Manage employee profiles, employment status, contact details, company assignment, and HR records.
                </p>

                <div class="d-flex flex-wrap gap-2">
                    <span class="badge badge-subtle-primary">
                        <span class="fas fa-users me-1"></span>
                        {{ number_format($totalEmployees) }} Total Employees
                    </span>

                    <span class="badge badge-subtle-success">
                        <span class="fas fa-check-circle me-1"></span>
                        {{ number_format($activeEmployees) }} Active
                    </span>

                    <span class="badge badge-subtle-warning">
                        <span class="fas fa-pause-circle me-1"></span>
                        {{ number_format($suspendedEmployees) }} Suspended
                    </span>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="row g-2">
                    <div class="col-6 col-md-3">
                        <div class="employee-kpi-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="text-primary">{{ number_format($totalEmployees) }}</h5>
                                        <p class="mb-0 text-600">Total</p>
                                    </div>
                                    <span class="employee-kpi-icon bg-primary-subtle text-primary">
                                        <span class="fas fa-users"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="employee-kpi-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="text-success">{{ number_format($activeEmployees) }}</h5>
                                        <p class="mb-0 text-600">Active</p>
                                    </div>
                                    <span class="employee-kpi-icon bg-success-subtle text-success">
                                        <span class="fas fa-user-check"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="employee-kpi-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="text-secondary">{{ number_format($inactiveEmployees) }}</h5>
                                        <p class="mb-0 text-600">Inactive</p>
                                    </div>
                                    <span class="employee-kpi-icon bg-secondary-subtle text-secondary">
                                        <span class="fas fa-user-minus"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="employee-kpi-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="text-info">{{ number_format($companyCount) }}</h5>
                                        <p class="mb-0 text-600">Companies</p>
                                    </div>
                                    <span class="employee-kpi-icon bg-info-subtle text-info">
                                        <span class="fas fa-building"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
