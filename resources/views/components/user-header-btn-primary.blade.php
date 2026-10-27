<a {{ $attributes->merge(['class' => 'user-header-btn-primary inline-flex min-h-[44px] w-full min-w-0 items-center justify-center gap-1.5 rounded-lg bg-white px-3 py-2.5 text-xs font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-white/50 touch-manipulation sm:w-auto sm:gap-2 sm:px-4 sm:text-sm']) }}>
    {{ $slot }}
</a>
