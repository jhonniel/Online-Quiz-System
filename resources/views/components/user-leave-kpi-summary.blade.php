@props([
    'total' => 0,
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
    'resubmission' => 0,
])

<div {{ $attributes->merge(['class' => 'user-leave-kpi-row user-portal-kpi']) }}>
    <x-user-stat-card label="Total Requests" :value="number_format((int) $total)" tone="indigo">
        <x-slot:icon>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        </x-slot:icon>
    </x-user-stat-card>
    <x-user-stat-card label="Pending" :value="number_format((int) $pending)" tone="amber">
        <x-slot:icon>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </x-slot:icon>
    </x-user-stat-card>
    <x-user-stat-card label="Approved" :value="number_format((int) $approved)" tone="emerald">
        <x-slot:icon>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </x-slot:icon>
    </x-user-stat-card>
    <x-user-stat-card label="Rejected" :value="number_format((int) $rejected)" tone="rose">
        <x-slot:icon>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </x-slot:icon>
    </x-user-stat-card>
    @if((int) $resubmission > 0)
        <x-user-stat-card
            class="user-leave-kpi-row__wide"
            label="For resubmission"
            :value="number_format((int) $resubmission)"
            tone="rose"
        >
            <x-slot:icon>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            </x-slot:icon>
        </x-user-stat-card>
    @endif
</div>
