<div class="flex flex-col items-center justify-center text-center px-6 py-8 min-h-[220px] sm:min-h-[240px] lg:min-h-[260px] w-full">
    @if(($icon ?? 'chart') === 'wifi')
        <svg class="w-10 h-10 text-gray-300 mb-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
    @elseif(($icon ?? 'chart') === 'check')
        <svg class="w-10 h-10 text-gray-300 mb-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    @elseif(($icon ?? 'chart') === 'users')
        <svg class="w-10 h-10 text-gray-300 mb-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
    @else
        <svg class="w-10 h-10 text-gray-300 mb-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
    @endif
    <p class="text-sm font-medium text-gray-600">{{ $message }}</p>
    @if(!empty($hint))
        <p class="text-xs text-gray-500 mt-1 max-w-xs">{{ $hint }}</p>
    @endif
</div>
