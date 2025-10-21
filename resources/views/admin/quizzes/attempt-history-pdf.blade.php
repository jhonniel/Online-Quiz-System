<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quiz Attempt History</title>
    <style>
        @page { margin: 18mm 12mm 14mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; }
        header { position: fixed; top: -14mm; left: 0; right: 0; height: 12mm; border-bottom: 1px solid #E5E7EB; }
        footer { position: fixed; bottom: -10mm; left: 0; right: 0; height: 10mm; border-top: 1px solid #E5E7EB; color: #6B7280; font-size: 9px; }
        .header-inner, .footer-inner { width: 100%; padding: 4px 12mm; box-sizing: border-box; }
        .title { font-size: 14px; font-weight: bold; margin: 0; }
        .muted { color: #6B7280; font-size: 9.5px; }
        .section-title { font-size: 11.5px; margin: 8px 0 6px; font-weight: bold; }
        .grid { display: table; width: 100%; table-layout: fixed; }
        .col { display: table-cell; vertical-align: top; }
        .card { border: 1px solid #E5E7EB; border-radius: 6px; padding: 8px; margin-bottom: 8px; }
        .table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .table th, .table td { border: 1px solid #E5E7EB; padding: 4px 6px; text-align: left; vertical-align: top; word-wrap: break-word; word-break: break-word; }
        .table th { background: #F3F4F6; font-weight: 600; font-size: 9.75px; }
        .right { text-align: right; }
        thead { display: table-header-group; }
        tfoot { display: table-row-group; }
        tr { page-break-inside: avoid; }
        .small { font-size: 10.5px; }
        .pill { display: inline-block; padding: 1px 5px; border-radius: 999px; background: #EEF2FF; color: #3730A3; font-size: 9px; }
    </style>
    </head>
<body>
    <header>
        <div class="header-inner">
            <div class="title">Quiz Attempt History</div>
            <div class="muted">{{ $assignment->user->name }} • {{ $assignment->user->email }} — Quiz: {{ $assignment->quiz->title }}</div>
        </div>
    </header>
    <footer>
        <div class="footer-inner">Page <span class="page"></span> of <span class="topage"></span></div>
    </footer>

    <div class="grid" style="margin-top: 10px;">
        <div class="col" style="width: 50%; padding-right: 8px;">
            <div class="card">
                <div class="section-title">Summary</div>
                <table class="table">
                    <tr><th>Total Attempts</th><td class="right">{{ $stats['total_attempts'] }}</td></tr>
                    <tr><th>Total Questions per Attempt</th><td class="right">{{ $stats['total_questions'] }}</td></tr>
                    <tr><th>Total Score (sum of correct answers)</th><td class="right">{{ $stats['total_score_sum'] }}</td></tr>
                    <tr><th>Average Score</th><td class="right">{{ $stats['average_correct'] }} / {{ $stats['total_questions'] }}</td></tr>
                    <tr><th>Average Percentage</th><td class="right">{{ $stats['average_percentage'] }}%</td></tr>
                    <tr><th>Total Time Consumed</th><td class="right">{{ gmdate('H:i:s', $stats['total_time_seconds']) }}</td></tr>
                    <tr><th>Average Time per Attempt</th><td class="right">{{ gmdate('H:i:s', $stats['average_time_seconds']) }}</td></tr>
                </table>
            </div>
        </div>
        <div class="col" style="width: 50%; padding-left: 8px;">
            <div class="card">
                <div class="section-title">Assignment</div>
                <table class="table">
                    <tr><th>Status</th><td class="right">{{ $assignment->getStatusText() }}</td></tr>
                    <tr><th>Best Score</th><td class="right">{{ $assignment->best_score ?? 0 }} / {{ $assignment->quiz->total_questions }}</td></tr>
                    <tr><th>Attempts Recorded</th><td class="right">{{ $assignment->attempt_count }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="section-title">Attempts</div>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th style="width:16%">Score</th>
                    <th style="width:12%">Percent</th>
                    <th style="width:12%">Time</th>
                    <th style="width:14%">Status</th>
                    <th style="width:22%">Started</th>
                    <th style="width:22%">Completed</th>
                </tr>
            </thead>
            <tbody>
            @foreach($attempts as $attempt)
                <tr>
                    <td>#{{ $attempt->attempt_number }}</td>
                    <td>{{ $attempt->correct_answers }} / {{ $attempt->total_questions }}</td>
                    <td>{{ $attempt->percentage }}%</td>
                    <td>{{ $attempt->time_taken_formatted }}</td>
                    <td>{{ $attempt->getStatusText() }}</td>
                    <td>{{ $attempt->started_at ? $attempt->started_at->format('M j, Y g:i A') : 'N/A' }}</td>
                    <td>{{ $attempt->completed_at ? $attempt->completed_at->format('M j, Y g:i A') : 'N/A' }}</td>
                </tr>
                @php $detail = collect($attempts_detailed)->firstWhere('id', $attempt->id); @endphp
                @if($detail && !empty($detail['questions']))
                    <tr>
                        <td colspan="7">
                            <table class="table" style="margin-top:6px;">
                                <thead>
                                    <tr>
                                        <th style="width:6%">#</th>
                                        <th>Question</th>
                                        <th style="width:18%">Your Answer</th>
                                        <th style="width:18%">Correct Answer</th>
                                        <th style="width:8%" class="right">Pts</th>
                                        <th style="width:10%">Result</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($detail['questions'] as $idx => $q)
                                    @php
                                        $map = ['A' => $q['option_a'] ?? '','B' => $q['option_b'] ?? '','C' => $q['option_c'] ?? '','D' => $q['option_d'] ?? ''];
                                        $userText = $q['user_key'] ? ($map[$q['user_key']] ?? $q['user_key']) : 'No answer';
                                        $correctText = $q['correct_key'] ? ($map[$q['correct_key']] ?? $q['correct_key']) : 'N/A';
                                    @endphp
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>{{ $q['question_text'] }}</td>
                                        <td>{{ $userText }}</td>
                                        <td>{{ $correctText }}</td>
                                        <td class="right">{{ $q['points'] }}</td>
                                        <td>{!! $q['is_correct'] ? '<span class="pill">Correct</span>' : 'Incorrect' !!}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                @endif
            @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>


