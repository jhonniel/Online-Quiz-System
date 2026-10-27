@extends('layouts.user')

@section('page-title', 'Enter Quiz Code')

@section('content')
<x-user-page-shell class="pb-8">
    <x-user-page-header title="Enter quiz code" subtitle="Use the code from your instructor to open an assigned quiz.">
        <x-slot:icon>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
            </svg>
        </x-slot:icon>
    </x-user-page-header>

    <div class="mx-auto w-full max-w-md">
        <div class="overflow-hidden rounded-xl border border-slate-200/90 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:p-8">
            <form class="space-y-5" action="{{ route('user.quizzes.validate-code') }}" method="POST">
                @csrf
                <div>
                    <label for="quiz_code" class="block text-sm font-medium text-slate-700">Quiz code</label>
                    <input id="quiz_code"
                           name="quiz_code"
                           type="text"
                           required
                           maxlength="20"
                           autocomplete="off"
                           class="mt-1.5 block w-full rounded-lg border-slate-300 uppercase shadow-sm placeholder:normal-case placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           placeholder="e.g. ABC123"
                           value="{{ old('quiz_code') }}">
                    @if(isset($errors) && $errors->has('quiz_code'))
                        <p class="mt-2 text-sm text-rose-600">{{ $errors->first('quiz_code') }}</p>
                    @endif
                </div>
                <button type="submit"
                        class="flex w-full justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Start quiz
                </button>
            </form>

            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-xs uppercase tracking-wide">
                    <span class="bg-white px-2 text-slate-400">or</span>
                </div>
            </div>

            <a href="{{ url('/dashboard') }}"
               class="flex w-full items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to dashboard
            </a>
        </div>
    </div>
</x-user-page-shell>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const quizCodeInput = document.getElementById('quiz_code');
    if (!quizCodeInput) {
        return;
    }
    quizCodeInput.addEventListener('input', function (e) {
        e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    });
});
</script>
@endsection
