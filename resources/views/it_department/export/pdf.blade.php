<!DOCTYPE html>
<html>
<head>
    <title>Job Orders PDF Export</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background: #eee; }
        h2 { margin-bottom: 5px; }
    </style>
</head>

<body>

<h2>Job Orders Export</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Bus</th>
            <th>Job Type</th>
            <th>Status</th>
            <th>Date Filled</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $job)
            <tr>
                <td>{{ $job->id }}</td>
                <td>{{ $job->bus->name ?? 'N/A' }}</td>
                <td>{{ $job->job_type }}</td>
                <td>{{ $job->job_status }}</td>
                <td>{{ $job->job_date_filled }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
