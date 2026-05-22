@php
    $variant = $variant ?? 'sidebar';
@endphp

@if($variant === 'sidebar')
    <div class="p-3 space-y-2" aria-hidden="true">
        @for($i = 0; $i < 6; $i++)
            <div class="rounded-xl border border-gray-100 bg-white p-3">
                <div class="flex items-center gap-3">
                    <x-skeleton variant="circle" class="h-10 w-10 shrink-0" />
                    <div class="min-w-0 flex-1 space-y-2">
                        <x-skeleton class="h-3.5 w-3/4" />
                        <x-skeleton class="h-3 w-full" />
                        <x-skeleton class="h-3 w-1/2" />
                    </div>
                    <x-skeleton class="h-6 w-10 shrink-0 rounded-md" />
                </div>
            </div>
        @endfor
    </div>
@elseif($variant === 'quiz-grid')
    <div class="max-w-6xl mx-auto w-full space-y-4 p-4 sm:p-6 lg:p-8" aria-hidden="true">
        <x-skeleton variant="card" class="h-12 w-full" />
        <x-skeleton class="h-8 w-40" />
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @for($i = 0; $i < 6; $i++)
                <x-skeleton variant="card" class="h-36 w-full" />
            @endfor
        </div>
    </div>
@elseif($variant === 'grading')
    <div class="flex-1 flex flex-col min-h-0 p-4 sm:p-6 lg:p-8 max-w-6xl mx-auto w-full space-y-4" aria-hidden="true">
        <x-skeleton variant="card" class="h-12 w-full shrink-0" />
        <div class="space-y-4 pt-2">
            @for($i = 0; $i < 2; $i++)
                <x-skeleton variant="card" class="h-48 w-full" />
            @endfor
        </div>
    </div>
@elseif($variant === 'stats')
    <div class="flex flex-wrap gap-2" aria-hidden="true">
        @for($i = 0; $i < 3; $i++)
            <x-skeleton variant="card" class="h-12 w-28" />
        @endfor
    </div>
@endif
