<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Attendance History Report</h1>
        <p><strong>Intern:</strong> {{ $intern->user->name }}</p>
        <p><strong>Department:</strong> {{ $intern->department }}</p>
        <p><strong>Total Hours Rendered:</strong> {{ number_format($totalHours, 2) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Total Hours</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendance as $record)
            <tr>
                <td>{{ $record->work_date->format('Y-m-d') }}</td>
                <td>{{ $record->time_in ? $record->time_in->format('H:i:s') : '---' }}</td>
                <td>{{ $record->time_out ? $record->time_out->format('H:i:s') : '---' }}</td>
                <td>{{ $record->total_hours }}</td>
                <td>{{ $record->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>