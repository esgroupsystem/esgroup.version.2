<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Job Order #{{ str_pad($job->id, 5, '0', STR_PAD_LEFT) }}
    </title>

    @php
        $formatDate = static function ($value, string $format = 'F d, Y'): string {
            if (blank($value)) {
                return 'N/A';
            }

            try {
                return \Carbon\Carbon::parse($value)->format($format);
            } catch (\Throwable $exception) {
                return (string) $value;
            }
        };

        $formatTime = static function ($value): string {
            if (blank($value)) {
                return 'N/A';
            }

            try {
                return \Carbon\Carbon::parse($value)->format('h:i A');
            } catch (\Throwable $exception) {
                return (string) $value;
            }
        };

        $jobOrderNumber = str_pad($job->id, 5, '0', STR_PAD_LEFT);

        $dateCreated = $job->job_date_filled ?: $job->created_at;

        $statusKey = strtolower(trim((string) $job->job_status));

        $statusClass = match ($statusKey) {
            'pending' => 'status-pending',
            'in progress' => 'status-progress',
            'completed' => 'status-completed',
            'cancelled', 'canceled' => 'status-cancelled',
            default => 'status-default',
        };
    @endphp

    <style>
        :root {
            --primary: #2c7be5;
            --primary-dark: #1c5cad;
            --primary-soft: #edf4fc;
            --dark: #263238;
            --text: #344050;
            --muted: #748194;
            --border: #d8e2ef;
            --surface: #f9fafd;
            --white: #ffffff;
            --success: #00864e;
            --success-soft: #d9f8eb;
            --warning: #c46600;
            --warning-soft: #fff3cd;
            --info: #1978a5;
            --info-soft: #dff4fb;
            --danger: #c02424;
            --danger-soft: #fde6e6;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            background: #eef1f5;
            color: var(--text);
            font-family:
                Arial,
                Helvetica,
                sans-serif;
            font-size: 12px;
            line-height: 1.45;
        }

        .print-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 20px;
        }

        .action-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 110px;
            padding: 10px 18px;
            border: 1px solid transparent;
            border-radius: 6px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .action-button-primary {
            background: var(--primary);
            color: var(--white);
        }

        .action-button-secondary {
            border-color: #b6c2d2;
            background: var(--white);
            color: var(--text);
        }

        .document {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 30px;
            padding: 13mm;
            background: var(--white);
            box-shadow: 0 8px 30px rgba(38, 50, 56, 0.12);
        }

        .document-header {
            position: relative;
            overflow: hidden;
            padding: 20px 22px;
            border: 1px solid var(--border);
            border-top: 5px solid var(--primary);
            border-radius: 8px;
            background:
                linear-gradient(135deg,
                    rgba(44, 123, 229, 0.1),
                    rgba(44, 123, 229, 0.015));
        }

        .header-decoration {
            position: absolute;
            top: -60px;
            right: -45px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(44, 123, 229, 0.07);
        }

        .header-content {
            position: relative;
            z-index: 1;
            display: table;
            width: 100%;
        }

        .header-left,
        .header-right {
            display: table-cell;
            vertical-align: middle;
        }

        .header-left {
            width: 68%;
        }

        .header-right {
            width: 32%;
            text-align: right;
        }

        .company-name {
            margin-bottom: 4px;
            color: var(--primary-dark);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .document-title {
            margin: 0;
            color: var(--dark);
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .document-subtitle {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: 11px;
        }

        .job-order-number {
            margin-bottom: 8px;
            color: var(--muted);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .job-order-number strong {
            display: block;
            margin-top: 2px;
            color: var(--dark);
            font-size: 19px;
            letter-spacing: 1px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 11px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .status-pending {
            background: var(--warning-soft);
            color: var(--warning);
        }

        .status-progress {
            background: var(--info-soft);
            color: var(--info);
        }

        .status-completed {
            background: var(--success-soft);
            color: var(--success);
        }

        .status-cancelled {
            background: var(--danger-soft);
            color: var(--danger);
        }

        .status-default {
            background: #edf0f5;
            color: var(--muted);
        }

        .summary-grid {
            display: table;
            width: 100%;
            margin-top: 14px;
            border-spacing: 8px 0;
            table-layout: fixed;
        }

        .summary-item {
            display: table-cell;
            width: 25%;
            padding: 11px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: var(--surface);
            vertical-align: top;
        }

        .summary-label {
            margin-bottom: 4px;
            color: var(--muted);
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .summary-value {
            color: var(--dark);
            font-size: 12px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .section {
            margin-top: 16px;
            page-break-inside: avoid;
        }

        .section-header {
            display: table;
            width: 100%;
            margin-bottom: 0;
            padding: 9px 12px;
            border: 1px solid var(--border);
            border-bottom: 0;
            border-radius: 6px 6px 0 0;
            background: var(--primary-soft);
        }

        .section-title,
        .section-description {
            display: table-cell;
            vertical-align: middle;
        }

        .section-title {
            color: var(--primary-dark);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .section-description {
            color: var(--muted);
            font-size: 9px;
            text-align: right;
        }

        .section-body {
            border: 1px solid var(--border);
            border-radius: 0 0 6px 6px;
            background: var(--white);
        }

        .information-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .information-table tr:not(:last-child) {
            border-bottom: 1px solid var(--border);
        }

        .information-table td {
            padding: 9px 12px;
            vertical-align: top;
        }

        .information-table .label {
            width: 22%;
            background: var(--surface);
            color: var(--muted);
            font-size: 10px;
            font-weight: 700;
        }

        .information-table .value {
            width: 28%;
            color: var(--dark);
            font-size: 11px;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .remarks-box {
            min-height: 75px;
            padding: 14px;
            color: var(--text);
            white-space: pre-line;
            overflow-wrap: anywhere;
        }

        .remarks-empty {
            color: var(--muted);
            font-style: italic;
        }

        .signature-section {
            margin-top: 35px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 20px 0;
            table-layout: fixed;
        }

        .signature-table td {
            width: 50%;
            padding: 0;
            vertical-align: top;
        }

        .signature-label {
            margin-bottom: 36px;
            color: var(--muted);
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .signature-line {
            border-top: 1px solid var(--dark);
            padding-top: 6px;
            text-align: center;
        }

        .signature-name {
            color: var(--dark);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .signature-position {
            margin-top: 2px;
            color: var(--muted);
            font-size: 9px;
        }

        .acknowledgment-section {
            margin-top: 25px;
            padding-top: 18px;
            border-top: 1px dashed var(--border);
            page-break-inside: avoid;
        }

        .acknowledgment-title {
            margin-bottom: 24px;
            color: var(--muted);
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.6px;
            text-align: center;
            text-transform: uppercase;
        }

        .acknowledgment-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 20px 0;
            table-layout: fixed;
        }

        .acknowledgment-table td {
            width: 33.333%;
            padding-top: 25px;
            border-top: 1px solid var(--dark);
            color: var(--muted);
            font-size: 9px;
            text-align: center;
        }

        .document-footer {
            display: table;
            width: 100%;
            margin-top: 28px;
            padding-top: 10px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: 8px;
        }

        .footer-left,
        .footer-right {
            display: table-cell;
            width: 50%;
            vertical-align: middle;
        }

        .footer-right {
            text-align: right;
        }

        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        @media print {

            html,
            body {
                width: 210mm;
                min-height: 297mm;
                background: #fff;
            }

            body {
                margin: 0;
                font-size: 11px;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .document {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .document-header,
            .summary-item,
            .section,
            .signature-section,
            .acknowledgment-section {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            a {
                color: inherit;
                text-decoration: none;
            }
        }

        @media screen and (max-width: 900px) {
            .document {
                width: calc(100% - 24px);
                min-height: auto;
                margin: 0 12px 25px;
                padding: 20px;
            }

            .header-left,
            .header-right {
                display: block;
                width: 100%;
                text-align: left;
            }

            .header-right {
                margin-top: 15px;
            }

            .summary-grid {
                display: block;
                border-spacing: 0;
            }

            .summary-item {
                display: block;
                width: 100%;
                margin-top: 8px;
            }
        }
    </style>
</head>

<body>
    {{-- Screen controls --}}
    <div class="print-actions no-print">
        <a href="{{ url()->previous() }}" class="action-button action-button-secondary">
            Back
        </a>

        <button type="button" class="action-button action-button-primary" onclick="window.print()">
            Print Job Order
        </button>
    </div>

    <main class="document">
        {{-- Header --}}
        <header class="document-header">
            <div class="header-decoration"></div>

            <div class="header-content">
                <div class="header-left">
                    <div class="company-name">
                        {{ config('app.name', 'Company Management System') }}
                    </div>

                    <h1 class="document-title">IT JOB ORDER</h1>

                    <p class="document-subtitle">
                        Internal service request and work assignment document
                    </p>
                </div>

                <div class="header-right">
                    <div class="job-order-number">
                        Job Order Number

                        <strong>#{{ $jobOrderNumber }}</strong>
                    </div>

                    <span class="status-badge {{ $statusClass }}">
                        {{ $job->job_status ?: 'Unknown' }}
                    </span>
                </div>
            </div>
        </header>

        {{-- Summary --}}
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Date Created</div>

                <div class="summary-value">
                    {{ $formatDate($dateCreated, 'M d, Y') }}
                </div>
            </div>

            <div class="summary-item">
                <div class="summary-label">Requester</div>

                <div class="summary-value">
                    {{ $job->job_creator ?: 'N/A' }}
                </div>
            </div>

            <div class="summary-item">
                <div class="summary-label">Assigned To</div>

                <div class="summary-value">
                    {{ $job->job_assign_person ?: 'Not assigned' }}
                </div>
            </div>

            <div class="summary-item">
                <div class="summary-label">Job Type</div>

                <div class="summary-value">
                    {{ $job->job_type ?: 'N/A' }}
                </div>
            </div>
        </div>

        {{-- Request information --}}
        <section class="section">
            <div class="section-header">
                <div class="section-title">Request Information</div>

                <div class="section-description">
                    General job-order information
                </div>
            </div>

            <div class="section-body">
                <table class="information-table">
                    <tr>
                        <td class="label">Job Order No.</td>
                        <td class="value">#{{ $jobOrderNumber }}</td>

                        <td class="label">Current Status</td>
                        <td class="value">
                            {{ $job->job_status ?: 'N/A' }}
                        </td>
                    </tr>

                    <tr>
                        <td class="label">Date Created</td>
                        <td class="value">
                            {{ $formatDate($dateCreated, 'F d, Y h:i A') }}
                        </td>

                        <td class="label">Requester</td>
                        <td class="value">
                            {{ $job->job_creator ?: 'N/A' }}
                        </td>
                    </tr>

                    <tr>
                        <td class="label">Assigned To</td>
                        <td class="value">
                            {{ $job->job_assign_person ?: 'Not assigned' }}
                        </td>

                        <td class="label">Direction</td>
                        <td class="value">
                            {{ $job->direction ?: 'N/A' }}
                        </td>
                    </tr>
                </table>
            </div>
        </section>

        {{-- Bus information --}}
        <section class="section">
            <div class="section-header">
                <div class="section-title">Bus Information</div>

                <div class="section-description">
                    Vehicle associated with the job order
                </div>
            </div>

            <div class="section-body">
                <table class="information-table">
                    <tr>
                        <td class="label">Bus Name</td>
                        <td class="value">
                            {{ optional($job->bus)->name ?: 'N/A' }}
                        </td>

                        <td class="label">Body Number</td>
                        <td class="value">
                            {{ optional($job->bus)->body_number ?: 'N/A' }}
                        </td>
                    </tr>

                    <tr>
                        <td class="label">Plate Number</td>
                        <td class="value">
                            {{ optional($job->bus)->plate_number ?: 'N/A' }}
                        </td>

                        <td class="label">Garage</td>
                        <td class="value">
                            {{ optional($job->bus)->garage ?: 'N/A' }}
                        </td>
                    </tr>
                </table>
            </div>
        </section>

        {{-- Job details --}}
        <section class="section">
            <div class="section-header">
                <div class="section-title">Job Details</div>

                <div class="section-description">
                    Incident schedule and assigned personnel
                </div>
            </div>

            <div class="section-body">
                <table class="information-table">
                    <tr>
                        <td class="label">Job Type</td>
                        <td class="value">
                            {{ $job->job_type ?: 'N/A' }}
                        </td>

                        <td class="label">Date Start</td>
                        <td class="value">
                            {{ $formatDate($job->job_datestart) }}
                        </td>
                    </tr>

                    <tr>
                        <td class="label">Start Time</td>
                        <td class="value">
                            {{ $formatTime($job->job_time_start) }}
                        </td>

                        <td class="label">End Time</td>
                        <td class="value">
                            {{ $formatTime($job->job_time_end) }}
                        </td>
                    </tr>

                    <tr>
                        <td class="label">Seat Number</td>
                        <td class="value">
                            {{ $job->job_sitNumber ?: 'N/A' }}
                        </td>

                        <td class="label">Direction</td>
                        <td class="value">
                            {{ $job->direction ?: 'N/A' }}
                        </td>
                    </tr>

                    <tr>
                        <td class="label">Driver</td>
                        <td class="value">
                            {{ $job->driver_name ?: 'N/A' }}
                        </td>

                        <td class="label">Conductor</td>
                        <td class="value">
                            {{ $job->conductor_name ?: 'N/A' }}
                        </td>
                    </tr>
                </table>
            </div>
        </section>

        {{-- Remarks --}}
        <section class="section">
            <div class="section-header">
                <div class="section-title">Remarks</div>

                <div class="section-description">
                    Findings and additional information
                </div>
            </div>

            <div class="section-body">
                <div class="remarks-box">
                    @if (filled($job->job_remarks))
                        {{ $job->job_remarks }}
                    @else
                        <span class="remarks-empty">
                            No remarks were provided for this job order.
                        </span>
                    @endif
                </div>
            </div>
        </section>

        {{-- Signatures --}}
        <section class="signature-section">
            <table class="signature-table">
                <tr>
                    <td>
                        <div class="signature-label">Prepared by</div>

                        <div class="signature-line">
                            <div class="signature-name">
                                {{ $job->job_creator ?: 'Requester' }}
                            </div>

                            <div class="signature-position">
                                Requesting Personnel
                            </div>
                        </div>
                    </td>

                    <td>
                        <div class="signature-label">Assigned to / Received by</div>

                        <div class="signature-line">
                            <div class="signature-name">
                                {{ $job->job_assign_person ?: 'Assigned Personnel' }}
                            </div>

                            <div class="signature-position">
                                IT Department
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </section>

        {{-- Approval and acknowledgment --}}
        <section class="acknowledgment-section">
            <div class="acknowledgment-title">
                Completion and approval acknowledgment
            </div>

            <table class="acknowledgment-table">
                <tr>
                    <td>Completed by / Signature</td>
                    <td>Approved by / Signature</td>
                    <td>Date Completed</td>
                </tr>
            </table>
        </section>

        {{-- Footer --}}
        <footer class="document-footer">
            <div class="footer-left">
                Internal document generated by {{ config('app.name') }}
            </div>

            <div class="footer-right">
                Printed:
                {{ now()->timezone(config('app.timezone', 'Asia/Manila'))->format('F d, Y h:i A') }}
            </div>
        </footer>
    </main>
</body>

</html>
