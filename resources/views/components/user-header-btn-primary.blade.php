<a {{ $attributes->merge(['class' => 'user-header-btn-primary inline-flex min-h-[48px] w-full min-w-0 items-center justify-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-semibold text-indigo-700 shadow-md shadow-black/10 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-white/50 touch-manipulation sm:min-h-[52px] sm:w-auto sm:px-5 sm:py-3.5 sm:text-base [&>svg]:h-5 [&>svg]:w-5 [&>svg]:shrink-0 sm:[&>svg]:h-5 sm:[&>svg]:w-5']) }}>

    {{ $slot }}
</a>
