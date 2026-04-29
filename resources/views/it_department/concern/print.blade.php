<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>CCTV Job Orders Print</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            color: #222;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
        }

        .meta {
            margin-bottom: 15px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f1f3f5;
            font-weight: bold;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
            vertical-align: top;
        }

        .badge {
            font-weight: bold;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="margin-bottom: 15px;">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>

    <div class="header">
        <h2>CCTV Job Orders Report</h2>
        <div>Status: {{ $status }}</div>
        <div>Generated: {{ now()->format('F d, Y h:i A') }}</div>
    </div>

    <div class="meta">
        <strong>Search:</strong> {{ $q ?: 'None' }} <br>
        <strong>Total Records:</strong> {{ $jobOrders->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Bus</th>
                <th>Reporter</th>
                <th>Issue</th>
                <th>Details</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($jobOrders as $jo)
                <tr>
                    <td>{{ $busDisplayMap[$jo->bus_no] ?? $jo->bus_no }}</td>
                    <td>{{ $jo->reported_by ?: '—' }}</td>
                    <td>{{ $jo->issue_type }}</td>
                    <td>{{ $jo->problem_details ?: '—' }}</td>
                    <td><span class="badge">{{ $jo->status }}</span></td>
                    <td>{{ optional($jo->created_at)->format('Y-m-d h:i A') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center;">No job orders found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>

</html>
