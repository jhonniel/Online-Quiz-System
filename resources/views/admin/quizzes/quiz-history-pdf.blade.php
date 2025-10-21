<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quiz History - {{ $quiz->title }}</title>
    <style>
        @page { margin: 18mm 12mm 14mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #111827; }
        header { position: fixed; top: -14mm; left: 0; right: 0; height: 12mm; border-bottom: 1px solid #E5E7EB; }
        footer { position: fixed; bottom: -10mm; left: 0; right: 0; height: 10mm; border-top: 1px solid #E5E7EB; color: #6B7280; font-size: 9px; }
        .header-inner, .footer-inner { width: 100%; padding: 4px 12mm; box-sizing: border-box; }
        .title { font-size: 14px; font-weight: bold; margin: 0; }
        .muted { color: #6B7280; font-size: 9.8px; }
        .table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .table th, .table td { border: 1px solid #E5E7EB; padding: 4px 6px; text-align: left; vertical-align: top; word-wrap: break-word; word-break: break-word; }
        .table th { background: #F3F4F6; font-weight: 600; font-size: 10px; }
        .right { text-align: right; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <header>
        <div class="header-inner">
            <div class="title">Quiz History</div>
            <div class="muted">{{ $quiz->title }} • Code: {{ $quiz->quiz_code }} • Total Questions: {{ $quiz->total_questions }}</div>
        </div>
    </header>
    <footer>
        <div class="footer-inner">Page <span class="page"></span> of <span class="topage"></span></div>
    </footer>

    <h2 style="margin-top: 10px; font-size: 13.5px;">Participants</h2>
    <table class="table">
        <thead>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th class="right">Attempts</th>
                <th class="right">Best Score</th>
                <th class="right">Total Score</th>
                <th class="right">Average Score</th>
                <th class="right">Average %</th>
                <th>Last Attempt</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td>{{ $row['user_name'] }}</td>
                    <td>{{ $row['user_email'] }}</td>
                    <td class="right">{{ $row['attempt_count'] }}</td>
                    <td class="right">{{ $row['best_score'] }} / {{ $quiz->total_questions }}</td>
                    <td class="right">{{ $row['total_score'] }}</td>
                    <td class="right">{{ $row['avg_correct'] }} / {{ $quiz->total_questions }}</td>
                    <td class="right">{{ $row['avg_percent'] }}%</td>
                    <td>{{ $row['last_attempt_at'] ? $row['last_attempt_at']->format('M j, Y g:i A') : 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>


