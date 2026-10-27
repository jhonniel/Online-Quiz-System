<a {{ $attributes->merge(['class' => 'user-header-btn-secondary inline-flex min-h-[44px] w-full min-w-0 items-center justify-center gap-1.5 rounded-lg border border-white/30 bg-white/10 px-3 py-2 text-xs font-medium text-white hover:bg-white/20 touch-manipulation sm:w-auto sm:gap-2 sm:text-sm']) }}>
    {{ $slot }}
</a>
