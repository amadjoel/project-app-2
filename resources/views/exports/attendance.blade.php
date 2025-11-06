<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>Attendance Export</h1>
    <p>Date: {{ now()->format('M d, Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Student</th>
                <th>Status</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($r->date)->format('Y-m-d') }}</td>
                    <td>{{ $r->student->name ?? '—' }}</td>
                    <td>{{ ucfirst($r->status) }}</td>
                    <td>{{ $r->check_in_time ? \Carbon\Carbon::parse($r->check_in_time)->format('H:i') : '—' }}</td>
                    <td>{{ $r->check_out_time ? \Carbon\Carbon::parse($r->check_out_time)->format('H:i') : '—' }}</td>
                    <td>{{ $r->notes }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
