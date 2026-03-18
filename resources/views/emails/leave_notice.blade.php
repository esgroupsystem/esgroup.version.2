<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Official Leave Notification</title>
</head>

<body style="font-family: Arial, sans-serif; color:#333; line-height:1.7; font-size:16px;">

    @php
        use Carbon\Carbon;

        // Color logic
        $color = '#0d6efd';

        if (strtolower($noticeType) === 'final notice') {
            $color = '#dc3545'; // red
        } elseif (strtolower($noticeType) === 'warning') {
            $color = '#fd7e14'; // orange
        } elseif (strtolower($noticeType) === 'reminder' || strtolower($noticeType) === '2nd notice') {
            $color = '#198754'; // green
        }

        $today = Carbon::now('Asia/Manila')->startOfDay();
        $endDate = $leave->end_date ? Carbon::parse($leave->end_date, 'Asia/Manila')->startOfDay() : null;
        $daysNotReturned = 0;
        if ($endDate && $today->greaterThan($endDate)) {
            $daysNotReturned = $endDate->diffInDays($today);
        }
    @endphp

    <h2 style="margin-bottom:6px; color:{{ $color }}; font-size:24px;">
        Official Leave Notification
    </h2>

    <hr style="margin-bottom:20px;">

    <p>Dear HR Officer,</p>

    <p>
        This is to formally inform you that a leave record has been updated in the system.
    </p>

    <p>
        The employee,
        <strong style="font-size:17px;">{{ $leave->employee->full_name ?? 'N/A' }}</strong>,
        has filed a
        <strong>{{ $leave->leave_type }}</strong> leave scheduled from
        <strong>{{ optional($leave->start_date)->format('d F Y') }}</strong> to
        <strong>{{ optional($leave->end_date)->format('d F Y') }}</strong>,
        covering a total of
        <strong>{{ $leave->days }}</strong> day(s).
    </p>

    <p>
        This record falls under the category of
        <strong>{{ $category }}</strong>
        and is currently marked as
        <strong style="color:{{ $color }}; font-size:17px;">
            {{ $noticeType }}
        </strong>.
    </p>

    <p>
        Current status of the leave is
        <strong>{{ $leave->status ?? 'Pending' }}</strong>.
    </p>

    {{-- 🔥 DAYS NOT RETURNED SECTION --}}
    @if ($daysNotReturned > 0 && in_array(strtolower($noticeType), ['2nd notice', 'final notice']))
        <p>
            As of <strong>{{ $today->format('d F Y') }}</strong>,
            the employee has failed to report back to work for
            <strong style="color:#dc3545; font-size:17px;">
                {{ $daysNotReturned }} day(s)
            </strong>
            after the end of the approved leave period.
        </p>
    @endif

    @if (!empty($leave->last_action_note))
        <p>
            <strong>Remarks / Last Action:</strong>
            <em>{{ $leave->last_action_note }}</em>
        </p>
    @endif

    <p>
        You are advised to log in to the HR Management System to review and take the necessary action regarding this
        matter.
    </p>

    <p>
        Should you require further clarification, please coordinate with the concerned department.
    </p>

    <br>

    <p>Thank you.</p>

    <p style="margin-top:35px; font-size:15px;">
        <strong>Jell Group Management System</strong><br>
        <span style="font-size:13px; color:#777;">
            This is a system-generated notification. No signature is required.
        </span>
    </p>

</body>

</html>
