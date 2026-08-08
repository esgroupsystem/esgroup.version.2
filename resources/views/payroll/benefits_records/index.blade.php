@extends('layouts.app')

@section('title', 'Benefits Records')

@section('content')
    @php
        $money = fn($value) => '₱ ' . number_format((float) $value, 2);
        $month = (int) data_get($filters, 'month', now('Asia/Manila')->month);
        $year = (int) data_get($filters, 'year', now('Asia/Manila')->year);
        $periodLabel = \Carbon\Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila')->format('F Y');
        $reportQuery = array_filter([
            'month' => $month,
            'year' => $year,
            'search' => data_get($filters, 'search'),
            'garage_group' => data_get($filters, 'garage_group'),
        ], fn ($value) => $value !== null && $value !== '');
    @endphp

    @once
        <style>
            .benefits-page {
                font-size: .875rem;
            }

            .benefits-kpi {
                border: 1px solid var(--falcon-border-color, #d8e2ef);
                border-radius: .75rem;
                padding: 1rem;
                height: 100%;
                background: var(--falcon-card-bg, #fff);
            }

            .benefits-kpi-label {
                color: var(--falcon-600, #748194);
                font-size: .68rem;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
                margin-bottom: .35rem;
            }

            .benefits-kpi-value {
                font-size: 1.2rem;
                font-weight: 700;
                color: var(--falcon-900, #344050);
            }

            .benefits-table th {
                white-space: nowrap;
                font-size: .72rem;
                text-transform: uppercase;
                letter-spacing: .03em;
            }

            .benefits-table td {
                vertical-align: middle;
            }

            .benefits-money {
                font-variant-numeric: tabular-nums;
                white-space: nowrap;
                font-weight: 600;
            }

            .benefits-detail-card {
                border: 1px solid var(--falcon-border-color, #d8e2ef);
                border-radius: .65rem;
                background: var(--falcon-card-bg, #fff);
                height: 100%;
            }

            .benefits-detail-label {
                color: var(--falcon-600, #748194);
                font-size: .7rem;
                text-transform: uppercase;
                font-weight: 700;
            }

            .benefits-detail-value {
                font-weight: 600;
                color: var(--falcon-900, #344050);
            }

            .benefits-id {
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                font-size: .75rem;
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
                                <i class="fas fa-shield-alt text-primary me-2"></i>
                                Benefits Records
                            </h4>
                            <p class="mb-0 text-muted small">
                                Finalized SSS, PhilHealth, and Pag-IBIG employee deductions with employer counterpart contributions.
                                Records are posted only when payroll is finalized.
                            </p>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="badge badge-subtle-primary text-primary px-3 py-2">
                                <i class="fas fa-calendar-alt me-1"></i>
                                {{ $periodLabel }}
                            </span>
                            <a href="{{ route('benefits-records.overall', $reportQuery) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-file-invoice-dollar me-1"></i>
                                Overall / Print
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
                            <input type="number" class="form-control" id="year" name="year" min="2020"
                                max="2100" value="{{ $year }}">
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
                                <i class="fas fa-filter me-1"></i>
                                Apply
                            </button>
                            <a href="{{ route('benefits-records.index') }}" class="btn btn-falcon-default">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-2">
                    <div class="benefits-kpi">
                        <div class="benefits-kpi-label">Active Employees</div>
                        <div class="benefits-kpi-value">{{ number_format($activeEmployeeCount) }}</div>
                        <div class="text-muted small">Payroll inclusion ON</div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-2">
                    <div class="benefits-kpi">
                        <div class="benefits-kpi-label">Posted Employees</div>
                        <div class="benefits-kpi-value text-success">{{ number_format($postedEmployeeCount) }}</div>
                        <div class="text-muted small">Has finalized payroll record</div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-2">
                    <div class="benefits-kpi">
                        <div class="benefits-kpi-label">Not Posted</div>
                        <div class="benefits-kpi-value {{ $notPostedEmployeeCount > 0 ? 'text-warning' : 'text-success' }}">
                            {{ number_format($notPostedEmployeeCount) }}
                        </div>
                        <div class="text-muted small">No finalized benefit record yet</div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-2">
                    <div class="benefits-kpi">
                        <div class="benefits-kpi-label">Employee Share</div>
                        <div class="benefits-kpi-value">{{ $money($totals->employee_total ?? 0) }}</div>
                        <div class="text-muted small">SSS + PhilHealth + Pag-IBIG</div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-2">
                    <div class="benefits-kpi">
                        <div class="benefits-kpi-label">Company Share</div>
                        <div class="benefits-kpi-value">{{ $money($totals->employer_total ?? 0) }}</div>
                        <div class="text-muted small">Includes SSS EC</div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-2">
                    <div class="benefits-kpi">
                        <div class="benefits-kpi-label">Combined Contribution</div>
                        <div class="benefits-kpi-value text-primary">{{ $money($totals->grand_total ?? 0) }}</div>
                        <div class="text-muted small">Employee + company</div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2">
                        <div>
                            <h5 class="mb-1">Contribution Summary</h5>
                            <div class="text-muted small">
                                Monthly totals from finalized payroll snapshots only.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 benefits-table">
                            <thead class="bg-body-tertiary">
                                <tr>
                                    <th class="ps-3">Employee</th>
                                    <th>Company / Group</th>
                                    <th class="text-end">SSS Employee</th>
                                    <th class="text-end">SSS Company</th>
                                    <th class="text-end">PhilHealth EE / ER</th>
                                    <th class="text-end">Pag-IBIG EE / ER</th>
                                    <th class="text-end">Employee Total</th>
                                    <th class="text-end">Company Total</th>
                                    <th class="text-end">Combined</th>
                                    <th class="text-end pe-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($employees as $row)
                                    @php
                                        $employee = $row['employee'];
                                        $summary = $row['summary'];
                                        $identifiers = $row['identifiers'];
                                        $detailId = 'benefit-detail-' . $employee->id;
                                    @endphp

                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark">{{ $employee->effective_name }}</div>
                                            <div class="text-muted small">{{ $employee->effective_employee_no ?: 'No employee no.' }}</div>
                                        </td>

                                        <td>
                                            <div class="fw-semibold">{{ $employee->company?->name ?: 'No company' }}</div>
                                            <div class="text-muted small">{{ $employee->payroll_group_label }}</div>
                                        </td>

                                        <td class="text-end benefits-money">
                                            {{ $money($summary['sss_employee_total']) }}
                                        </td>

                                        <td class="text-end benefits-money">
                                            {{ $money($summary['sss_employer_total']) }}
                                            <div class="text-muted small">incl. EC {{ $money($summary['sss_employer_ec']) }}</div>
                                        </td>

                                        <td class="text-end benefits-money">
                                            {{ $money($summary['philhealth_employee']) }} / {{ $money($summary['philhealth_employer']) }}
                                        </td>

                                        <td class="text-end benefits-money">
                                            {{ $money($summary['pagibig_employee']) }} / {{ $money($summary['pagibig_employer']) }}
                                        </td>

                                        <td class="text-end benefits-money text-danger">
                                            {{ $money($summary['employee_total']) }}
                                        </td>

                                        <td class="text-end benefits-money text-primary">
                                            {{ $money($summary['employer_total']) }}
                                        </td>

                                        <td class="text-end benefits-money text-success">
                                            {{ $money($summary['grand_total']) }}
                                        </td>

                                        <td class="text-end pe-3">
                                            @if ($summary['posted'] && $summary['has_contribution'])
                                                <span class="badge badge-subtle-success text-success mb-1">Posted</span>
                                            @elseif ($summary['posted'])
                                                <span class="badge badge-subtle-info text-info mb-1">Finalized / Zero</span>
                                            @else
                                                <span class="badge badge-subtle-warning text-warning mb-1">Not Posted</span>
                                            @endif

                                            <div>
                                                <button class="btn btn-falcon-default btn-sm mt-1" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#{{ $detailId }}"
                                                    aria-expanded="false" aria-controls="{{ $detailId }}">
                                                    Details
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr class="bg-body-tertiary">
                                        <td colspan="10" class="p-0 border-0">
                                            <div class="collapse p-3 p-lg-4" id="{{ $detailId }}">
                                                <div class="row g-3">
                                                <div class="col-xl-4">
                                                    <div class="benefits-detail-card p-3">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <h6 class="mb-0 text-primary">SSS Complete Breakdown</h6>
                                                            <span class="badge badge-subtle-primary text-primary">Circular 2024-006</span>
                                                        </div>

                                                        <div class="row g-2 small">
                                                            <div class="col-6">
                                                                <div class="benefits-detail-label">SSS No.</div>
                                                                <div class="benefits-detail-value benefits-id">{{ $identifiers['sss'] ?: 'Not encoded' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="benefits-detail-label">1st Cutoff Gross (26-10)</div>
                                                                <div class="benefits-detail-value">{{ $money($summary['business_first_cutoff_gross']) }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="benefits-detail-label">2nd Cutoff Gross (11-25)</div>
                                                                <div class="benefits-detail-value">{{ $money($summary['business_second_cutoff_gross']) }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="benefits-detail-label">Monthly SSS Compensation</div>
                                                                <div class="benefits-detail-value fw-bold text-primary">{{ $money($summary['sss_compensation_basis']) }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="benefits-detail-label">Monthly Salary Credit</div>
                                                                <div class="benefits-detail-value">{{ $money($summary['sss_msc']) }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="benefits-detail-label">Compensation Range</div>
                                                                <div class="benefits-detail-value">
                                                                    {{ $money($summary['sss_compensation_range_minimum']) }} -
                                                                    {{ $summary['sss_compensation_range_maximum'] !== null ? $money($summary['sss_compensation_range_maximum']) : 'Over' }}
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="benefits-detail-label">Regular SS MSC</div>
                                                                <div class="benefits-detail-value">{{ $money($summary['sss_regular_ss_msc']) }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="benefits-detail-label">MPF MSC</div>
                                                                <div class="benefits-detail-value">{{ $money($summary['sss_mpf_msc']) }}</div>
                                                            </div>
                                                        </div>

                                                        <div class="alert alert-info py-2 px-3 mt-3 mb-0 small">
                                                            <strong>Monthly SSS formula:</strong> 1st cutoff gross + 2nd cutoff gross =
                                                            {{ $money($summary['business_first_cutoff_gross']) }} + {{ $money($summary['business_second_cutoff_gross']) }} =
                                                            <strong>{{ $money($summary['sss_compensation_basis']) }}</strong>.
                                                            The MSC, Regular SS, MPF and EC are then taken from SSS Circular 2024-006.
                                                        </div>

                                                        <hr>

                                                        <div class="table-responsive">
                                                            <table class="table table-sm mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th></th>
                                                                        <th class="text-end">Regular SS</th>
                                                                        <th class="text-end">MPF</th>
                                                                        <th class="text-end">EC</th>
                                                                        <th class="text-end">Total</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td>Employee</td>
                                                                        <td class="text-end">{{ $money($summary['sss_employee_regular_ss']) }}</td>
                                                                        <td class="text-end">{{ $money($summary['sss_employee_mpf']) }}</td>
                                                                        <td class="text-end">—</td>
                                                                        <td class="text-end fw-bold">{{ $money($summary['sss_employee_total']) }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Company</td>
                                                                        <td class="text-end">{{ $money($summary['sss_employer_regular_ss']) }}</td>
                                                                        <td class="text-end">{{ $money($summary['sss_employer_mpf']) }}</td>
                                                                        <td class="text-end">{{ $money($summary['sss_employer_ec']) }}</td>
                                                                        <td class="text-end fw-bold">{{ $money($summary['sss_employer_total']) }}</td>
                                                                    </tr>
                                                                </tbody>
                                                                <tfoot>
                                                                    <tr class="fw-bold">
                                                                        <td colspan="4">SSS Combined</td>
                                                                        <td class="text-end">{{ $money($summary['sss_total_contribution']) }}</td>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-4">
                                                    <div class="benefits-detail-card p-3">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <h6 class="mb-0 text-success">PhilHealth</h6>
                                                            <span class="badge badge-subtle-success text-success">5% / 50-50</span>
                                                        </div>

                                                        <div class="row g-3 small">
                                                            <div class="col-12">
                                                                <div class="benefits-detail-label">PhilHealth No.</div>
                                                                <div class="benefits-detail-value benefits-id">{{ $identifiers['philhealth'] ?: 'Not encoded' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="benefits-detail-label">Monthly Basic Salary</div>
                                                                <div class="benefits-detail-value">{{ $money($summary['philhealth_basis']) }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="benefits-detail-label">Premium Salary Base</div>
                                                                <div class="benefits-detail-value">{{ $money($summary['philhealth_salary_base']) }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="benefits-detail-label">Employee Share</div>
                                                                <div class="benefits-detail-value">{{ $money($summary['philhealth_employee']) }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="benefits-detail-label">Company Share</div>
                                                                <div class="benefits-detail-value">{{ $money($summary['philhealth_employer']) }}</div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="alert alert-success py-2 mb-0 d-flex justify-content-between">
                                                                    <span>PhilHealth Combined</span>
                                                                    <strong>{{ $money($summary['philhealth_total']) }}</strong>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-4">
                                                    <div class="benefits-detail-card p-3">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <h6 class="mb-0 text-warning">Pag-IBIG / HDMF</h6>
                                                            <span class="badge badge-subtle-warning text-warning">Circular 460</span>
                                                        </div>

                                                        <div class="row g-3 small">
                                                            <div class="col-12">
                                                                <div class="benefits-detail-label">Pag-IBIG No.</div>
                                                                <div class="benefits-detail-value benefits-id">{{ $identifiers['pagibig'] ?: 'Not encoded' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="benefits-detail-label">Monthly Basis</div>
                                                                <div class="benefits-detail-value">{{ $money($summary['pagibig_basis']) }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="benefits-detail-label">Fund Salary</div>
                                                                <div class="benefits-detail-value">{{ $money($summary['pagibig_fund_salary']) }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="benefits-detail-label">Employee Share</div>
                                                                <div class="benefits-detail-value">
                                                                    {{ $money($summary['pagibig_employee']) }}
                                                                    <span class="text-muted">({{ number_format($summary['pagibig_employee_rate'] * 100, 0) }}%)</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="benefits-detail-label">Company Share</div>
                                                                <div class="benefits-detail-value">
                                                                    {{ $money($summary['pagibig_employer']) }}
                                                                    <span class="text-muted">({{ number_format($summary['pagibig_employer_rate'] * 100, 0) }}%)</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="alert alert-warning py-2 mb-0 d-flex justify-content-between">
                                                                    <span>Pag-IBIG Combined</span>
                                                                    <strong>{{ $money($summary['pagibig_total']) }}</strong>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                                <div class="mt-3 small text-muted">
                                                    <strong>Finalized payroll source:</strong>
                                                @if ($summary['payroll_numbers'] !== [])
                                                    {{ implode(', ', $summary['payroll_numbers']) }}
                                                @else
                                                        No finalized payroll record for {{ $periodLabel }}.
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5 text-muted">
                                            <i class="fas fa-users-slash fa-2x mb-2 d-block"></i>
                                            No active employees match the current filters.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            @if ($employees->count() > 0)
                                <tfoot class="bg-body-tertiary fw-bold">
                                    <tr>
                                        <td class="ps-3" colspan="2">Filtered Period Totals</td>
                                        <td class="text-end">{{ $money($totals->sss_employee ?? 0) }}</td>
                                        <td class="text-end">{{ $money($totals->sss_employer ?? 0) }}</td>
                                        <td class="text-end">{{ $money($totals->philhealth_employee ?? 0) }} / {{ $money($totals->philhealth_employer ?? 0) }}</td>
                                        <td class="text-end">{{ $money($totals->pagibig_employee ?? 0) }} / {{ $money($totals->pagibig_employer ?? 0) }}</td>
                                        <td class="text-end text-danger">{{ $money($totals->employee_total ?? 0) }}</td>
                                        <td class="text-end text-primary">{{ $money($totals->employer_total ?? 0) }}</td>
                                        <td class="text-end text-success">{{ $money($totals->grand_total ?? 0) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>

                @if ($employees->hasPages())
                    <div class="card-footer bg-white border-top d-flex justify-content-end">
                        {{ $employees->links('pagination.custom')}}
                    </div>
                @endif
            </div>

            <div class="alert alert-info border-0 shadow-sm">
                <div class="d-flex">
                    <i class="fas fa-info-circle mt-1 me-2"></i>
                    <div>
                        <strong>Posting rule:</strong> Benefits Records are not created from draft payrolls. The payroll can still show projected statutory deductions for review, but the official contribution ledger is written only inside the successful Finalize transaction.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
