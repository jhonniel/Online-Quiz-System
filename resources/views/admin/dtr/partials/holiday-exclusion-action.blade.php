@php
    $rowHoliday = ($dtrHolidayMap ?? [])[$dtr->date->format('Y-m-d')] ?? null;
    $rowExcluded = $rowHoliday?->excludesUser((int) $dtr->user_id) ?? false;
@endphp
@if(($canManageEmployeeDtr ?? false) && $rowHoliday)
    @if($rowExcluded)
        <form action="{{ route('admin.dtr.holiday-exclusions.destroy') }}" method="POST" class="inline"
              onsubmit="return confirm(@js('Restore ' . $rowHoliday->name . ' for ' . $employeeName . '? The day will be credited 8 hours again.'));">
            @csrf
            @method('DELETE')
            <input type="hidden" name="user_id" value="{{ $dtr->user_id }}">
            <input type="hidden" name="date" value="{{ $dtr->date->format('Y-m-d') }}">
            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-sky-200 text-xs font-medium rounded-md text-sky-700 bg-sky-50 hover:bg-sky-100"
                    title="Excluded from {{ $rowHoliday->name }}">
                Restore holiday
            </button>
        </form>
    @elseif(($dtr->status ?? '') === 'holiday')
        <form action="{{ route('admin.dtr.holiday-exclusions.store') }}" method="POST" class="inline"
              onsubmit="return confirm(@js('Exclude ' . $employeeName . ' from ' . $rowHoliday->name . ' (' . $dtr->date->format('M d, Y') . ')? The day will be ABSENT unless they have Hubstaff time.'));">
            @csrf
            <input type="hidden" name="user_id" value="{{ $dtr->user_id }}">
            <input type="hidden" name="date" value="{{ $dtr->date->format('Y-m-d') }}">
            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-amber-200 text-xs font-medium rounded-md text-amber-700 bg-amber-50 hover:bg-amber-100">
                Exclude from holiday
            </button>
        </form>
    @endif
@endif
