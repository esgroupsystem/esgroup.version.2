@extends('layouts.app')

@section('title', 'Benefits Overall')

@section('content')
    @php
        $amount = fn ($value) => number_format((float) $value, 2);
        $month = (int) data_get($filters, 'month', now('Asia/Manila')->month);
        $year = (int) data_get($filters, 'year', now('Asia/Manila')->year);
        $periodLabel = \Carbon\Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila')->format('F Y');
        $postedRows = collect($rows)->filter(fn ($row) => (bool) data_get($row, 'summary.posted'))->values();
        $reportQuery = array_filter([
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

            .benefits-register-wrap {
                border: 1px solid #111;
                background: #fff;
            }

            .benefits-register {
                min-width: 1780px;
                margin: 0;
                border-collapse: collapse;
                color: #111;
                font-variant-numeric: tabular-nums;
            }

            .benefits-register th,
            .benefits-register td {
                border: 1px solid #111 !important;
                padding: .45rem .5rem;
                vertical-align: middle;
            }

            .benefits-register thead th {
                text-align: center;
                font-weight: 800;
                white-space: nowrap;
            }

            .benefits-register .group-sss,
            .benefits-register .group-mpf,
            .benefits-register .group-sss-total {
                background: #fff200;
            }

            .benefits-register .group-phic {
                background: #92d050;
            }

            .benefits-register .group-hdmf {
                background: #9dc3e6;
            }

            .benefits-register .identity-head {
                background: #f2f2f2;
            }

            .benefits-register .employee-cell {
                min-width: 250px;
                font-weight: 700;
                white-space: nowrap;
            }

            .benefits-register .company-cell {
                min-width: 190px;
                white-space: nowrap;
            }

            .benefits-register .number-cell {
                min-width: 92px;
                text-align: right;
                white-space: nowrap;
            }

            .benefits-register tfoot td {
                background: #f2f2f2;
                font-weight: 800;
            }

            .benefits-period-badge {
                border: 1px solid var(--falcon-border-color, #d8e2ef);
                border-radius: .5rem;
                background: var(--falcon-gray-100, #f9fafd);
                padding: .55rem .8rem;
                font-weight: 700;
            }

            @media print {
                .benefits-no-print,
                .navbar,
                .navbar-vertical,
                .footer {
                    display: none !important;
                }

                .content,
                .container-fluid {
                    margin: 0 !important;
                    padding: 0 !important;
                    max-width: none !important;
                }

                .benefits-register {
                    min-width: 0;
                    width: 100%;
                    font-size: 9px;
                }
            }
        </style>
    @endonce

    <div class="container-fluid benefits-overall-page" data-layout="container">
        <div class="content">
            <div class="card border-0 shadow-sm mb-3 benefits-no-print">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                        <div>
                            <h4 class="mb-1 text-dark">
                                <i class="fas fa-table text-primary me-2"></i>
                                Benefits Overall
                            </h4>
                            <p class="mb-0 text-muted small">
                                Statutory contribution register arranged like the SSS/MPF contribution worksheet.
                            </p>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="benefits-period-badge">{{ $periodLabel }}</span>
                            <a href="{{ route('benefits-records.index', $reportQuery) }}" class="btn btn-falcon-default">
                                <i class="fas fa-users me-1"></i> Employee Records
                            </a>
                            <button type="button" class="btn btn-primary" onclick="window.print()">
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('benefits-records.overall') }}" class="row g-3 align-items-end">
                        <div class="col-md-4 col-xl-3">
                            <label for="search" class="form-label">Search employee/company</label>
                            <input type="text" class="form-control" id="search" name="search"
                                value="{{ data_get($filters, 'search') }}" placeholder="Employee, employee no., company">
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
                            <input type="number" class="form-control" id="year" name="year" min="2020" max="2100"
                                value="{{ $year }}">
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
                                <i class="fas fa-filter me-1"></i> Apply
                            </button>
                            <a href="{{ route('benefits-records.overall') }}" class="btn btn-falcon-default">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-2">
                        <div>
                            <h5 class="mb-1">Government Benefits Contribution Register</h5>
                            <div class="text-muted small">{{ $periodLabel }} · {{ number_format($postedRows->count()) }} posted employee record(s)</div>
                        </div>
                        <div class="text-muted small align-self-lg-end">
                            EE = Employee · ER = Employer · EC = Employees' Compensation
                        </div>
                    </div>
                </div>

                <div class="card-body p-0 benefits-register-wrap">
                    <div class="table-responsive">
                        <table class="table benefits-register">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="identity-head">Employee</th>
                                    <th rowspan="2" class="identity-head">Company</th>
                                    <th colspan="4" class="group-sss">SSS PREMIUM</th>
                                    <th colspan="3" class="group-mpf">MPF</th>
                                    <th rowspan="2" class="group-sss-total">TOTAL SSS/MPF</th>
                                    <th colspan="3" class="group-phic">PHILHEALTH</th>
                                    <th colspan="3" class="group-hdmf">PAG-IBIG</th>
                                </tr>
                                <tr>
                                    <th class="group-sss">EE</th>
                                    <th class="group-sss">ER</th>
                                    <th class="group-sss">EC</th>
                                    <th class="group-sss">TOTAL</th>
                                    <th class="group-mpf">EE</th>
                                    <th class="group-mpf">ER</th>
                                    <th class="group-mpf">TOTAL</th>
                                    <th class="group-phic">EE</th>
                                    <th class="group-phic">ER</th>
                                    <th class="group-phic">TOTAL</th>
                                    <th class="group-hdmf">EE</th>
                                    <th class="group-hdmf">ER</th>
                                    <th class="group-hdmf">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($postedRows as $row)
                                    @php
                                        $employee = $row['employee'];
                                        $summary = $row['summary'];
                                        $sssPremiumTotal = round(
                                            (float) $summary['sss_employee_regular_ss']
                                            + (float) $summary['sss_employer_regular_ss']
                                            + (float) $summary['sss_employer_ec'],
                                            2
                                        );
                                        $mpfTotal = round(
                                            (float) $summary['sss_employee_mpf']
                                            + (float) $summary['sss_employer_mpf'],
                                            2
                                        );
                                    @endphp
                                    <tr>
                                        <td class="employee-cell">
                                            {{ $employee->payroll_display_name }}
                                        </td>
                                        <td class="company-cell">{{ $row['company_name'] }}</td>
                                        <td class="number-cell">{{ $amount($summary['sss_employee_regular_ss']) }}</td>
                                        <td class="number-cell">{{ $amount($summary['sss_employer_regular_ss']) }}</td>
                                        <td class="number-cell">{{ $amount($summary['sss_employer_ec']) }}</td>
                                        <td class="number-cell fw-semibold">{{ $amount($sssPremiumTotal) }}</td>
                                        <td class="number-cell">{{ $amount($summary['sss_employee_mpf']) }}</td>
                                        <td class="number-cell">{{ $amount($summary['sss_employer_mpf']) }}</td>
                                        <td class="number-cell fw-semibold">{{ $amount($mpfTotal) }}</td>
                                        <td class="number-cell fw-bold">{{ $amount($summary['sss_total_contribution']) }}</td>
                                        <td class="number-cell">{{ $amount($summary['philhealth_employee']) }}</td>
                                        <td class="number-cell">{{ $amount($summary['philhealth_employer']) }}</td>
                                        <td class="number-cell fw-semibold">{{ $amount($summary['philhealth_total']) }}</td>
                                        <td class="number-cell">{{ $amount($summary['pagibig_employee']) }}</td>
                                        <td class="number-cell">{{ $amount($summary['pagibig_employer']) }}</td>
                                        <td class="number-cell fw-semibold">{{ $amount($summary['pagibig_total']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="16" class="text-center text-muted py-5">
                                            No posted Benefits Records match the selected month and filters.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            @if ($postedRows->isNotEmpty())
                                <tfoot>
                                    <tr>
                                        <td colspan="2" class="text-end">OVERALL</td>
                                        <td class="number-cell">{{ $amount($totals['sss_employee_regular_ss']) }}</td>
                                        <td class="number-cell">{{ $amount($totals['sss_employer_regular_ss']) }}</td>
                                        <td class="number-cell">{{ $amount($totals['sss_employer_ec']) }}</td>
                                        <td class="number-cell">{{ $amount($totals['sss_regular_total']) }}</td>
                                        <td class="number-cell">{{ $amount($totals['sss_employee_mpf']) }}</td>
                                        <td class="number-cell">{{ $amount($totals['sss_employer_mpf']) }}</td>
                                        <td class="number-cell">{{ $amount($totals['sss_mpf_total']) }}</td>
                                        <td class="number-cell">{{ $amount($totals['sss_total']) }}</td>
                                        <td class="number-cell">{{ $amount($totals['philhealth_employee']) }}</td>
                                        <td class="number-cell">{{ $amount($totals['philhealth_employer']) }}</td>
                                        <td class="number-cell">{{ $amount($totals['philhealth_total']) }}</td>
                                        <td class="number-cell">{{ $amount($totals['pagibig_employee']) }}</td>
                                        <td class="number-cell">{{ $amount($totals['pagibig_employer']) }}</td>
                                        <td class="number-cell">{{ $amount($totals['pagibig_total']) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
