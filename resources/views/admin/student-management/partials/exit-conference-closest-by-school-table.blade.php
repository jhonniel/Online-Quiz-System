{{-- Expects: $exitConferenceClosestBySchool (list of row arrays) --}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School</th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Closest student</th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exit date</th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">vs today</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($exitConferenceClosestBySchool as $exitRow)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $exitRow['school_name'] }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        @if(!empty($exitRow['student_id']))
                            <div>{{ $exitRow['student_name'] }}</div>
                            <div class="text-xs text-gray-500 truncate max-w-xs" title="{{ $exitRow['student_email'] ?? '' }}">{{ $exitRow['student_email'] }}</div>
                        @else
                            <span class="text-gray-400">No student with a computable exit date</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 tabular-nums">
                        @if(!empty($exitRow['exit_date_formatted']))
                            {{ $exitRow['exit_date_formatted'] }}
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        @if(($exitRow['source'] ?? '') === 'admin')
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">Admin set</span>
                        @elseif(($exitRow['source'] ?? '') === 'estimated')
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">Estimated</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        @php
                            $d = $exitRow['signed_days_from_today'] ?? null;
                        @endphp
                        @if($d === null)
                            <span class="text-gray-400">—</span>
                        @elseif($d === 0)
                            Today
                        @elseif($d > 0)
                            @php $ad = abs((int) $d); @endphp
                            In {{ number_format($ad) }} {{ $ad === 1 ? 'day' : 'days' }}
                        @else
                            @php $bd = abs((int) $d); @endphp
                            {{ number_format($bd) }} {{ $bd === 1 ? 'day' : 'days' }} ago
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                        No schools with students still completing OJT in your current department scope.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
