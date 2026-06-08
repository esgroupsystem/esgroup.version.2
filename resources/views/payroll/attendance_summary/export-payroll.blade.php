<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payroll Attendance Export</title>

    <style>
        @page {
            size: 8.5in 11in;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
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
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin: 5px 0 8px;
            font-size: 9px;
        }

        .employee-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
        }

        .employee-card {
            border: 1px solid #333;
            min-height: 310px;
            padding: 4px;
            page-break-inside: avoid;
        }

        .employee-name {
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            border-bottom: 1px solid #333;
            padding-bottom: 2px;
            margin-bottom: 2px;
        }

        .employee-meta {
            font-size: 8.5px;
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
            font-size: 8px;
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

        .mini-summary {
            margin-top: 3px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2px;
            font-size: 8px;
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
        /*
         * Safe fallback:
         * Controller should pass $summaryRows.
         * This prevents undefined variable error if older controller still passes $rows.
         */
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
                    <strong>Total Employees:</strong>
                    {{ number_format($employees->count()) }}
                </div>

                <div>
                    <strong>Total Records:</strong>
                    {{ number_format($exportRows->count()) }}
                </div>

                <div>
                    <strong>Present:</strong>
                    {{ number_format($stats['present'] ?? 0) }}
                </div>

                <div>
                    <strong>Half Day:</strong>
                    {{ number_format($stats['half_day'] ?? 0) }}
                </div>

                <div>
                    <strong>Late/UT:</strong>
                    {{ number_format($stats['late_undertime_records'] ?? 0) }}
                </div>

                <div>
                    <strong>Absent:</strong>
                    {{ number_format($stats['absent'] ?? 0) }}
                </div>

                <div>
                    <strong>Incomplete:</strong>
                    {{ number_format($stats['incomplete'] ?? 0) }}
                </div>
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
                                    <th style="width: 15%;">Day</th>
                                    <th style="width: 15%;">Date</th>
                                    <th style="width: 18%;">In</th>
                                    <th style="width: 18%;">Out</th>
                                    <th style="width: 16%;">Worked</th>
                                    <th style="width: 18%;">Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($employee['records'] as $record)
                                    @php
                                        $workedHours = ((int) $record->worked_minutes) / 60;
                                        $statusLabel = strtoupper(
                                            str_replace('_', ' ', $record->attendance_status ?? '—'),
                                        );
                                    @endphp

                                    <tr>
                                        <td>
                                            {{ optional($record->work_date)->format('D') }}
                                        </td>

                                        <td>
                                            {{ optional($record->work_date)->format('m/d') }}
                                        </td>

                                        <td>
                                            {{ $record->actual_time_in ? $record->actual_time_in->format('h:i A') : '—' }}
                                        </td>

                                        <td>
                                            {{ $record->actual_time_out ? $record->actual_time_out->format('h:i A') : '—' }}
                                        </td>

                                        <td>
                                            {{ number_format($workedHours, 2) }}
                                        </td>

                                        <td>
                                            {{ $statusLabel }}
                                        </td>
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
                                <strong>Half Day:</strong>
                                {{ number_format((int) ($employee['total_half_day_count'] ?? 0)) }}
                            </div>

                            <div>
                                <strong>Late:</strong>
                                {{ number_format((float) $employee['total_late_minutes'], 0) }} min
                            </div>

                            <div>
                                <strong>UT:</strong>
                                {{ number_format((float) $employee['total_undertime_minutes'], 0) }} min
                            </div>

                            <div>
                                <strong>Worked:</strong>
                                {{ number_format(((float) $employee['total_worked_minutes']) / 60, 2) }} hr
                            </div>

                            <div>
                                <strong>Payable:</strong>
                                {{ number_format((float) $employee['total_payable_days'], 2) }} day
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
