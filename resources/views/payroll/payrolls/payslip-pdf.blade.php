<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $payroll->payroll_number }} Payslips</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            color: #111;
            margin: 0;
            background: #fff;
        }

        .page {
            page-break-after: always;
            padding: 0;
            width: 100%;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .report-header {
            text-align: center;
            border-bottom: 2px solid #111;
            padding-bottom: 5px;
            margin-bottom: 6px;
        }

        .report-header h1 {
            font-size: 15px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .report-header .sub {
            font-size: 10px;
            margin-top: 2px;
        }

        .summary-line {
            width: 100%;
            border-collapse: separate;
            border-spacing: 3px;
            margin: 4px 0 7px;
        }

        .summary-line td {
            border: 1px solid #777;
            padding: 3px;
            font-size: 7.5px;
            text-align: center;
            vertical-align: middle;
        }

        .summary-line strong {
            display: block;
            font-size: 7px;
            text-transform: uppercase;
        }

        .legend {
            border: 1px solid #111;
            padding: 4px;
            margin-bottom: 6px;
            font-size: 7.5px;
            line-height: 1.3;
        }

        .employee-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px;
        }

        .employee-grid td {
            width: 50%;
            vertical-align: top;
        }

        .employee-card {
            position: relative;
            border: 1px dashed #333;
            min-height: 0;
            height: auto;
            padding: 4px 4px 3px;
            page-break-inside: avoid;
            overflow: hidden;
        }

        .employee-card-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: .22;
            z-index: 0;
        }

        .employee-card-content {
            position: relative;
            z-index: 2;
        }

        .employee-company {
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            color: #555;
            letter-spacing: .25px;
            margin-bottom: 1px;
        }

        .employee-name {
            font-weight: bold;
            font-size: 9.5px;
            text-transform: uppercase;
            border-bottom: 1px solid #333;
            padding-bottom: 2px;
            margin-bottom: 2px;
            text-align: center;
        }

        .employee-meta {
            font-size: 7.5px;
            margin-bottom: 3px;
            width: 100%;
            border-collapse: collapse;
        }

        .employee-meta td {
            border: 0;
            padding: 1px 2px;
            width: 50%;
            text-align: left;
        }

        table.inner-table {
            width: 100%;
            border-collapse: collapse;
        }

        .inner-table th,
        .inner-table td {
            border: 1px solid #555;
            padding: 2px;
            font-size: 6.8px;
            line-height: 1.15;
            text-align: center;
            vertical-align: middle;
        }

        .inner-table th {
            background: #e9ecef;
            font-weight: bold;
        }

        .text-start {
            text-align: left !important;
        }

        .text-end {
            text-align: right !important;
        }

        .fw-bold {
            font-weight: bold;
        }

        .status-danger {
            background: #ffe5e9;
            font-weight: bold;
        }

        .status-warning {
            background: #fff3cd;
            font-weight: bold;
        }

        .status-paid {
            background: #e7f7ee;
            font-weight: bold;
        }

        .mini-summary {
            width: 100%;
            border-collapse: separate;
            border-spacing: 2px;
            margin-top: 3px;
        }

        .mini-summary td {
            border: 1px solid #777;
            padding: 2px;
            font-size: 7px;
            text-align: center;
            width: 33.33%;
        }

        .pay-table-wrap {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px;
        }

        .pay-table-wrap td {
            width: 50%;
            vertical-align: top;
            padding: 0;
            border: 0;
        }

        .pay-table-wrap td:first-child {
            padding-right: 2px;
        }

        .pay-table-wrap td:last-child {
            padding-left: 2px;
        }

        .money-table {
            width: 100%;
            border-collapse: collapse;
        }

        .money-table th,
        .money-table td {
            border: 1px solid #555;
            padding: 2px;
            font-size: 6.8px;
            line-height: 1.15;
        }

        .money-table th {
            background: #e9ecef;
            text-transform: uppercase;
            font-weight: bold;
            text-align: center;
        }

        .money-table .label {
            text-align: left;
            width: 54%;
        }

        .money-table .unit {
            text-align: right;
            width: 18%;
        }

        .money-table .amount {
            text-align: right;
            width: 28%;
            white-space: nowrap;
        }

        .total-row td {
            background: #e9ecef;
            font-weight: bold;
            font-size: 7.2px;
        }

        .net-row td {
            background: #dfe7f2;
            font-weight: bold;
            font-size: 8px;
        }

        .daily-title {
            margin-top: 4px;
            margin-bottom: 2px;
            font-weight: bold;
            font-size: 7.2px;
            text-transform: uppercase;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 4px;
        }

        .signature-table td {
            border: 0;
            padding: 1px 0;
            font-size: 7px;
            vertical-align: bottom;
        }

        .signature-label {
            width: 12%;
            white-space: nowrap;
            padding-right: 2px !important;
        }

        .received-signature-line {
            width: 50%;
        }

        .signature-spacer {
            width: 7%;
        }

        .date-label {
            width: 8%;
            white-space: nowrap;
            padding-right: 2px !important;
        }

        .date-signature-line {
            width: 23%;
        }

        .signature-line {
            border-bottom: 1px solid #111 !important;
            height: 10px;
        }

        .cut-note {
            text-align: right;
            font-size: 6px;
            color: #555;
            margin: 1px 0 0;
            line-height: 1;
            letter-spacing: .3px;
        }

        .print-date {
            font-size: 8px;
            text-align: right;
            margin-top: 6px;
        }
    </style>
</head>

<body>
    @php
        /*
         * Use PHP instead of the peso glyph because some PDF fonts/renderers
         * do not contain the ₱ character and display it as a question mark.
         */
        $currency = 'PHP';
        $money = fn($value) => number_format((float) $value, 2);
        $moneyWithCurrency = fn($value) => $currency . ' ' . $money($value);

        $backgroundPath = public_path('images/payroll/jell-payslip-bg.jpg');

        $backgroundImage = file_exists($backgroundPath)
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($backgroundPath))
            : null;

        $pageCount = $slipPages->count();
    @endphp

    @forelse ($slipPages as $pageIndex => $page)
        <section class="page">
            <div class="report-header">
                <h1>Employee Payslip Export</h1>
                <div class="sub">
                    Payroll: {{ $payroll->payroll_number }} |
                    Cutoff: {{ $payroll->cutoff_label }} |
                    Period: {{ $periodLabel }} |
                    Page {{ $pageIndex + 1 }} of {{ $pageCount }}
                </div>
            </div>

            <table class="summary-line">
                <tr>
                    <td>
                        <strong>Employees</strong>
                        {{ number_format($payroll->items->count()) }}
                    </td>

                    <td>
                        <strong>Gross Pay</strong>
                        {{ $moneyWithCurrency($payroll->items->sum('gross_pay')) }}
                    </td>

                    <td>
                        <strong>Net Pay</strong>
                        {{ $moneyWithCurrency($payroll->items->sum('net_pay')) }}
                    </td>

                    <td>
                        <strong>Deductions</strong>
                        {{ $moneyWithCurrency(
                            $payroll->items->sum('total_employee_government_deductions') + $payroll->items->sum('other_deductions'),
                        ) }}
                    </td>

                    <td>
                        <strong>SSS</strong>
                        {{ $moneyWithCurrency($payroll->items->sum('sss_employee')) }}
                    </td>

                    <td>
                        <strong>PhilHealth</strong>
                        {{ $moneyWithCurrency($payroll->items->sum('philhealth_employee')) }}
                    </td>

                    <td>
                        <strong>Pag-Ibig</strong>
                        {{ $moneyWithCurrency($payroll->items->sum('pagibig_employee')) }}
                    </td>

                    <td>
                        <strong>Period End</strong>
                        {{ $periodEnding }}
                    </td>
                </tr>
            </table>

            <div class="legend">
                <strong>Status Guide:</strong>
                Holiday = paid holiday. Hol Unpaid = holiday not paid. Double Pay = regular holiday worked.
                OB = official business. OFFSET = offset adjustment. ADJ = approved adjustment.
                Late/UT = late and undertime. Review = needs checking before final release.
            </div>

            <table class="employee-grid">
                @foreach ($page->chunk(2) as $row)
                    <tr>
                        @foreach ($row as $slip)
                            @php
                                $item = $slip['item'];
                                $attendanceRows = collect($slip['attendanceRows'] ?? []);
                            @endphp

                            <td>
                                <div class="employee-card">
                                    @if ($backgroundImage)
                                        <img class="employee-card-bg" src="{{ $backgroundImage }}" alt="">
                                    @endif

                                    <div class="employee-card-content">
                                        <div class="employee-company">
                                            {{ $slip['company_name'] ?: 'NO COMPANY / NOT TAGGED' }}
                                        </div>

                                        <div class="employee-name">
                                            {{ $slip['employee_name'] ?: 'NO NAME' }}
                                        </div>

                                        <table class="employee-meta">
                                            <tr>
                                                <td>
                                                    <strong>Emp No:</strong>
                                                    {{ $slip['employee_no'] ?: '—' }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <strong>Period Ending:</strong>
                                                    {{ $slip['period_ending'] }}
                                                </td>
                                            </tr>
                                        </table>

                                        <table class="pay-table-wrap">
                                            <tr>
                                                <td>
                                                    <table class="money-table">
                                                        <thead>
                                                            <tr>
                                                                <th colspan="3">Earnings</th>
                                                            </tr>
                                                        </thead>

                                                        <tbody>
                                                            @foreach ($slip['earnings'] as $earning)
                                                                <tr>
                                                                    <td class="label">{{ $earning['label'] }}</td>
                                                                    <td class="unit">{{ $earning['unit'] }}</td>
                                                                    <td class="amount">
                                                                        {{ $money($earning['amount']) }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach

                                                            <tr class="total-row">
                                                                <td colspan="2" class="text-end">Gross Pay</td>
                                                                <td class="amount">
                                                                    {{ $money($slip['gross_pay']) }}
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>

                                                <td>
                                                    <table class="money-table">
                                                        <thead>
                                                            <tr>
                                                                <th colspan="2">Deductions</th>
                                                            </tr>
                                                        </thead>

                                                        <tbody>
                                                            @foreach ($slip['deductions'] as $deduction)
                                                                <tr>
                                                                    <td class="label">{{ $deduction['label'] }}</td>
                                                                    <td class="amount">
                                                                        {{ $money($deduction['amount']) }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach

                                                            <tr class="total-row">
                                                                <td>Total Deductions</td>
                                                                <td class="amount">
                                                                    {{ $money($slip['total_deductions']) }}
                                                                </td>
                                                            </tr>

                                                            <tr class="net-row">
                                                                <td>Net Pay</td>
                                                                <td class="amount">
                                                                    {{ $moneyWithCurrency($slip['net_pay']) }}
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                        <table class="mini-summary">
                                            <tr>
                                                <td>
                                                    <strong>Absent:</strong>
                                                    {{ $slip['summary']['absent'] }}
                                                </td>

                                                <td>
                                                    <strong>Late/UT:</strong>
                                                    {{ $slip['summary']['late_minutes'] }}/{{ $slip['summary']['undertime_minutes'] }}
                                                    min
                                                </td>

                                                <td>
                                                    <strong>Holiday Paid:</strong>
                                                    {{ $slip['summary']['holiday_paid'] }}
                                                </td>
                                            </tr>
                                        </table>

                                        <table class="signature-table">
                                            <tr>
                                                <td class="signature-label">
                                                    <strong>RECEIVED:</strong>
                                                </td>
                                                <td class="signature-line received-signature-line"></td>
                                                <td class="signature-spacer"></td>
                                                <td class="date-label">
                                                    <strong>DATE:</strong>
                                                </td>
                                                <td class="signature-line date-signature-line"></td>
                                            </tr>
                                        </table>

                                        <div class="cut-note">CUT HERE</div>
                                    </div>
                                </div>
                            </td>
                        @endforeach

                        @if (collect($row)->count() === 1)
                            <td></td>
                        @endif
                    </tr>
                @endforeach
            </table>

            <div class="print-date">
                Printed: {{ $printedAt }}
            </div>
        </section>
    @empty
        <section class="page">
            <div class="report-header">
                <h1>Employee Payslip Export</h1>
                <div class="sub">No payslip records found.</div>
            </div>
        </section>
    @endforelse
</body>

</html>
