@if($leaveRequestActivityLogs->isNotEmpty())
    <div class="border border-slate-200 rounded-xl bg-slate-50/80 overflow-hidden shadow-sm">
        <div class="bg-slate-100/90 border-b border-slate-200 px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-900">Reviewer notes &amp; actions</h3>
            <p class="text-xs text-slate-600 mt-1">Everything your reviewer recorded for this request, in order. Use this while updating or fixing your submission.</p>
        </div>
        <ul class="divide-y divide-slate-200 px-4 py-2 space-y-0">
            @foreach($leaveRequestActivityLogs as $log)
                @php
                    $badge = match ($log->action) {
                        'approved' => 'bg-green-100 text-green-800 ring-1 ring-inset ring-green-600/20',
                        'rejected' => 'bg-red-100 text-red-800 ring-1 ring-inset ring-red-600/20',
                        'resubmission_requested' => 'bg-amber-100 text-amber-950 ring-1 ring-inset ring-amber-500/30',
                        'for_more_verification' => 'bg-blue-100 text-blue-800 ring-1 ring-inset ring-blue-600/20',
                        default => 'bg-gray-100 text-gray-800 ring-1 ring-inset ring-gray-500/15',
                    };
                    $noteText = trim((string) ($log->notes ?? ''));
                @endphp
                <li class="py-4 first:pt-3 last:pb-3">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-semibold shrink-0 {{ $badge }}">
                            {{ $log->action_label }}
                        </span>
                        <span class="text-xs text-gray-500 sm:text-right tabular-nums">
                            {{ $log->created_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}
                            · {{ config('app.timezone') }}
                        </span>
                    </div>
                    @if($log->performer)
                        <p class="text-xs font-medium text-gray-700 mt-2">
                            By {{ $log->performer->name }}
                        </p>
                    @endif
                    <div class="mt-2 rounded-lg bg-white border border-slate-100 px-3 py-2">
                        @if($noteText !== '')
                            <p class="text-sm text-gray-900 whitespace-pre-line">{{ $noteText }}</p>
                        @else
                            <p class="text-sm text-gray-400 italic">No written note recorded for this step.</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@elseif(trim((string) ($leaveRequest->admin_notes ?? '')) !== '')
    <div class="border border-blue-100 rounded-xl bg-blue-50 px-4 py-3 shadow-sm">
        <h3 class="text-sm font-semibold text-blue-950">Administrator notes</h3>
        <p class="text-sm text-blue-950 mt-2 whitespace-pre-line">{{ $leaveRequest->admin_notes }}</p>
    </div>
@endif
