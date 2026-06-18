<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payroll Attendance Export</title>

    <style>
        @page {
            size: 8.5in 11in;
            margin: 9mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #111;
            margin: 0;
            background: #fff;
        }

        .toolbar {
            padding: 10px;
            background: #f2f4f7;
            border-bottom: 1px solid #ccc;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .toolbar button {
            background: #2563eb;
            color: #fff;
            border: 0;
            padding: 7px 14px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
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
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 3px;
            margin: 5px 0 8px;
            font-size: 8px;
        }

        .summary-line div {
            border: 1px solid #777;
            padding: 3px;
            min-height: 25px;
        }

        .legend {
            border: 1px solid #111;
            padding: 4px;
            margin-bottom: 6px;
            font-size: 8px;
            line-height: 1.3;
        }

        .employee-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5px;
        }

        .employee-card {
            border: 1px solid #333;
            min-height: 320px;
            padding: 4px;
            page-break-inside: avoid;
        }

        .employee-name {
            font-weight: bold;
            font-size: 9.5px;
            text-transform: uppercase;
            border-bottom: 1px solid #333;
            padding-bottom: 2px;
            margin-bottom: 2px;
        }

        .employee-meta {
            font-size: 8px;
            margin-bottom: 3px;
            display: flex;
            justify-content: space-between;
            gap: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #555;
            padding: 2px;
            font-size: 7px;
            line-height: 1.15;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background: #e9ecef;
            font-weight: bold;
        }

        .text-start {
            text-align: left;
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
            margin-top: 3px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2px;
            font-size: 7.5px;
        }

        .mini-summary div {
            border: 1px solid #777;
            padding: 2px;
        }

        .signature-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 10px;
            font-size: 9px;
        }

        .signature-box {
            text-align: center;
            padding-top: 18px;
        }

        .signature-line {
            border-top: 1px solid #111;
            padding-top: 2px;
            font-weight: bold;
        }

        .print-date {
            font-size: 8px;
            text-align: right;
            margin-top: 6px;
        }

        @media print {
            .toolbar {
                display: none;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    @php
        $exportRows = $summaryRows ?? ($rows ?? collect());
    @endphp

    <div class="toolbar">
        <div>
            <strong>Payroll Attendance Export</strong>
            <span>{{ $cutoffLabel }}</span>
        </div>

        <button onclick="window.print()">Print / Save as PDF</button>
    </div>

    @forelse ($employeePages as $pageIndex => $employeePage)
        <section class="page">
            <div class="report-header">
                <h1>Payroll Attendance Summary</h1>
                <div class="sub">
                    Cutoff: {{ $cutoffLabel }} |
                    Page {{ $pageIndex + 1 }} of {{ $employeePages->count() }}
                </div>
            </div>

            <div class="summary-line">
                <div>
                    <strong>Employees</strong><br>
                    {{ number_format($employees->count()) }}
                </div>

                <div>
                    <strong>Records</strong><br>
                    {{ number_format($exportRows->count()) }}
                </div>

                <div>
                    <strong>Pay Units</strong><br>
                    {{ number_format((float) ($stats['total_payable_days'] ?? 0), 2) }}
                </div>

                <div>
                    <strong>Needs Review</strong><br>
                    {{ number_format((int) ($stats['needs_review'] ?? 0)) }}
                </div>

                <div>
                    <strong>Holiday Paid</strong><br>
                    {{ number_format((int) ($stats['holiday_paid'] ?? 0)) }}
                </div>

                <div>
                    <strong>Holiday Unpaid</strong><br>
                    {{ number_format((int) ($stats['holiday_unpaid'] ?? 0)) }}
                </div>

                <div>
                    <strong>Rest Day Paid</strong><br>
                    {{ number_format((int) ($stats['rest_day_paid'] ?? 0)) }}
                </div>

                <div>
                    <strong>Adjustments</strong><br>
                    {{ number_format((int) ($stats['adjustment'] ?? 0)) }}
                </div>
            </div>

            <div class="legend">
                <strong>Payroll Rules:</strong>
                Rest day/day off is 100% paid. Holiday without work is paid only when before/after dates are qualified
                by biometrics, leave, adjustment, holiday, or plotted rest day. Regular holiday worked = 2.00 pay units.
                Special/non-regular holiday worked = 1.30 pay units. No Schedule, Incomplete Log, Half Day, Absent, and
                Unpaid Holiday must be checked before payroll.
            </div>

            <div class="employee-grid">
                @foreach ($employeePage as $employee)
                    <div class="employee-card">
                        <div class="employee-name">
                            {{ $employee['employee_name'] ?: 'NO NAME' }}
                        </div>

                        <div class="employee-meta">
                            <span>Emp No: {{ $employee['employee_no'] ?: '—' }}</span>
                            <span>Bio ID: {{ $employee['biometric_employee_id'] ?: '—' }}</span>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 12%;">Day</th>
                                    <th style="width: 13%;">Date</th>
                                    <th style="width: 15%;">In</th>
                                    <th style="width: 15%;">Out</th>
                                    <th style="width: 13%;">Work</th>
                                    <th style="width: 13%;">Pay</th>
                                    <th style="width: 19%;">Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($employee['records'] as $record)
                                    @php
                                        $workDate = $record->work_date
                                            ? \Carbon\Carbon::parse($record->work_date)
                                            : null;
                                        $actualIn = $record->actual_time_in
                                            ? \Carbon\Carbon::parse($record->actual_time_in)
                                            : null;
                                        $actualOut = $record->actual_time_out
                                            ? \Carbon\Carbon::parse($record->actual_time_out)
                                            : null;
                                        $workedHours = ((int) $record->worked_minutes) / 60;
                                        $statusLabel = strtoupper(
                                            str_replace('_', ' ', $record->attendance_status ?? '—'),
                                        );

                                        $rowClass = match ($record->attendance_status) {
                                            'holiday_unpaid',
                                            'no_schedule',
                                            'incomplete_log',
                                            'absent'
                                                => 'status-danger',
                                            'half_day', 'late', 'undertime', 'late_undertime' => 'status-warning',
                                            default => (float) $record->payable_days > 0 ? 'status-paid' : '',
                                        };
                                    @endphp

                                    <tr class="{{ $rowClass }}">
                                        <td>{{ $workDate ? $workDate->format('D') : '—' }}</td>
                                        <td>{{ $workDate ? $workDate->format('m/d') : '—' }}</td>
                                        <td>{{ $actualIn ? $actualIn->format('h:i A') : '—' }}</td>
                                        <td>{{ $actualOut ? $actualOut->format('h:i A') : '—' }}</td>
                                        <td>{{ number_format($workedHours, 2) }}</td>
                                        <td>{{ number_format((float) $record->payable_days, 2) }}</td>
                                        <td>{{ $statusLabel }}</td>
                                    </tr>
                                @endforeach

                                @for ($i = $employee['records']->count(); $i < 15; $i++)
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>

                        <div class="mini-summary">
                            <div>
                                <strong>Absent:</strong>
                                {{ number_format((int) ($employee['total_absent_count'] ?? 0)) }}
                            </div>

                            <div>
                                <strong>Review:</strong>
                                {{ number_format((int) ($employee['total_review_count'] ?? 0)) }}
                            </div>

                            <div>
                                <strong>Hol Paid:</strong>
                                {{ number_format((int) ($employee['total_holiday_paid_count'] ?? 0)) }}
                            </div>

                            <div>
                                <strong>Hol Unpaid:</strong>
                                {{ number_format((int) ($employee['total_holiday_unpaid_count'] ?? 0)) }}
                            </div>

                            <div>
                                <strong>Late/UT:</strong>
                                {{ number_format((float) $employee['total_late_minutes'], 0) }}/{{ number_format((float) $employee['total_undertime_minutes'], 0) }}
                                min
                            </div>

                            <div>
                                <strong>Pay Units:</strong>
                                {{ number_format((float) $employee['total_payable_days'], 2) }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="signature-row">
                <div class="signature-box">
                    <div class="signature-line">Prepared By</div>
                    <div>Payroll / HR Staff</div>
                </div>

                <div class="signature-box">
                    <div class="signature-line">Checked By</div>
                    <div>HR Supervisor</div>
                </div>

                <div class="signature-box">
                    <div class="signature-line">Approved By</div>
                    <div>Authorized Signatory</div>
                </div>
            </div>

            <div class="print-date">
                Printed: {{ now('Asia/Manila')->format('F d, Y h:i A') }}
            </div>
        </section>
    @empty
        <section class="page">
            <div class="report-header">
                <h1>Payroll Attendance Summary</h1>
                <div class="sub">No records found for {{ $cutoffLabel }}</div>
            </div>
        </section>
    @endforelse
</body>

</html>
