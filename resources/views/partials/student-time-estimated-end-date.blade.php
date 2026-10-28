@php
    $requiredHours = (float) ($studentTime['required_hours'] ?? 0);
    $remainingHours = (float) ($studentTime['remaining_hours'] ?? 0);
    $formatted = $studentTime['estimated_end_date_formatted'] ?? null;
@endphp
<div class="border border-gray-100 rounded-lg px-3 py-2">
    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Estimated End Date</p>
    @if($formatted && $requiredHours > 0 && $remainingHours > 0)
        <p class="text-sm text-gray-900 mt-0.5">
            <span class="font-bold">{{ $formatted }}</span>
        </p>
        <p class="text-xs text-gray-500 mt-1">
            Projection from remaining hours and recent DTR pace (weekdays only). Not a guarantee.
        </p>
    @elseif($requiredHours > 0 && $remainingHours <= 0)
        <p class="text-sm font-semibold text-green-600 mt-0.5">Requirement met</p>
        <p class="text-xs text-gray-500 mt-1">No further hours needed based on current DTR totals.</p>
    @else
        <p class="text-sm text-gray-500 mt-0.5">—</p>
        <p class="text-xs text-gray-400 mt-1">Set required training hours to calculate an estimate.</p>
    @endif
</div>
