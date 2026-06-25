<div class="row g-3 mb-4">

    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm report-stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <p class="text-muted small fw-semibold mb-1">
                            Total Employees
                        </p>
                        <h2 class="fw-bold mb-1">
                            {{ number_format($totalEmployees) }}
                        </h2>
                        <p class="text-muted small mb-0">
                            All employee records
                        </p>
                    </div>

                    <div class="report-stat-icon bg-primary-subtle text-primary">
                        <span class="fas fa-users"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm report-stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <p class="text-muted small fw-semibold mb-1">
                            Active Employees
                        </p>
                        <h2 class="fw-bold text-success mb-1">
                            {{ number_format($activeEmployees) }}
                        </h2>
                        <p class="text-muted small mb-0">
                            Current active manpower
                        </p>
                    </div>

                    <div class="report-stat-icon bg-success-subtle text-success">
                        <span class="fas fa-user-check"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm report-stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <p class="text-muted small fw-semibold mb-1">
                            Other Status
                        </p>
                        <h2 class="fw-bold text-warning mb-1">
                            {{ number_format($otherStatusEmployees) }}
                        </h2>
                        <p class="text-muted small mb-0">
                            Resigned, inactive, hold, or others
                        </p>
                    </div>

                    <div class="report-stat-icon bg-warning-subtle text-warning">
                        <span class="fas fa-user-clock"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm report-stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <p class="text-muted small fw-semibold mb-1">
                            Departments
                        </p>
                        <h2 class="fw-bold text-info mb-1">
                            {{ number_format($departmentSummary->count()) }}
                        </h2>
                        <p class="text-muted small mb-0">
                            Departments with employee count
                        </p>
                    </div>

                    <div class="report-stat-icon bg-info-subtle text-info">
                        <span class="fas fa-building"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
