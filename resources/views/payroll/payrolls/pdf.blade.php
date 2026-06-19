<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $payroll->payroll_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #888; padding: 4px; vertical-align: top; }
        th { background: #eee; }
        .text-end { text-align: right; }
        .mb { margin-bottom: 12px; }
        h2, p { margin: 0; }
    </style>
</head>
<body>
    <div class="mb">
        <h2>Payroll Summary</h2>
        <p>{{ $payroll->payroll_number }} | {{ $payroll->cutoff_label }} | Contribution: {{ $payroll->contribution_label }}</p>
        <p>{{ optional($payroll->period_start)->format('M d, Y') }} - {{ optional($payroll->period_end)->format('M d, Y') }} | Status: {{ strtoupper($payroll->status) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th class="text-end">Days</th>
                <th class="text-end">Hours</th>
                <th class="text-end">Regular</th>
                <th class="text-end">Holiday</th>
                <th class="text-end">Rest/OT</th>
                <th class="text-end">Gross</th>
                <th class="text-end">Gov.</th>
                <th class="text-end">Other Ded.</th>
                <th class="text-end">Net</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payroll->items as $item)
                <tr>
                    <td>{{ $item->employee_name }}<br>{{ $item->employee_no }}</td>
                    <td class="text-end">{{ number_format($item->total_payable_days, 2) }}</td>
                    <td class="text-end">{{ number_format($item->total_payable_hours, 2) }}</td>
                    <td class="text-end">{{ number_format($item->regular_pay, 2) }}</td>
                    <td class="text-end">{{ number_format($item->holiday_pay, 2) }}</td>
                    <td class="text-end">{{ number_format($item->rest_day_pay + $item->overtime_pay, 2) }}</td>
                    <td class="text-end">{{ number_format($item->gross_pay, 2) }}</td>
                    <td class="text-end">{{ number_format($item->total_employee_government_deductions, 2) }}</td>
                    <td class="text-end">{{ number_format($item->other_deductions, 2) }}</td>
                    <td class="text-end">{{ number_format($item->net_pay, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">TOTAL</th>
                <th class="text-end">{{ number_format($totals['regular_pay'], 2) }}</th>
                <th class="text-end">{{ number_format($totals['holiday_pay'], 2) }}</th>
                <th class="text-end">{{ number_format($totals['rest_day_pay'] + $totals['overtime_pay'], 2) }}</th>
                <th class="text-end">{{ number_format($totals['gross_pay'], 2) }}</th>
                <th class="text-end">{{ number_format($totals['total_employee_government_deductions'], 2) }}</th>
                <th class="text-end">{{ number_format($totals['other_deductions'], 2) }}</th>
                <th class="text-end">{{ number_format($totals['net_pay'], 2) }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
