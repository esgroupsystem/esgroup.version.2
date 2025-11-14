<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $employee->employee_id }} - 201</title>
    <style>
        body {
            font-family: DejaVu Sans, Helvetica, Arial;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile {
            display: flex;
            gap: 20px;
            margin-bottom: 10px;
        }

        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 8px;
            overflow: hidden;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .meta {
            flex: 1;
        }

        .section {
            margin-top: 12px;
        }

        .label {
            font-weight: 600;
        }

        .timeline {
            margin-top: 8px;
            border-left: 2px solid #ddd;
            padding-left: 10px;
        }

        .timeline-item {
            margin-bottom: 8px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>{{ $employee->full_name }} ({{ $employee->employee_id }})</h2>
        <div>{{ $employee->position->title ?? '-' }} • {{ $employee->department->name ?? '-' }}</div>
    </div>

    <div class="profile">

        @php
            $profilePath =
                $employee->asset && $employee->asset->profile_picture
                    ? asset('storage/' . $employee->asset->profile_picture)
                    : asset('assets/img/no-image-default.png');
        @endphp
        <div class="avatar">
            <img src="{{ $profilePath }}" style="width:120px; height:120px; object-fit:cover;">
        </div>
        <div class="meta">
            <div><span class="label">Status:</span> {{ $employee->status ?? 'Active' }}</div>
            <div><span class="label">Hired:</span> {{ optional($employee->date_hired)->format('M d, Y') ?? '-' }}</div>
            <div style="margin-top:8px;"><span class="label">Contact:</span> {{ $employee->email ?? '-' }} /
                {{ $employee->phone_number ?? '-' }}</div>
        </div>
    </div>

    <div class="section">
        <h4>Numbers</h4>
        <div><strong>SSS:</strong> {{ $employee->asset->sss_number ?? '-' }}</div>
        <div><strong>TIN:</strong> {{ $employee->asset->tin_number ?? '-' }}</div>
        <div><strong>PhilHealth:</strong> {{ $employee->asset->philhealth_number ?? '-' }}</div>
        <div><strong>Pag-IBIG:</strong> {{ $employee->asset->pagibig_number ?? '-' }}</div>
    </div>

    <div class="section">
        <h4>Employment History</h4>
        <div class="timeline">
            @foreach ($employee->histories as $h)
                <div class="timeline-item">
                    <div><strong>{{ $h->title }}</strong> ({{ optional($h->start_date)->format('M Y') ?? '-' }} -
                        {{ optional($h->end_date)->format('M Y') ?? 'Present' }})</div>
                    <div>{{ $h->description }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="section">
        <h4>Attachments</h4>
        <ul>
            @foreach ($employee->attachments as $a)
                <li>{{ $a->file_name }}</li>
            @endforeach
        </ul>
    </div>
</body>

</html>
