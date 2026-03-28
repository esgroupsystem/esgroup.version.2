<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $payroll->payroll_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 5px;
            vertical-align: top;
        }

        th {
            background: #f0f0f0;
        }

        h2,
        h4,
        p {
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="mb-20">
        <h2>Payroll Summary</h2>
        <p>{{ $payroll->payroll_number }}</p>
        <p>{{ $payroll->cutoff_label }}</p>
        <p>Status: {{ strtoupper($payroll->status) }}</p>
    </div>

    <table class="mb-20">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Payable Days</th>
                <th>Payable Hours</th>
                <th>Gross</th>
                <th>SSS</th>
                <th>PhilHealth</th>
                <th>Pag-IBIG</th>
                <th>Tax</th>
                <th>Net</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payroll->items as $item)
                <tr>
                    <td>{{ $item->employee_name }}<br>{{ $item->employee_no }}</td>
                    <td class="text-right">{{ number_format($item->total_payable_days, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total_payable_hours, 2) }}</td>
                    <td class="text-right">{{ number_format($item->gross_pay, 2) }}</td>
                    <td class="text-right">{{ number_format($item->sss_employee, 2) }}</td>
                    <td class="text-right">{{ number_format($item->philhealth_employee, 2) }}</td>
                    <td class="text-right">{{ number_format($item->pagibig_employee, 2) }}</td>
                    <td class="text-right">{{ number_format($item->withholding_tax, 2) }}</td>
                    <td class="text-right">{{ number_format($item->net_pay, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-right">TOTAL</th>
                <th class="text-right">{{ number_format($totals['gross_pay'], 2) }}</th>
                <th class="text-right">{{ number_format($totals['sss_employee'], 2) }}</th>
                <th class="text-right">{{ number_format($totals['philhealth_employee'], 2) }}</th>
                <th class="text-right">{{ number_format($totals['pagibig_employee'], 2) }}</th>
                <th class="text-right">{{ number_format($totals['withholding_tax'], 2) }}</th>
                <th class="text-right">{{ number_format($totals['net_pay'], 2) }}</th>
            </tr>
        </tfoot>
    </table>
</body>

</html>
