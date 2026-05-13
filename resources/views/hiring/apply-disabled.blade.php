@extends('layouts.landing')

@section('title', 'Applications unavailable - ' . ($settings['system_name'] ?? 'Careers'))
@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-lg mx-auto bg-white rounded-lg shadow-sm border border-amber-200 p-8 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-700 mb-4">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <h1 class="text-xl font-semibold text-gray-900">Online applications are turned off</h1>
        @if(!empty($positionTitle))
            <p class="mt-2 text-sm text-gray-600">The listing <span class="font-medium text-gray-800">{{ $positionTitle }}</span> is not accepting submissions through this form right now.</p>
        @else
            <p class="mt-2 text-sm text-gray-600">We are not accepting new applications through this page right now.</p>
        @endif
        <div class="mt-6 rounded-md bg-gray-50 border border-gray-200 px-4 py-3 text-left text-sm text-gray-700">
            <p class="font-medium text-gray-900">What to check (admin)</p>
            <ul class="mt-2 list-disc list-inside space-y-1 text-gray-600">
                <li>In <strong>Admin → Settings → Hiring</strong>, set <strong>“Public hiring applications”</strong> to <strong>enabled</strong>.</li>
                <li>Confirm the <strong>application URL path</strong> matches the link you share (default <code class="text-xs bg-gray-100 px-1 rounded">hiring/apply</code>).</li>
                <li>Ensure the <strong>position is active</strong> and the <strong>application deadline</strong> has not passed.</li>
            </ul>
        </div>
        @if(!empty($closedReason) && $closedReason === 'deadline_or_inactive')
            <p class="mt-4 text-sm text-gray-600">This role may be inactive or past its application deadline. Please choose another open position or contact HR.</p>
        @endif
        <p class="mt-8">
            <a href="{{ url('/') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Back to home</a>
        </p>
    </div>
</div>
@endsection
