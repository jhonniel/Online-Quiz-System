<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $exportMeta['title'] ?? 'Users List' }}</title>
    <style>
        @page { margin: 32mm 10mm 14mm 10mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111827; margin: 0; padding: 0; }
        header { position: fixed; top: -28mm; left: 0; right: 0; border-bottom: 1px solid #E5E7EB; padding-bottom: 4px; }
        footer { position: fixed; bottom: -10mm; left: 0; right: 0; height: 10mm; color: #6B7280; font-size: 8px; }
        .header-inner, .footer-inner { width: 100%; box-sizing: border-box; }
        .brand-table { width: 100%; border-collapse: collapse; }
        .brand-logo-cell { width: 58px; vertical-align: top; padding: 0 8px 0 0; }
        .brand-text-cell { vertical-align: top; }
        .brand-address-cell { vertical-align: top; padding: 0; }
        .brand-logo { width: 48px; height: 48px; object-fit: contain; display: block; }
        .system-name { font-size: 14px; font-weight: bold; margin: 0; padding: 0; color: #111827; line-height: 1.2; }
        .system-address { font-size: 8px; color: #4B5563; line-height: 1.3; margin: 2px 0 0 0; padding: 0; }
        .footer-meta { font-size: 8px; color: #6B7280; margin: 0; padding: 3px 0 0; }
        .report-title { font-size: 13px; font-weight: bold; margin: 8px 0 0 0; padding: 0; color: #111827; line-height: 1.2; }
        .meta { font-size: 8px; color: #4b5563; margin-bottom: 8px; line-height: 1.4; }
        table.data-table { width: 100%; border-collapse: collapse; }
        table.data-table th, table.data-table td { border: 1px solid #d1d5db; padding: 3px 4px; text-align: left; vertical-align: top; }
        table.data-table th { background: #f3f4f6; font-weight: 700; font-size: 8px; }
        thead { display: table-header-group; }
        tbody tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <header>
        <div class="header-inner">
            <table class="brand-table">
                <tr>
                    @if(!empty($branding['system_logo_data_uri']))
                        <td class="brand-logo-cell" rowspan="{{ !empty($branding['contact_address']) ? 2 : 1 }}">
                            <img src="{{ $branding['system_logo_data_uri'] }}" alt="Logo" class="brand-logo">
                        </td>
                    @endif
                    <td class="brand-text-cell">
                        <p class="system-name">{{ $branding['system_name'] ?? 'System' }}</p>
                    </td>
                </tr>
                @if(!empty($branding['contact_address']))
                    <tr>
                        <td class="brand-address-cell">
                            <p class="system-address">{{ $branding['contact_address'] }}</p>
                        </td>
                    </tr>
                @endif
            </table>
            <p class="report-title">{{ $exportMeta['title'] ?? 'Users List' }}</p>
        </div>
    </header>

    <footer>
        <div class="footer-inner">
            <p class="footer-meta">
                Generated {{ now()->format('M d, Y h:i A') }} • {{ $branding['system_name'] ?? 'System' }} • Page <span class="page"></span> of <span class="topage"></span>
            </p>
        </div>
    </footer>

    <div class="meta">
        Generated: {{ now()->format('M d, Y h:i A') }}
        | Total: {{ $users->count() }}
        @if(!empty($exportMeta['school']))
            | School: {{ $exportMeta['school'] }}
        @endif
        @if(!empty($exportMeta['role']))
            | Role: {{ $exportMeta['role'] }}
        @endif
        @if(!empty($exportMeta['department']))
            | Department: {{ $exportMeta['department'] }}
        @endif
        @if(!empty($exportMeta['search']))
            | Search: "{{ $exportMeta['search'] }}"
        @endif
        @if(!empty($exportMeta['selected_count']))
            | Selected export: {{ $exportMeta['selected_count'] }} user(s)
        @endif
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Name</th>
                <th>Email</th>
                <th>Contact Number</th>
                @if(empty($isTeachersManagement))
                    <th>Department</th>
                @endif
                <th>Status</th>
                <th>Date Hired</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->contact_number ?: '—' }}</td>
                    @if(empty($isTeachersManagement))
                        <td>
                            @if(in_array($user->role, ['employee', 'student'], true) && $user->department)
                                {{ $user->department->name }}
                            @else
                                —
                            @endif
                        </td>
                    @endif
                    <td>{{ $user->is_active ? 'Active' : 'Disabled' }}</td>
                    <td>{{ $user->date_hired?->format('M d, Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ empty($isTeachersManagement) ? 7 : 6 }}">No users match the current filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
