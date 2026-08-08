@extends('layouts.app')

@section('title', 'Benefits Overall Report')

@section('content')
    @php
        $money = fn($value) => 'PHP ' . number_format((float) $value, 2);
        $month = (int) data_get($filters, 'month', now('Asia/Manila')->month);
        $year = (int) data_get($filters, 'year', now('Asia/Manila')->year);
        $periodLabel = \Carbon\Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila')->format('F Y');
        $printQuery = array_filter([
            'month' => $month,
            'year' => $year,
            'search' => data_get($filters, 'search'),
            'garage_group' => data_get($filters, 'garage_group'),
        ], fn ($value) => $value !== null && $value !== '');
    @endphp

    @once
        <style>
            .benefits-overall-page {
                font-size: .875rem;
            }

            .benefits-stat-card,
            .benefits-summary-card {
                border: 1px solid var(--falcon-border-color, #d8e2ef);
                border-radius: .75rem;
                background: var(--falcon-card-bg, #fff);
                height: 100%;
            }

            .benefits-stat-card {
                padding: 1rem;
            }

            .benefits-stat-label {
                color: var(--falcon-600, #748194);
                font-size: .68rem;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
            }

            .benefits-stat-value {
                margin-top: .35rem;
                color: var(--falcon-900, #344050);
                font-size: 1.2rem;
                font-weight: 700;
                font-variant-numeric: tabular-nums;
            }

            .benefits-overall-table th {
                white-space: nowrap;
                font-size: .7rem;
                text-transform: uppercase;
                letter-spacing: .03em;
                vertical-align: middle;
            }

            .benefits-overall-table td {
                vertical-align: middle;
            }

            .benefits-money {
                white-space: nowrap;
                font-variant-numeric: tabular-nums;
            }

            .benefits-id {
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                font-size: .72rem;
                white-space: nowrap;
            }
        </style>
    @endonce

    <div class="container-fluid benefits-overall-page" data-layout="container">
        <div class="content">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                        <div>
                            <h4 class="mb-1 text-dark">
                                <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                                Benefits Overall Report
                            </h4>
                            <p class="mb-0 text-muted small">
                                Exact finalized SSS, PhilHealth, and Pag-IBIG contributions for all active payroll employees.
                            </p>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('benefits-records.index', $printQuery) }}" class="btn btn-falcon-default">
                                <i class="fas fa-users me-1"></i>
                                Employee Records
                            </a>
                            <a href="{{ route('benefits-records.print', $printQuery) }}" target="_blank"
                                rel="noopener" class="btn btn-primary">
                                <i class="fas fa-print me-1"></i>
                                Print Exact Report
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('benefits-records.overall') }}" class="row g-3 align-items-end">
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
                            <a href="{{ route('benefits-records.overall') }}" class="btn btn-falcon-default">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="benefits-stat-card">
                        <div class="benefits-stat-label">Report Period</div>
                        <div class="benefits-stat-value">{{ $periodLabel }}</div>
                        <div class="small text-muted">Finalized payroll contribution month</div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="benefits-stat-card">
                        <div class="benefits-stat-label">Active / Posted Employees</div>
                        <div class="benefits-stat-value">
                            {{ number_format($activeEmployeeCount) }} / {{ number_format($postedEmployeeCount) }}
                        </div>
                        <div class="small text-muted">
                            {{ number_format($notPostedEmployeeCount) }} employee(s) not yet posted
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="benefits-stat-card">
                        <div class="benefits-stat-label">Employee Contributions</div>
                        <div class="benefits-stat-value text-danger">{{ $money($totals['employee_total']) }}</div>
                        <div class="small text-muted">Total employee deductions</div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="benefits-stat-card">
                        <div class="benefits-stat-label">Company Contributions</div>
                        <div class="benefits-stat-value text-primary">{{ $money($totals['employer_total']) }}</div>
                        <div class="small text-muted">Employer share including SSS EC</div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-xl-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-body-tertiary border-bottom py-3">
                            <h5 class="mb-1">Government Contribution Summary</h5>
                            <div class="text-muted small">Employee share + company counterpart for {{ $periodLabel }}.</div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 benefits-overall-table">
                                    <thead class="bg-body-tertiary">
                                        <tr>
                                            <th class="ps-3">Program</th>
                                            <th class="text-end">Employee</th>
                                            <th class="text-end">Company</th>
                                            <th class="text-end pe-3">Combined</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-3 fw-semibold">SSS</td>
                                            <td class="text-end benefits-money">{{ $money($totals['sss_employee']) }}</td>
                                            <td class="text-end benefits-money">{{ $money($totals['sss_employer']) }}</td>
                                            <td class="text-end benefits-money fw-bold pe-3">{{ $money($totals['sss_total']) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3 fw-semibold">PhilHealth</td>
                                            <td class="text-end benefits-money">{{ $money($totals['philhealth_employee']) }}</td>
                                            <td class="text-end benefits-money">{{ $money($totals['philhealth_employer']) }}</td>
                                            <td class="text-end benefits-money fw-bold pe-3">{{ $money($totals['philhealth_total']) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3 fw-semibold">Pag-IBIG / HDMF</td>
                                            <td class="text-end benefits-money">{{ $money($totals['pagibig_employee']) }}</td>
                                            <td class="text-end benefits-money">{{ $money($totals['pagibig_employer']) }}</td>
                                            <td class="text-end benefits-money fw-bold pe-3">{{ $money($totals['pagibig_total']) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-body-tertiary fw-bold">
                                        <tr>
                                            <td class="ps-3">OVERALL</td>
                                            <td class="text-end text-danger">{{ $money($totals['employee_total']) }}</td>
                                            <td class="text-end text-primary">{{ $money($totals['employer_total']) }}</td>
                                            <td class="text-end text-success pe-3">{{ $money($totals['grand_total']) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-body-tertiary border-bottom py-3">
                            <h5 class="mb-1">Company Totals</h5>
                            <div class="text-muted small">Contribution snapshot grouped by employee company.</div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 benefits-overall-table">
                                    <thead class="bg-body-tertiary">
                                        <tr>
                                            <th class="ps-3">Company</th>
                                            <th class="text-center">Employees</th>
                                            <th class="text-end pe-3">Combined</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($companyTotals as $company)
                                            <tr>
                                                <td class="ps-3 fw-semibold">{{ $company['company_name'] }}</td>
                                                <td class="text-center">{{ number_format($company['employee_count']) }}</td>
                                                <td class="text-end benefits-money pe-3">
                                                    {{ $money($company['totals']['grand_total']) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">
                                                    No finalized contribution records for this period.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2">
                        <div>
                            <h5 class="mb-1">Exact Employee Contribution Register</h5>
                            <div class="text-muted small">
                                All active employees are shown. Amounts are sourced only from finalized payroll contribution records.
                            </div>
                        </div>
                        <span class="badge badge-subtle-primary text-primary px-3 py-2">
                            {{ number_format($postedEmployeeCount) }} posted / {{ number_format($activeEmployeeCount) }} active
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 benefits-overall-table">
                            <thead class="bg-body-tertiary">
                                <tr>
                                    <th class="ps-3">Employee</th>
                                    <th>Company</th>
                                    <th>Government IDs</th>
                                    <th class="text-end">SSS EE</th>
                                    <th class="text-end">SSS ER</th>
                                    <th class="text-end">PHIC EE</th>
                                    <th class="text-end">PHIC ER</th>
                                    <th class="text-end">HDMF EE</th>
                                    <th class="text-end">HDMF ER</th>
                                    <th class="text-end">Employee Total</th>
                                    <th class="text-end">Company Total</th>
                                    <th class="text-end">Combined</th>
                                    <th class="text-end pe-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $row)
                                    @php
                                        $employee = $row['employee'];
                                        $summary = $row['summary'];
                                        $ids = $row['identifiers'];
                                    @endphp
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark">{{ $employee->effective_name }}</div>
                                            <div class="text-muted small">{{ $employee->effective_employee_no ?: 'No employee no.' }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $row['company_name'] }}</div>
                                            <div class="text-muted small">{{ $employee->payroll_group_label }}</div>
                                        </td>
                                        <td>
                                            <div class="benefits-id">SSS: {{ $ids['sss'] ?: 'Not encoded' }}</div>
                                            <div class="benefits-id">PHIC: {{ $ids['philhealth'] ?: 'Not encoded' }}</div>
                                            <div class="benefits-id">HDMF: {{ $ids['pagibig'] ?: 'Not encoded' }}</div>
                                        </td>
                                        <td class="text-end benefits-money">{{ $money($summary['sss_employee_total']) }}</td>
                                        <td class="text-end benefits-money">{{ $money($summary['sss_employer_total']) }}</td>
                                        <td class="text-end benefits-money">{{ $money($summary['philhealth_employee']) }}</td>
                                        <td class="text-end benefits-money">{{ $money($summary['philhealth_employer']) }}</td>
                                        <td class="text-end benefits-money">{{ $money($summary['pagibig_employee']) }}</td>
                                        <td class="text-end benefits-money">{{ $money($summary['pagibig_employer']) }}</td>
                                        <td class="text-end benefits-money text-danger fw-semibold">{{ $money($summary['employee_total']) }}</td>
                                        <td class="text-end benefits-money text-primary fw-semibold">{{ $money($summary['employer_total']) }}</td>
                                        <td class="text-end benefits-money text-success fw-bold">{{ $money($summary['grand_total']) }}</td>
                                        <td class="text-end pe-3">
                                            @if ($summary['posted'])
                                                <span class="badge badge-subtle-success text-success">Posted</span>
                                            @else
                                                <span class="badge badge-subtle-warning text-warning">Not Posted</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center text-muted py-5">
                                            No active employees match the selected filters.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($rows->isNotEmpty())
                                <tfoot class="bg-body-tertiary fw-bold">
                                    <tr>
                                        <td colspan="3" class="ps-3">OVERALL TOTAL</td>
                                        <td class="text-end">{{ $money($totals['sss_employee']) }}</td>
                                        <td class="text-end">{{ $money($totals['sss_employer']) }}</td>
                                        <td class="text-end">{{ $money($totals['philhealth_employee']) }}</td>
                                        <td class="text-end">{{ $money($totals['philhealth_employer']) }}</td>
                                        <td class="text-end">{{ $money($totals['pagibig_employee']) }}</td>
                                        <td class="text-end">{{ $money($totals['pagibig_employer']) }}</td>
                                        <td class="text-end text-danger">{{ $money($totals['employee_total']) }}</td>
                                        <td class="text-end text-primary">{{ $money($totals['employer_total']) }}</td>
                                        <td class="text-end text-success">{{ $money($totals['grand_total']) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <div class="alert alert-info border-0 shadow-sm mb-0">
                <div class="d-flex gap-2">
                    <i class="fas fa-info-circle mt-1"></i>
                    <div>
                        <strong>Exact monthly contribution rule:</strong> Benefits Records are posted only after both cutoffs for the contribution month are finalized.
                        SSS uses the combined gross of the 1st cutoff (26-10) and 2nd cutoff (11-25), then applies the exact SSS Circular 2024-006 MSC / Regular SS / MPF / EC bracket.
                        The report reads that finalized monthly snapshot and does not recalculate values while viewing or printing.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
