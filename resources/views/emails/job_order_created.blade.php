@php($bus = $job->bus)
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8"/>
<title>Job Order Receipt</title>
</head>

<body style="margin:0; padding:0; background:#e9ecef; font-family:Arial, Helvetica, sans-serif;">

    <table width="100%" cellspacing="0" cellpadding="0" style="padding:20px 0;">
        <tr>
            <td align="center">

                <!-- Receipt Container -->
                <table width="420" cellspacing="0" cellpadding="0" 
                    style="background:#ffffff; border:1px solid #dcdcdc; border-radius:6px;">

                    <!-- Header -->
                    <tr>
                        <td style="text-align:center; padding:12px; background:#0a4fa3;">
                            <img src="{{ asset('assets/img/favicons/esgroup-logo180x180.png') }}" width="75" style="margin-bottom:6px;">
                            <h3 style="color:#ffffff; margin:0; font-size:16px;">Job Order Receipt</h3>
                            <p style="color:#e9e9e9; margin:3px 0 0; font-size:12px;">
                                #{{ $job->id }}
                            </p>
                        </td>
                    </tr>

                    <!-- Receipt Body -->
                    <tr>
                        <td style="padding:15px 18px; font-size:13px; color:#333;">

                            <!-- Job Summary Box -->
                            <table width="100%" cellspacing="0" cellpadding="0" 
                                style="border:1px solid #ccc; padding:10px; border-radius:4px; margin-bottom:12px;">
                                <tr>
                                    <td style="font-weight:bold;">Type:</td>
                                    <td style="text-align:right;">{{ $job->job_type }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;">Status:</td>
                                    <td style="text-align:right;">{{ $job->job_status }}</td>
                                </tr>
                            </table>

                            <!-- Bus Info -->
                            <h4 style="margin:8px 0 4px; font-size:14px; border-bottom:1px dashed #bbb;">Bus Details</h4>
                            <table width="100%" cellspacing="0" cellpadding="3" style="font-size:13px;">
                                @if($bus)
                                    <tr><td><strong>Name:</strong></td><td align="right">{{ $bus->name }}</td></tr>
                                    <tr><td><strong>Body #:</strong></td><td align="right">{{ $bus->body_number }}</td></tr>
                                    <tr><td><strong>Plate #:</strong></td><td align="right">{{ $bus->plate_number }}</td></tr>
                                @else
                                    <tr><td colspan="2"><em>Bus not linked</em></td></tr>
                                @endif
                            </table>

                            <!-- Incident -->
                            <h4 style="margin:12px 0 4px; font-size:14px; border-bottom:1px dashed #bbb;">Incident Info</h4>
                            <table width="100%" cellspacing="0" cellpadding="3" style="font-size:13px;">
                                <tr><td><strong>Date:</strong></td><td align="right">{{ $job->job_datestart }}</td></tr>
                                <tr><td><strong>Time:</strong></td><td align="right">{{ $job->job_time_start }} - {{ $job->job_time_end }}</td></tr>
                                <tr><td><strong>Direction:</strong></td><td align="right">{{ $job->direction }}</td></tr>

                                @if($job->driver_name)
                                <tr><td><strong>Driver:</strong></td><td align="right">{{ $job->driver_name }}</td></tr>
                                @endif

                                @if($job->conductor_name)
                                <tr><td><strong>Conductor:</strong></td><td align="right">{{ $job->conductor_name }}</td></tr>
                                @endif

                                @if($job->job_sitNumber)
                                <tr><td><strong>Seat #:</strong></td><td align="right">{{ $job->job_sitNumber }}</td></tr>
                                @endif
                            </table>

                            @if($job->job_remarks)
                                <h4 style="margin:12px 0 4px; font-size:14px; border-bottom:1px dashed #bbb;">Remarks</h4>
                                <p style="font-size:13px; margin:0 0 10px;">{{ $job->job_remarks }}</p>
                            @endif

                            <!-- Reporter -->
                            <h4 style="margin:12px 0 4px; font-size:14px; border-bottom:1px dashed #bbb;">Reported By</h4>
                            <table width="100%" cellspacing="0" cellpadding="3" style="font-size:13px;">
                                <tr><td><strong>Name:</strong></td><td align="right">{{ $job->job_creator }}</td></tr>
                                <tr><td><strong>Filed:</strong></td><td align="right">{{ $job->job_date_filled }}</td></tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#0a4fa3; text-align:center; padding:8px;">
                            <p style="font-size:11px; color:#e9e9e9; margin:0;">
                                ES Group IT Department · {{ date('Y') }}
                            </p>
                        </td>
                    </tr>

                </table>
                <!-- End Receipt -->

            </td>
        </tr>
    </table>

</body>
</html>
