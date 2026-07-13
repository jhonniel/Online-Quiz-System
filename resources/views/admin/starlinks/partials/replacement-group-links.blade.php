@php
    $replacementGroupSummaries = $replacementGroupSummaries ?? [];
    $groupLeaderId = $starlink->replacement_group_id ?? null;
    $groupSummary = $groupLeaderId ? ($replacementGroupSummaries[$groupLeaderId] ?? null) : null;
    $groupMembers = $groupSummary['members'] ?? collect();
    $otherMembers = $groupMembers->where('id', '!=', $starlink->id);
@endphp

@if($groupSummary && ($groupSummary['count'] ?? 0) > 1)
    <div class="mt-2">
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-800">
            Replacement group · {{ $groupSummary['count'] }} devices
        </span>
        @if($otherMembers->isNotEmpty())
            <div class="mt-2 flex flex-wrap gap-1.5">
                @foreach($otherMembers as $member)
                    <button type="button"
                            @click.prevent="openView({{ $member->id }})"
                            class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium text-sky-800 bg-sky-50 hover:bg-sky-100 border border-sky-200 transition-colors touch-manipulation">
                        {{ $member->starlink_id ?: ($member->serial_number ?: ('#'.$member->id)) }}
                    </button>
                @endforeach
            </div>
        @endif
    </div>
@endif
