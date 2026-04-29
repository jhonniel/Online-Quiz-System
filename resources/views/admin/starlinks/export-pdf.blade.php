<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Starlinks Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 6px 0; }
        .meta { font-size: 11px; color: #4b5563; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-weight: 700; }
    </style>
</head>
<body>
    <h1>Starlinks Export</h1>
    <div class="meta">
        Generated: {{ now()->format('M d, Y h:i A') }}
        @if(!empty($search))
            | Search: "{{ $search }}"
        @endif
        @if(!empty($statusFilter))
            | Status: {{ $statusFilter }}
        @endif
        @if(!empty($accountEmailFilter))
            | Account/Email: "{{ $accountEmailFilter }}"
        @endif
        @if(!empty($clientNameFilter))
            | Client Name: "{{ $clientNameFilter }}"
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Account / Email</th>
                <th>Starlink ID</th>
                <th>Kit No.</th>
                <th>Office / Location</th>
                <th>Client Name</th>
                <th>Plan</th>
                <th>Status</th>
                <th>Contact Email</th>
                <th>Start Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($starlinks as $starlink)
                <tr>
                    <td>{{ $starlink->id }}</td>
                    <td>{{ $starlink->account_linked_email ?: ($starlink->linkedAccount->email ?? '—') }}</td>
                    <td>{{ $starlink->starlink_id ?: '—' }}</td>
                    <td>{{ $starlink->kit_number ?: '—' }}</td>
                    <td>{{ $starlink->office_location ?: '—' }}</td>
                    <td>{{ $starlink->municipality ?: '—' }}</td>
                    <td>{{ $starlink->plan ?: '—' }}</td>
                    <td>{{ $starlink->status ?: '—' }}</td>
                    <td>{{ $starlink->contact_email ?: '—' }}</td>
                    <td>{{ $starlink->start_date?->format('Y-m-d') ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
