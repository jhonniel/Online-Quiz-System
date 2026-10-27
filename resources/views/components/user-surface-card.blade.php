{{-- White content surface (no section header) — profile panels, embedded layouts --}}
<section {{ $attributes->merge(['class' => 'min-w-0 overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.04]']) }}>
    {{ $slot }}
</section>
