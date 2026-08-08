<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benefits Contribution Register - {{ \Carbon\Carbon::create((int) $filters['year'], (int) $filters['month'], 1)->format('F Y') }}</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #172b4d;
            background: #eef2f6;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            line-height: 1.35;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            background: #fff;
            border-bottom: 1px solid #d8e2ef;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        }

        .toolbar-actions {
            display: flex;
            gap: 8px;
        }

        .btn {
            display: inline-block;
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            color: #344050;
            background: #fff;
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-primary {
            color: #fff;
            background: #2c7be5;
            border-color: #2c7be5;
        }

        .report {
            width: min(100%, 1500px);
            margin: 14px auto;
            padding: 12mm;
            background: #fff;
            box-shadow: 0 2px 16px rgba(0, 0, 0, .08);
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            padding-bottom: 12px;
            border-bottom: 3px solid #1f5aa6;
        }

        .report-title {
            margin: 0;
            color: #1f5aa6;
            font-size: 21px;
            letter-spacing: .02em;
        }

        .report-subtitle {
            margin-top: 3px;
            color: #53677f;
            font-size: 11px;
        }

        .header-meta {
            min-width: 310px;
            text-align: right;
        }

        .header-meta div {
            margin-bottom: 2px;
        }

        .section {
            margin-top: 14px;
        }

        .section-title {
            margin: 0 0 6px;
            padding: 7px 9px;
            color: #fff;
            background: #1f5aa6;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .section-title.phic {
            background: #166534;
        }

        .section-title.hdmf {
            background: #9a6700;
        }

        .section-title.overall {
            background: #344050;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-top: 12px;
        }

        .summary-box {
            padding: 9px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
        }

        .summary-label {
            color: #64748b;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .summary-value {
            margin-top: 3px;
            color: #172b4d;
            font-size: 14px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        th,
        td {
            padding: 4px 5px;
            border: 1px solid #aebbd0;
            vertical-align: middle;
        }

        th {
            color: #fff;
            background: #2b67b1;
            font-size: 7px;
            line-height: 1.2;
            text-transform: uppercase;
            text-align: center;
        }

        .phic-table th {
            background: #207a47;
        }

        .hdmf-table th {
            background: #aa7600;
        }

        .overall-table th {
            background: #4a5568;
        }

        td {
            color: #1f2937;
            font-size: 7.5px;
        }

        tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        tfoot td {
            font-weight: 700;
            background: #eef3f9;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .money {
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .employee-name {
            font-weight: 700;
        }

        .muted {
            color: #64748b;
        }

        .id-value {
            white-space: nowrap;
            font-family: "Courier New", monospace;
            font-size: 7px;
        }

        .status-posted {
            color: #166534;
            font-weight: 700;
        }

        .status-pending {
            color: #92400e;
            font-weight: 700;
        }

        .report-note {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #cbd5e1;
            color: #64748b;
            font-size: 8px;
        }

        .signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 50px;
            margin-top: 26px;
        }

        .signature-line {
            padding-top: 22px;
            border-bottom: 1px solid #334155;
        }

        .signature-label {
            margin-top: 4px;
            text-align: center;
            color: #64748b;
            font-size: 8px;
        }

        .page-break {
            break-before: page;
            page-break-before: always;
        }

        @media print {
            body {
                background: #fff;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .report {
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    @php
        $money = fn($value) => 'PHP ' . number_format((float) $value, 2);
        $month = (int) $filters['month'];
        $year = (int) $filters['year'];
        $periodLabel = \Carbon\Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila')->format('F Y');
        $overallQuery = array_filter([
            'month' => $month,
            'year' => $year,
            'search' => data_get($filters, 'search'),
            'garage_group' => data_get($filters, 'garage_group'),
        ], fn ($value) => $value !== null && $value !== '');
    @endphp

    <div class="toolbar no-print">
        <div>
            <strong>Benefits Contribution Register</strong>
            <span class="muted">&mdash; {{ $periodLabel }}</span>
        </div>
        <div class="toolbar-actions">
            <a href="{{ route('benefits-records.overall', $overallQuery) }}" class="btn">Back to Overall</a>
            <button type="button" class="btn btn-primary" onclick="window.print()">Print Report</button>
        </div>
    </div>

    <main class="report">
        <header class="report-header">
            <div>
                <h1 class="report-title">JELL GROUP</h1>
                <div class="report-subtitle">Benefits Contribution Register</div>
                <div class="report-subtitle">SSS &bull; PhilHealth &bull; Pag-IBIG / HDMF</div>
            </div>
            <div class="header-meta">
                <div><strong>Contribution Month:</strong> {{ $periodLabel }}</div>
                <div><strong>Active Employees:</strong> {{ number_format($activeEmployeeCount) }}</div>
                <div><strong>Posted Employees:</strong> {{ number_format($postedEmployeeCount) }}</div>
                <div><strong>Generated:</strong> {{ now('Asia/Manila')->format('F d, Y h:i A') }}</div>
            </div>
        </header>

        <div class="summary-grid">
            <div class="summary-box">
                <div class="summary-label">Employee Contribution</div>
                <div class="summary-value">{{ $money($totals['employee_total']) }}</div>
            </div>
            <div class="summary-box">
                <div class="summary-label">Company Contribution</div>
                <div class="summary-value">{{ $money($totals['employer_total']) }}</div>
            </div>
            <div class="summary-box">
                <div class="summary-label">Combined Contribution</div>
                <div class="summary-value">{{ $money($totals['grand_total']) }}</div>
            </div>
            <div class="summary-box">
                <div class="summary-label">Not Yet Posted</div>
                <div class="summary-value">{{ number_format($notPostedEmployeeCount) }}</div>
            </div>
        </div>

        <section class="section">
            <h2 class="section-title overall">Overall Government Contribution Summary</h2>
            <table class="overall-table">
                <thead>
                    <tr>
                        <th>Program</th>
                        <th>Employee Share</th>
                        <th>Company Share</th>
                        <th>Combined Contribution</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>SSS</strong></td>
                        <td class="text-end money">{{ $money($totals['sss_employee']) }}</td>
                        <td class="text-end money">{{ $money($totals['sss_employer']) }}</td>
                        <td class="text-end money"><strong>{{ $money($totals['sss_total']) }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>PhilHealth</strong></td>
                        <td class="text-end money">{{ $money($totals['philhealth_employee']) }}</td>
                        <td class="text-end money">{{ $money($totals['philhealth_employer']) }}</td>
                        <td class="text-end money"><strong>{{ $money($totals['philhealth_total']) }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Pag-IBIG / HDMF</strong></td>
                        <td class="text-end money">{{ $money($totals['pagibig_employee']) }}</td>
                        <td class="text-end money">{{ $money($totals['pagibig_employer']) }}</td>
                        <td class="text-end money"><strong>{{ $money($totals['pagibig_total']) }}</strong></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td>OVERALL</td>
                        <td class="text-end money">{{ $money($totals['employee_total']) }}</td>
                        <td class="text-end money">{{ $money($totals['employer_total']) }}</td>
                        <td class="text-end money">{{ $money($totals['grand_total']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <section class="section">
            <h2 class="section-title">SSS Detailed Contribution Register</h2>
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Company</th>
                        <th>SSS No.</th>
                        <th>1st Gross<br><span class="muted">26-10</span></th>
                        <th>2nd Gross<br><span class="muted">11-25</span></th>
                        <th>Monthly SSS Basis</th>
                        <th>MSC</th>
                        <th>Regular SS MSC</th>
                        <th>MPF MSC</th>
                        <th>EE Regular SS</th>
                        <th>EE MPF</th>
                        <th>EE Total</th>
                        <th>ER Regular SS</th>
                        <th>ER MPF</th>
                        <th>ER EC</th>
                        <th>ER Total</th>
                        <th>Combined</th>
                        <th>Status</th>
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
                            <td>
                                <div class="employee-name">{{ $employee->effective_name }}</div>
                                <div class="muted">{{ $employee->effective_employee_no ?: '-' }}</div>
                            </td>
                            <td>{{ $row['company_name'] }}</td>
                            <td class="id-value">{{ $ids['sss'] ?: '-' }}</td>
                            <td class="text-end money">{{ $money($summary['business_first_cutoff_gross']) }}</td>
                            <td class="text-end money">{{ $money($summary['business_second_cutoff_gross']) }}</td>
                            <td class="text-end money"><strong>{{ $money($summary['sss_compensation_basis']) }}</strong></td>
                            <td class="text-end money">{{ $money($summary['sss_msc']) }}</td>
                            <td class="text-end money">{{ $money($summary['sss_regular_ss_msc']) }}</td>
                            <td class="text-end money">{{ $money($summary['sss_mpf_msc']) }}</td>
                            <td class="text-end money">{{ $money($summary['sss_employee_regular_ss']) }}</td>
                            <td class="text-end money">{{ $money($summary['sss_employee_mpf']) }}</td>
                            <td class="text-end money">{{ $money($summary['sss_employee_total']) }}</td>
                            <td class="text-end money">{{ $money($summary['sss_employer_regular_ss']) }}</td>
                            <td class="text-end money">{{ $money($summary['sss_employer_mpf']) }}</td>
                            <td class="text-end money">{{ $money($summary['sss_employer_ec']) }}</td>
                            <td class="text-end money">{{ $money($summary['sss_employer_total']) }}</td>
                            <td class="text-end money">{{ $money($summary['sss_total_contribution']) }}</td>
                            <td class="text-center {{ $summary['posted'] ? 'status-posted' : 'status-pending' }}">
                                {{ $summary['posted'] ? 'POSTED' : 'NOT POSTED' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="18" class="text-center">No active employees found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="11">SSS TOTAL</td>
                        <td class="text-end money">{{ $money($totals['sss_employee']) }}</td>
                        <td colspan="3"></td>
                        <td class="text-end money">{{ $money($totals['sss_employer']) }}</td>
                        <td class="text-end money">{{ $money($totals['sss_total']) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <section class="section page-break">
            <h2 class="section-title phic">PhilHealth Detailed Contribution Register</h2>
            <table class="phic-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Company</th>
                        <th>PhilHealth No.</th>
                        <th>Monthly Basic Salary</th>
                        <th>Contribution Basis</th>
                        <th>Premium Salary Base</th>
                        <th>Employee Share</th>
                        <th>Company Share</th>
                        <th>Combined</th>
                        <th>Status</th>
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
                            <td>
                                <div class="employee-name">{{ $employee->effective_name }}</div>
                                <div class="muted">{{ $employee->effective_employee_no ?: '-' }}</div>
                            </td>
                            <td>{{ $row['company_name'] }}</td>
                            <td class="id-value">{{ $ids['philhealth'] ?: '-' }}</td>
                            <td class="text-end money">{{ $money($summary['monthly_basic_salary']) }}</td>
                            <td class="text-end money">{{ $money($summary['philhealth_basis']) }}</td>
                            <td class="text-end money">{{ $money($summary['philhealth_salary_base']) }}</td>
                            <td class="text-end money">{{ $money($summary['philhealth_employee']) }}</td>
                            <td class="text-end money">{{ $money($summary['philhealth_employer']) }}</td>
                            <td class="text-end money">{{ $money($summary['philhealth_total']) }}</td>
                            <td class="text-center {{ $summary['posted'] ? 'status-posted' : 'status-pending' }}">
                                {{ $summary['posted'] ? 'POSTED' : 'NOT POSTED' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center">No active employees found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6">PHILHEALTH TOTAL</td>
                        <td class="text-end money">{{ $money($totals['philhealth_employee']) }}</td>
                        <td class="text-end money">{{ $money($totals['philhealth_employer']) }}</td>
                        <td class="text-end money">{{ $money($totals['philhealth_total']) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <section class="section page-break">
            <h2 class="section-title hdmf">Pag-IBIG / HDMF Detailed Contribution Register</h2>
            <table class="hdmf-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Company</th>
                        <th>Pag-IBIG MID No.</th>
                        <th>Monthly Basis</th>
                        <th>Fund Salary</th>
                        <th>EE Rate</th>
                        <th>Employee Share</th>
                        <th>ER Rate</th>
                        <th>Company Share</th>
                        <th>Combined</th>
                        <th>Status</th>
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
                            <td>
                                <div class="employee-name">{{ $employee->effective_name }}</div>
                                <div class="muted">{{ $employee->effective_employee_no ?: '-' }}</div>
                            </td>
                            <td>{{ $row['company_name'] }}</td>
                            <td class="id-value">{{ $ids['pagibig'] ?: '-' }}</td>
                            <td class="text-end money">{{ $money($summary['pagibig_basis']) }}</td>
                            <td class="text-end money">{{ $money($summary['pagibig_fund_salary']) }}</td>
                            <td class="text-center">{{ number_format($summary['pagibig_employee_rate'] * 100, 2) }}%</td>
                            <td class="text-end money">{{ $money($summary['pagibig_employee']) }}</td>
                            <td class="text-center">{{ number_format($summary['pagibig_employer_rate'] * 100, 2) }}%</td>
                            <td class="text-end money">{{ $money($summary['pagibig_employer']) }}</td>
                            <td class="text-end money">{{ $money($summary['pagibig_total']) }}</td>
                            <td class="text-center {{ $summary['posted'] ? 'status-posted' : 'status-pending' }}">
                                {{ $summary['posted'] ? 'POSTED' : 'NOT POSTED' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center">No active employees found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6">PAG-IBIG / HDMF TOTAL</td>
                        <td class="text-end money">{{ $money($totals['pagibig_employee']) }}</td>
                        <td></td>
                        <td class="text-end money">{{ $money($totals['pagibig_employer']) }}</td>
                        <td class="text-end money">{{ $money($totals['pagibig_total']) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <section class="section page-break">
            <h2 class="section-title overall">Consolidated Employee / Company Contribution Register</h2>
            <table class="overall-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Company</th>
                        <th>SSS EE</th>
                        <th>SSS ER</th>
                        <th>PHIC EE</th>
                        <th>PHIC ER</th>
                        <th>HDMF EE</th>
                        <th>HDMF ER</th>
                        <th>Employee Total</th>
                        <th>Company Total</th>
                        <th>Grand Total</th>
                        <th>Payroll Source</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php
                            $employee = $row['employee'];
                            $summary = $row['summary'];
                        @endphp
                        <tr>
                            <td>
                                <div class="employee-name">{{ $employee->effective_name }}</div>
                                <div class="muted">{{ $employee->effective_employee_no ?: '-' }}</div>
                            </td>
                            <td>{{ $row['company_name'] }}</td>
                            <td class="text-end money">{{ $money($summary['sss_employee_total']) }}</td>
                            <td class="text-end money">{{ $money($summary['sss_employer_total']) }}</td>
                            <td class="text-end money">{{ $money($summary['philhealth_employee']) }}</td>
                            <td class="text-end money">{{ $money($summary['philhealth_employer']) }}</td>
                            <td class="text-end money">{{ $money($summary['pagibig_employee']) }}</td>
                            <td class="text-end money">{{ $money($summary['pagibig_employer']) }}</td>
                            <td class="text-end money">{{ $money($summary['employee_total']) }}</td>
                            <td class="text-end money">{{ $money($summary['employer_total']) }}</td>
                            <td class="text-end money"><strong>{{ $money($summary['grand_total']) }}</strong></td>
                            <td>{{ $summary['payroll_numbers'] !== [] ? implode(', ', $summary['payroll_numbers']) : '-' }}</td>
                            <td class="text-center {{ $summary['posted'] ? 'status-posted' : 'status-pending' }}">
                                {{ $summary['posted'] ? 'POSTED' : 'NOT POSTED' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="13" class="text-center">No active employees found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">OVERALL TOTAL</td>
                        <td class="text-end money">{{ $money($totals['sss_employee']) }}</td>
                        <td class="text-end money">{{ $money($totals['sss_employer']) }}</td>
                        <td class="text-end money">{{ $money($totals['philhealth_employee']) }}</td>
                        <td class="text-end money">{{ $money($totals['philhealth_employer']) }}</td>
                        <td class="text-end money">{{ $money($totals['pagibig_employee']) }}</td>
                        <td class="text-end money">{{ $money($totals['pagibig_employer']) }}</td>
                        <td class="text-end money">{{ $money($totals['employee_total']) }}</td>
                        <td class="text-end money">{{ $money($totals['employer_total']) }}</td>
                        <td class="text-end money">{{ $money($totals['grand_total']) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <section class="section">
            <h2 class="section-title overall">Company Contribution Totals</h2>
            <table class="overall-table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Posted Employees</th>
                        <th>Employee Share</th>
                        <th>Company Share</th>
                        <th>Combined Contribution</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companyTotals as $company)
                        <tr>
                            <td><strong>{{ $company['company_name'] }}</strong></td>
                            <td class="text-center">{{ number_format($company['employee_count']) }}</td>
                            <td class="text-end money">{{ $money($company['totals']['employee_total']) }}</td>
                            <td class="text-end money">{{ $money($company['totals']['employer_total']) }}</td>
                            <td class="text-end money"><strong>{{ $money($company['totals']['grand_total']) }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">No finalized contribution records for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <div class="signatures">
            <div>
                <div class="signature-line"></div>
                <div class="signature-label">Prepared By</div>
            </div>
            <div>
                <div class="signature-line"></div>
                <div class="signature-label">Checked By</div>
            </div>
            <div>
                <div class="signature-line"></div>
                <div class="signature-label">Approved By</div>
            </div>
        </div>

        <div class="report-note">
            Exact monthly contribution amounts are read from finalized Benefits Records. SSS compensation is the finalized 1st cutoff gross (26-10) plus finalized 2nd cutoff gross (11-25), then the official MSC / Regular SS / MPF / EC schedule is applied. This print view does not recalculate values while rendering.
        </div>
    </main>
</body>
</html>
