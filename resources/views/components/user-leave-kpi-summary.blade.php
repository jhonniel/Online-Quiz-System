@props([
    'total' => 0,
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
    'resubmission' => 0,
])

<div {{ $attributes->merge(['class' => 'user-leave-kpi-row']) }}>
    <x-user-stat-card
        size="compact"
        tone="indigo"
        label="Total requests"
        :value="number_format((int) $total)"
    />
    <x-user-stat-card
        size="compact"
        tone="amber"
        label="Pending"
        :value="number_format((int) $pending)"
    />
    <x-user-stat-card
        size="compact"
        tone="emerald"
        label="Approved"
        :value="number_format((int) $approved)"
    />
    <x-user-stat-card
        size="compact"
        tone="rose"
        label="Rejected"
        :value="number_format((int) $rejected)"
    />
    <x-user-stat-card
        size="compact"
        tone="rose"
        label="For resubmission"
        :value="number_format((int) $resubmission)"
    />
</div>
