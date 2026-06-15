@props([
    'nda',
    'previewUrl',
    'effectiveStatus',
])

@php
    use App\Models\StudentNda;
    $btnBase = 'w-full inline-flex items-center justify-center gap-1.5 rounded-md px-3 py-2 text-xs font-medium shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1';
@endphp

<div class="flex flex-col gap-1.5 min-w-[7.25rem] max-w-[8.5rem]">
    <button type="button"
            @click="previewUrl = '{{ $previewUrl }}'; previewStudent = '{{ e($nda->user?->name ?? 'Student') }}'; previewOpen = true"
            class="{{ $btnBase }} text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:ring-indigo-500">
        <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        Preview
    </button>

    @if($effectiveStatus !== StudentNda::STATUS_APPROVED)
        <form method="POST" action="{{ route('admin.student-nda-files.approve', $nda) }}" class="w-full">
            @csrf
            <button type="submit"
                    onclick="return confirm('Approve this NDA? The student will be able to record attendance.')"
                    class="{{ $btnBase }} text-white bg-green-600 hover:bg-green-700 focus:ring-green-500">
                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Approve
            </button>
        </form>
    @endif

    @if($effectiveStatus !== StudentNda::STATUS_REJECTED)
        <button type="button"
                @click="rejectUrl = '{{ route('admin.student-nda-files.reject', $nda) }}'; rejectStudent = '{{ e($nda->user?->name ?? 'Student') }}'; rejectOpen = true"
                class="{{ $btnBase }} text-white bg-red-600 hover:bg-red-700 focus:ring-red-500">
            <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Reject
        </button>
    @endif

    @unless($nda->reupload_allowed)
        <form method="POST" action="{{ route('admin.student-nda-files.allow-reupload', $nda) }}" class="w-full">
            @csrf
            <button type="submit"
                    class="{{ $btnBase }} text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 focus:ring-indigo-500">
                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Reupload
            </button>
        </form>
    @endunless
</div>
