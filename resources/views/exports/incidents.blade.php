<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Incident Logs Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>Incident Logs Export</h1>
    <p>Date: {{ now()->format('M d, Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Student</th>
                <th>Type</th>
                <th>Severity</th>
                <th>Title</th>
                <th>Parent Notified</th>
                <th>Resolved</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($r->incident_date)->format('Y-m-d') }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->incident_time)->format('H:i') }}</td>
                    <td>{{ $r->student->name ?? '—' }}</td>
                    <td>{{ ucfirst($r->type) }}</td>
                    <td>{{ ucfirst($r->severity) }}</td>
                    <td>{{ $r->title }}</td>
                    <td>{{ $r->parent_notified ? 'Yes' : 'No' }}</td>
                    <td>{{ $r->resolved ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
