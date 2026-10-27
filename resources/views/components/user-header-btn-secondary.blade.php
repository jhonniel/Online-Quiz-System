<a {{ $attributes->merge(['class' => 'user-header-btn-secondary inline-flex min-h-[48px] w-full min-w-0 items-center justify-center gap-2 rounded-xl border border-white/35 bg-white/10 px-4 py-3 text-sm font-semibold text-white hover:bg-white/20 touch-manipulation sm:min-h-[52px] sm:w-auto sm:px-5 sm:py-3.5 sm:text-base [&>svg]:h-5 [&>svg]:w-5 [&>svg]:shrink-0 sm:[&>svg]:h-5 sm:[&>svg]:w-5']) }}>

    {{ $slot }}
</a>
