<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Order #{{ $job->id }}</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #000;
            margin: 30px;
        }

        h2 {
            margin-bottom: 5px;
        }

        hr {
            border: 0;
            border-top: 1px solid #000;
            margin: 10px 0 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 6px 4px;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 25%;
        }

        .section {
            margin-top: 20px;
        }

        .footer {
            margin-top: 40px;
            font-size: 12px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <h2>IT JOB ORDER</h2>
    <small>Job Order No: <strong>#{{ $job->id }}</strong></small>
    <hr>

    {{-- BASIC INFO --}}
    <table>
        <tr>
            <td class="label">Date Created:</td>
            <td>{{ \Carbon\Carbon::parse($job->job_date_filled)->format('F d, Y h:i A') }}</td>
        </tr>
        <tr>
            <td class="label">Status:</td>
            <td>{{ $job->job_status }}</td>
        </tr>
        <tr>
            <td class="label">Requester:</td>
            <td>{{ $job->job_creator }}</td>
        </tr>
        <tr>
            <td class="label">Assigned To:</td>
            <td>{{ $job->job_assign_person ?? '—' }}</td>
        </tr>
    </table>

    {{-- BUS INFO --}}
    <div class="section">
        <h4>Bus Information</h4>
        <hr>

        <table>
            <tr>
                <td class="label">Bus Name:</td>
                <td>{{ $job->bus->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Body Number:</td>
                <td>{{ $job->bus->body_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Plate Number:</td>
                <td>{{ $job->bus->plate_number ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    {{-- JOB DETAILS --}}
    <div class="section">
        <h4>Job Details</h4>
        <hr>

        <table>
            <tr>
                <td class="label">Job Type:</td>
                <td>{{ $job->job_type }}</td>
            </tr>
            <tr>
                <td class="label">Date Start:</td>
                <td>{{ $job->job_datestart }}</td>
            </tr>
            <tr>
                <td class="label">Time:</td>
                <td>{{ $job->job_time_start }} - {{ $job->job_time_end }}</td>
            </tr>
            <tr>
                <td class="label">Seat Number:</td>
                <td>{{ $job->job_sitNumber }}</td>
            </tr>
            <tr>
                <td class="label">Direction:</td>
                <td>{{ $job->direction ?? '—' }}</td>
            </tr>
        </table>
    </div>

    {{-- REMARKS --}}
    <div class="section">
        <h4>Remarks</h4>
        <hr>
        <p>{{ $job->job_remarks ?? 'No remarks provided.' }}</p>
    </div>

    {{-- SIGNATURE --}}
    <div class="footer">
        <table>
            <tr>
                <td width="50%">
                    Prepared by:<br><br>
                    ___________________________<br>
                    {{ $job->job_creator }}
                </td>
                <td width="50%">
                    Approved by:<br><br>
                    ___________________________<br>
                    IT Department
                </td>
            </tr>
        </table>
    </div>

    {{-- PRINT BUTTON --}}
    <div class="no-print" style="margin-top:20px;">
        <button onclick="window.print()">Print</button>
    </div>

</body>
</html>
