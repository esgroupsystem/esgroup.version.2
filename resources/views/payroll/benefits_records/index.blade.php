@extends('layouts.app')

@section('title', 'Benefits Records')

@section('content')
    @php
        $money = fn ($value) => '₱ ' . number_format((float) $value, 2);
        $month = (int) data_get($filters, 'month', now('Asia/Manila')->month);
        $year = (int) data_get($filters, 'year', now('Asia/Manila')->year);
        $periodLabel = \Carbon\Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila')->format('F Y');
        $reportQuery = array_filter([
            'month' => $month,
            'year' => $year,
            'search' => data_get($filters, 'search'),
            'garage_group' => data_get($filters, 'garage_group'),
        ], fn ($value) => $value !== null && $value !== '');

        $employeeDue = (float) ($totals->employee_total ?? 0);
        $employeeCollected = (float) ($totals->sss_employee_collected ?? 0)
            + (float) ($totals->philhealth_employee_collected ?? 0)
            + (float) ($totals->pagibig_employee_collected ?? 0);
        $unrecovered = (float) ($totals->employee_share_unrecovered ?? 0);
    @endphp

    @once
        <style>
            .benefits-page { font-size: .875rem; }
            .benefits-kpi,
            .employee-benefit-card {
                border: 1px solid var(--falcon-border-color, #d8e2ef);
                border-radius: .75rem;
                background: var(--falcon-card-bg, #fff);
            }
            .benefits-kpi { padding: .9rem 1rem; height: 100%; }
            .benefits-kpi-label {
                color: var(--falcon-600, #748194);
                font-size: .67rem;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
                margin-bottom: .3rem;
            }
            .benefits-kpi-value {
                color: var(--falcon-900, #344050);
                font-size: 1.12rem;
                font-weight: 700;
                font-variant-numeric: tabular-nums;
            }
            .employee-benefit-card { overflow: hidden; }
            .employee-benefit-header {
                background: #fff7d6;
                border-bottom: 1px solid #f0d98b;
                padding: .85rem 1rem;
            }
            .benefit-program-table th {
                color: var(--falcon-600, #748194);
                font-size: .67rem;
                letter-spacing: .035em;
                text-transform: uppercase;
                white-space: nowrap;
            }
            .benefit-program-table td { vertical-align: middle; }
            .benefit-program-name { font-weight: 700; color: var(--falcon-900, #344050); }
            .benefit-id {
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                font-size: .75rem;
                white-space: nowrap;
            }
            .benefit-money { font-variant-numeric: tabular-nums; white-space: nowrap; font-weight: 600; }
            .benefit-summary-strip {
                background: var(--falcon-100, #f9fafd);
                border-top: 1px solid var(--falcon-border-color, #d8e2ef);
            }
            .settlement-warning {
                background: #fff7d6;
                border: 1px solid #f0d98b;
                border-radius: .55rem;
            }
        </style>
    @endonce

    <div class="container-fluid benefits-page" data-layout="container">
        <div class="content">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <div>
                            <h4 class="mb-1 text-dark">
                                <i class="fas fa-shield-alt text-warning me-2"></i>Benefits Records
                            </h4>
                            <p class="mb-0 text-muted small">
                                One monthly record per employee. Statutory amount due is kept separate from the amount actually collected from payroll.
                            </p>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="badge badge-subtle-warning text-warning px-3 py-2">
                                <i class="fas fa-calendar-alt me-1"></i>{{ $periodLabel }}
                            </span>
                            <a href="{{ route('benefits-records.overall', $reportQuery) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-file-invoice-dollar me-1"></i>Overall / Print
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('benefits-records.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-4 col-xl-3">
                            <label for="search" class="form-label">Search employee/company</label>
                            <input type="text" class="form-control" id="search" name="search"
                                value="{{ data_get($filters, 'search') }}" placeholder="Name, employee no., company">
                        </div>
                        <div class="col-6 col-md-2">
                            <label for="month" class="form-label">Month</label>
                            <select class="form-select" id="month" name="month">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" @selected($month === $m)>
                                        {{ \Carbon\Carbon::create(2000, $m, 1)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label for="year" class="form-label">Year</label>
                            <input type="number" class="form-control" id="year" name="year" min="2020" max="2100" value="{{ $year }}">
                        </div>
                        <div class="col-md-3 col-xl-2">
                            <label for="garage_group" class="form-label">Payroll Group</label>
                            <select class="form-select" id="garage_group" name="garage_group">
                                <option value="">All allowed groups</option>
                                @foreach ($groupOptions as $groupId => $groupLabel)
                                    <option value="{{ $groupId }}" @selected((int) data_get($filters, 'garage_group') === (int) $groupId)>
                                        {{ $groupLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i>Apply
                            </button>
                            <a href="{{ route('benefits-records.index') }}" class="btn btn-falcon-default">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl">
                    <div class="benefits-kpi">
                        <div class="benefits-kpi-label">Active Employees</div>
                        <div class="benefits-kpi-value">{{ number_format($activeEmployeeCount) }}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl">
                    <div class="benefits-kpi">
                        <div class="benefits-kpi-label">Posted</div>
                        <div class="benefits-kpi-value text-success">{{ number_format($postedEmployeeCount) }}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl">
                    <div class="benefits-kpi">
                        <div class="benefits-kpi-label">Employee Due</div>
                        <div class="benefits-kpi-value">{{ $money($employeeDue) }}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl">
                    <div class="benefits-kpi">
                        <div class="benefits-kpi-label">Actually Collected</div>
                        <div class="benefits-kpi-value text-primary">{{ $money($employeeCollected) }}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl">
                    <div class="benefits-kpi">
                        <div class="benefits-kpi-label">Unrecovered / Advanced</div>
                        <div class="benefits-kpi-value {{ $unrecovered > 0 ? 'text-warning' : 'text-success' }}">{{ $money($unrecovered) }}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl">
                    <div class="benefits-kpi">
                        <div class="benefits-kpi-label">Company Share</div>
                        <div class="benefits-kpi-value">{{ $money($totals->employer_total ?? 0) }}</div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h5 class="mb-0">Employee Benefit Records</h5>
                    <div class="small text-muted">Only finalized payroll contributions are posted to this monthly ledger.</div>
                </div>
                <div class="small text-muted">{{ number_format($employees->total()) }} employee(s)</div>
            </div>

            <div class="row g-3">
                @forelse ($employees as $row)
                    @php
                        $employee = $row['employee'];
                        $summary = $row['summary'];
                        $identifiers = $row['identifiers'];
                        $settlementStatus = (string) ($summary['settlement_status'] ?? 'not_posted');
                        $settlementMode = (string) data_get($summary, 'settlement_meta.mode', '');
                        $employeeCollectedTotal = (float) ($summary['sss_employee_collected'] ?? 0)
                            + (float) ($summary['philhealth_employee_collected'] ?? 0)
                            + (float) ($summary['pagibig_employee_collected'] ?? 0);
                        $employeeUnrecovered = (float) ($summary['employee_share_unrecovered'] ?? 0);
                        $statusClass = match ($settlementStatus) {
                            'complete' => 'success',
                            'employer_advanced' => 'warning',
                            'partial_collection' => 'warning',
                            default => $summary['posted'] ? 'info' : 'secondary',
                        };
                        $statusText = match ($settlementStatus) {
                            'complete' => 'Complete',
                            'employer_advanced' => 'Employer Advance',
                            'partial_collection' => 'Partial / Capped',
                            default => $summary['posted'] ? 'Finalized' : 'Not Posted',
                        };
                        $programs = [
                            [
                                'name' => 'SSS',
                                'id' => $identifiers['sss'] ?? null,
                                'due' => $summary['sss_employee_total'] ?? 0,
                                'collected' => $summary['sss_employee_collected'] ?? 0,
                                'employer' => $summary['sss_employer_total'] ?? 0,
                                'total' => $summary['sss_total_contribution'] ?? 0,
                            ],
                            [
                                'name' => 'PhilHealth',
                                'id' => $identifiers['philhealth'] ?? null,
                                'due' => $summary['philhealth_employee'] ?? 0,
                                'collected' => $summary['philhealth_employee_collected'] ?? 0,
                                'employer' => $summary['philhealth_employer'] ?? 0,
                                'total' => $summary['philhealth_total'] ?? 0,
                            ],
                            [
                                'name' => 'Pag-IBIG',
                                'id' => $identifiers['pagibig'] ?? null,
                                'due' => $summary['pagibig_employee'] ?? 0,
                                'collected' => $summary['pagibig_employee_collected'] ?? 0,
                                'employer' => $summary['pagibig_employer'] ?? 0,
                                'total' => $summary['pagibig_total'] ?? 0,
                            ],
                        ];
                    @endphp

                    <div class="col-12">
                        <div class="employee-benefit-card shadow-sm">
                            <div class="employee-benefit-header">
                                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2">
                                    <div>
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <h6 class="mb-0 text-dark">{{ $employee->payroll_display_name }}</h6>
                                            <span class="badge badge-subtle-{{ $statusClass }} text-{{ $statusClass }}">{{ $statusText }}</span>
                                        </div>
                                        <div class="small text-muted mt-1">
                                            {{ $employee->effective_employee_no ?: 'No employee no.' }}
                                            <span class="mx-1">•</span>{{ $employee->company?->name ?: 'No company' }}
                                            <span class="mx-1">•</span>{{ $employee->payroll_group_label }}
                                        </div>
                                    </div>
                                    <div class="text-lg-end small">
                                        @if (! empty($summary['payroll_numbers']))
                                            <div class="fw-semibold">Payroll: {{ implode(', ', $summary['payroll_numbers']) }}</div>
                                        @endif
                                        <div class="text-muted">{{ $periodLabel }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover mb-0 benefit-program-table">
                                    <thead class="bg-body-tertiary">
                                        <tr>
                                            <th class="ps-3">Benefit</th>
                                            <th>Government ID</th>
                                            <th class="text-end">Employee Due</th>
                                            <th class="text-end">Collected in Payroll</th>
                                            <th class="text-end">Company Share</th>
                                            <th class="text-end pe-3">Contribution Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($programs as $program)
                                            @php
                                                $programGap = max(0, (float) $program['due'] - (float) $program['collected']);
                                            @endphp
                                            <tr>
                                                <td class="ps-3 benefit-program-name">{{ $program['name'] }}</td>
                                                <td class="benefit-id">{{ $program['id'] ?: 'Not encoded' }}</td>
                                                <td class="text-end benefit-money">{{ $money($program['due']) }}</td>
                                                <td class="text-end benefit-money {{ $programGap > 0 ? 'text-warning' : 'text-success' }}">
                                                    {{ $money($program['collected']) }}
                                                    @if ($programGap > 0)
                                                        <div class="small text-warning">Gap {{ $money($programGap) }}</div>
                                                    @endif
                                                </td>
                                                <td class="text-end benefit-money">{{ $money($program['employer']) }}</td>
                                                <td class="text-end benefit-money pe-3">{{ $money($program['total']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="benefit-summary-strip px-3 py-2">
                                <div class="row g-2 align-items-center small">
                                    <div class="col-6 col-md">
                                        <span class="text-muted">Employee due:</span>
                                        <span class="fw-bold ms-1">{{ $money($summary['employee_total']) }}</span>
                                    </div>
                                    <div class="col-6 col-md">
                                        <span class="text-muted">Collected:</span>
                                        <span class="fw-bold text-primary ms-1">{{ $money($employeeCollectedTotal) }}</span>
                                    </div>
                                    <div class="col-6 col-md">
                                        <span class="text-muted">Company:</span>
                                        <span class="fw-bold ms-1">{{ $money($summary['employer_total']) }}</span>
                                    </div>
                                    <div class="col-6 col-md">
                                        <span class="text-muted">Combined:</span>
                                        <span class="fw-bold text-success ms-1">{{ $money($summary['grand_total']) }}</span>
                                    </div>
                                </div>

                                @if ($employeeUnrecovered > 0)
                                    <div class="settlement-warning mt-2 px-3 py-2 small">
                                        <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                        <strong>{{ $money($employeeUnrecovered) }}</strong> employee share was not collected from salary.
                                        @if ($settlementMode === 'employer_advance')
                                            Employer Advance is selected; keep this amount for HR/accounting settlement instead of making payroll negative.
                                        @else
                                            Payroll collection was capped to available pay; HR may review the employee settlement/refund action from the payroll item.
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5 text-muted">
                                <i class="fas fa-shield-alt fa-2x mb-3 d-block"></i>
                                No employees found for the selected filters.
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($employees->hasPages())
                <div class="mt-3">
                    {{ $employees->links('pagination.custom') }}
                </div>
            @endif
        </div>
    </div>
@endsection
