@php
    $variant = $variant ?? 'text';
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
@elseif($variant === 'list')
    <div class="space-y-2 py-1" aria-hidden="true">
        @for($i = 0; $i < 5; $i++)
            <div class="flex items-center gap-3 p-2 rounded-lg border border-gray-100 bg-white">
                <x-skeleton variant="circle" class="h-8 w-8 shrink-0" />
                <div class="min-w-0 flex-1 space-y-1.5">
                    <x-skeleton class="h-3.5 w-2/3" />
                    <x-skeleton class="h-3 w-full" />
                </div>
            </div>
        @endfor
    </div>
@elseif($variant === 'table')
    <div class="space-y-2" aria-hidden="true">
        <x-skeleton class="h-8 w-full rounded-md" />
        @for($i = 0; $i < 5; $i++)
            <x-skeleton class="h-10 w-full rounded-md" />
        @endfor
    </div>
@elseif($variant === 'quiz-grid')
    <div class="w-full space-y-4" aria-hidden="true">
        <x-skeleton variant="card" class="h-12 w-full" />
        <x-skeleton class="h-8 w-40" />
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @for($i = 0; $i < 6; $i++)
                <x-skeleton variant="card" class="h-36 w-full" />
            @endfor
        </div>
    </div>
@elseif($variant === 'grading')
    <div class="flex flex-col w-full space-y-4" aria-hidden="true">
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
@elseif($variant === 'modal')
    <div class="space-y-4 py-2" aria-hidden="true">
        <x-skeleton variant="card" class="h-24 w-full" />
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-skeleton variant="card" class="h-32 w-full" />
            <x-skeleton variant="card" class="h-32 w-full" />
        </div>
        <x-skeleton class="h-5 w-48" />
        @for($i = 0; $i < 4; $i++)
            <x-skeleton class="h-10 w-full" />
        @endfor
    </div>
@elseif($variant === 'panel')
    <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-3" aria-hidden="true">
        <x-skeleton class="h-5 w-1/3" />
        <x-skeleton class="h-4 w-full" />
        <x-skeleton class="h-4 w-5/6" />
        <x-skeleton class="h-20 w-full rounded-lg" />
    </div>
@elseif($variant === 'badge')
    <span class="inline-block h-5 w-20 animate-pulse bg-gray-200 rounded-full" aria-hidden="true"></span>
@elseif($variant === 'text')
    <div class="space-y-2 py-2" aria-hidden="true">
        <x-skeleton class="h-4 w-full" />
        <x-skeleton class="h-4 w-11/12" />
        <x-skeleton class="h-4 w-4/5" />
    </div>
@else
    <div class="space-y-2" aria-hidden="true">
        <x-skeleton class="h-4 w-full" />
        <x-skeleton class="h-4 w-3/4" />
    </div>
@endif
