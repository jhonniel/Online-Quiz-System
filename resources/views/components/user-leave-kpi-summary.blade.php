@props([
    'total' => 0,
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
    'resubmission' => 0,
])

<div {{ $attributes->merge(['class' => 'user-leave-kpi-summary']) }}>
    <x-user-kpi-cell
        label="Total requests"
        :value="number_format((int) $total)"
    />
    <x-user-kpi-cell
        label="Pending"
        :value="number_format((int) $pending)"
        :value-class="(int) $pending > 0 ? 'text-amber-600' : 'text-slate-900'"
    />
    <x-user-kpi-cell
        label="Approved"
        :value="number_format((int) $approved)"
        value-class="text-emerald-700"
    />
    <x-user-kpi-cell
        label="Rejected"
        :value="number_format((int) $rejected)"
        :value-class="(int) $rejected > 0 ? 'text-rose-600' : 'text-slate-900'"
    />
    <x-user-kpi-cell
        label="For resubmission"
        :value="number_format((int) $resubmission)"
        :value-class="(int) $resubmission > 0 ? 'text-rose-600' : 'text-slate-900'"
    />
</div>
