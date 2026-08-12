@extends('layouts.admin')

@section('title', 'Student Performance Evaluation')

@section('content')
@php
    $oldOr = function (string $field, $default = null) use ($rating) {
        $old = old($field);
        if ($old !== null) {
            return $old;
        }

        return $rating->{$field} ?? $default;
    };

    $hasSavedRating = $rating->exists;
    $raterName = $hasSavedRating ? $rating->raterDisplayName() : null;
    $raterEmail = $hasSavedRating ? $rating->raterDisplayEmail() : null;
    $ratedAt = $hasSavedRating && $rating->rated_at
        ? $rating->rated_at->timezone(config('app.timezone'))
        : ($hasSavedRating && $rating->updated_at ? $rating->updated_at->timezone(config('app.timezone')) : null);
    $overallScore = (int) ($totals['overall'] ?? 0);
    $overallMax = (int) ($totals['overall_max'] ?? 100);
    $overallPercent = $overallMax > 0 ? min(100, round(($overallScore / $overallMax) * 100)) : 0;
    $scoreLabels = [
        1 => 'Poor',
        2 => 'Fair',
        3 => 'Satisfactory',
        4 => 'Very Good',
        5 => 'Excellent',
    ];
    $sectionNumbers = [
        'leadership' => 'I',
        'attitude' => 'II',
        'performance' => 'III',
        'grand_total' => 'IV',
    ];
    $sectionMaxes = [];
    foreach ($sections as $section) {
        $sectionMaxes[$section['key']] = (int) collect($section['items'])->sum('max');
    }
@endphp
<div class="space-y-6">
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start min-w-0">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4 min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-indigo-100">Confidential · Admin only</p>
                    <h1 class="text-2xl font-bold text-white mt-0.5">Student Performance Evaluation</h1>
                    <p class="text-indigo-100 mt-1 truncate">{{ $student->name }} · {{ optional($student->university)->name ?? 'No school' }}@if(filled($student->course)) · {{ $student->course }}@endif</p>
                </div>
            </div>
            <a href="{{ route('admin.student-management.students') }}"
               class="inline-flex items-center justify-center self-start px-4 py-2 border border-white border-opacity-20 rounded-md text-sm font-medium text-white hover:bg-white hover:bg-opacity-10">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Students
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Student</p>
            <p class="mt-2 text-lg font-bold text-gray-900 leading-snug">{{ $student->name }}</p>
            <p class="mt-1 text-xs text-gray-500 break-all">{{ $student->email }}</p>
            @if(filled($student->course))
                <p class="mt-1 text-xs font-medium text-indigo-700">{{ $student->course }}</p>
            @endif
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Overall score</p>
            <p class="mt-2 text-2xl font-bold text-indigo-700 tabular-nums" id="rating-live-total">{{ $overallScore }} / {{ $overallMax }}</p>
            <div class="mt-2 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                <div id="rating-live-bar" class="h-full rounded-full bg-indigo-600 transition-all duration-300" style="width: {{ $overallPercent }}%"></div>
            </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Rated by</p>
            @if($hasSavedRating)
                <p class="mt-2 text-lg font-bold text-gray-900 leading-snug">{{ $raterName }}</p>
                @if($raterEmail)
                    <p class="mt-1 text-xs text-gray-500 break-all">{{ $raterEmail }}</p>
                @endif
            @else
                <p class="mt-2 text-lg font-bold text-gray-400">Not yet saved</p>
            @endif
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Last rated</p>
            <p class="mt-2 text-lg font-bold text-gray-900">{{ $ratedAt ? $ratedAt->format('M j, Y') : '—' }}</p>
            @if($ratedAt)
                <p class="mt-1 text-xs text-gray-500">{{ $ratedAt->format('g:i A') }}</p>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">Please enter valid scores before saving.</p>
            <ul class="mt-1 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.student-management.students.performance-rating.update', $student) }}" class="space-y-8" id="performance-rating-form">
        @csrf
        @method('PUT')

        <div class="text-sm text-gray-600">
            <span class="font-semibold text-gray-900">Rating scale:</span>
            1 Poor · 2 Fair · 3 Satisfactory · 4 Very Good · 5 Excellent
            <span class="text-gray-400">|</span>
            Journal max 10 · Requirements max 30
        </div>

        @foreach($sections as $section)
            @php
                $sectionKey = $section['key'];
                $sectionMax = $sectionMaxes[$sectionKey] ?? 0;
                $sectionNumber = $sectionNumbers[$sectionKey] ?? '';
            @endphp
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                    <div class="border-l-4 border-indigo-600 pl-4">
                        <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Part {{ $sectionNumber }}</p>
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight mt-0.5">{{ $section['title'] }}</h2>
                    </div>
                    <p class="text-sm text-gray-600">
                        Subtotal:
                        <span class="font-bold tabular-nums text-indigo-700">
                            <span class="section-subtotal" data-section="{{ $sectionKey }}">{{ (int) ($totals['sections'][$sectionKey] ?? 0) }}</span> / {{ $sectionMax }}
                        </span>
                    </p>
                </div>

                <div class="overflow-x-auto">
                    @if($sectionKey !== 'grand_total')
                        <table class="min-w-full table-fixed">
                            <colgroup>
                                <col class="w-auto">
                                <col span="5" class="w-24">
                            </colgroup>
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Evaluation criteria</th>
                                    @foreach($scoreLabels as $score => $label)
                                        <th class="px-2 py-3 text-center">
                                            <span class="block text-sm font-bold text-gray-900">{{ $score }}</span>
                                            <span class="block text-[10px] font-medium normal-case text-gray-500 leading-tight mt-0.5">{{ $label }}</span>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($section['items'] as $index => $item)
                                    @php
                                        $field = $item['field'];
                                        $max = (int) $item['max'];
                                        $letter = chr(97 + $index);
                                        $value = $oldOr($field);
                                    @endphp
                                    <tr class="hover:bg-gray-50/40">
                                        <td class="px-6 py-5 align-top">
                                            <div class="flex items-start gap-3">
                                                <span class="text-sm font-bold text-indigo-600 shrink-0">{{ strtoupper($letter) }}.</span>
                                                <div>
                                                    <p class="text-sm sm:text-base font-medium text-gray-900 leading-snug">{{ $item['label'] }}</p>
                                                    <p class="mt-1.5 text-sm text-gray-500">Max {{ $max }} points</p>
                                                    @error($field)
                                                        <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>
                                        </td>
                                        @for($score = 1; $score <= 5; $score++)
                                            <td class="px-2 py-5 text-center align-middle">
                                                <label class="group inline-flex cursor-pointer justify-center" title="{{ $scoreLabels[$score] }}">
                                                    <input type="radio"
                                                           name="{{ $field }}"
                                                           id="{{ $field }}_{{ $score }}"
                                                           value="{{ $score }}"
                                                           class="sr-only rating-score-input"
                                                           data-section="{{ $sectionKey }}"
                                                           data-max="{{ $max }}"
                                                           @checked((string) $value === (string) $score)
                                                           required>
                                                    <span class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-gray-300 bg-white transition
                                                                 group-has-[:checked]:border-indigo-600 group-has-[:checked]:bg-indigo-600
                                                                 hover:border-indigo-400">
                                                        <span class="h-2.5 w-2.5 rounded-full bg-white opacity-0 transition group-has-[:checked]:opacity-100"></span>
                                                    </span>
                                                </label>
                                            </td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    <th class="px-6 py-3 w-[70%]">Evaluation criteria</th>
                                    <th class="px-6 py-3 text-right">Score</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($section['items'] as $index => $item)
                                    @php
                                        $field = $item['field'];
                                        $max = (int) $item['max'];
                                        $letter = chr(97 + $index);
                                        $value = $oldOr($field);
                                    @endphp
                                    <tr class="hover:bg-gray-50/40">
                                        <td class="px-6 py-5 align-top">
                                            <div class="flex items-start gap-3">
                                                <span class="text-sm font-bold text-indigo-600 shrink-0">{{ strtoupper($letter) }}.</span>
                                                <div>
                                                    <p class="text-sm sm:text-base font-medium text-gray-900 leading-snug">{{ $item['label'] }}</p>
                                                    <p class="mt-1.5 text-sm text-gray-500">Max {{ $max }} points</p>
                                                    @error($field)
                                                        <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 align-middle text-right">
                                            <div class="inline-flex items-center justify-end gap-2">
                                                <input type="text"
                                                       name="{{ $field }}"
                                                       id="{{ $field }}"
                                                       inputmode="numeric"
                                                       pattern="{{ $max === 10 ? '^(0|[1-9]|10)$' : ($max === 30 ? '^(0|[1-9]|[12][0-9]|30)$' : '[0-9]+') }}"
                                                       min="0"
                                                       max="{{ $max }}"
                                                       maxlength="{{ strlen((string) $max) }}"
                                                       autocomplete="off"
                                                       value="{{ $value }}"
                                                       required
                                                       title="Whole number from 0 to {{ $max }}"
                                                       aria-describedby="{{ $field }}_hint"
                                                       class="rating-score-input rating-numeric-input w-16 rounded-md border-gray-300 py-2 text-center text-sm font-semibold tabular-nums focus:border-indigo-500 focus:ring-indigo-500 user-invalid:border-red-500 user-invalid:ring-1 user-invalid:ring-red-500"
                                                       data-section="{{ $sectionKey }}"
                                                       data-min="0"
                                                       data-max="{{ $max }}">
                                                <span id="{{ $field }}_hint" class="text-sm font-medium text-gray-500">/ {{ $max }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <label for="notes" class="block text-sm font-semibold text-gray-900">Evaluator remarks</label>
            <p class="mt-0.5 text-xs text-gray-500">Optional. Visible only to authorized admins.</p>
            <textarea id="notes" name="notes" rows="3"
                      class="mt-3 w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                      placeholder="Optional remarks…">{{ $oldOr('notes', '') }}</textarea>
            @error('notes')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-2">
            <a href="{{ route('admin.student-management.students') }}"
               class="inline-flex justify-center items-center px-4 py-2 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex justify-center items-center px-5 py-2 rounded-md bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-700">
                Save evaluation
            </button>
        </div>
    </form>
</div>

<script>
(function () {
    const totalEl = document.getElementById('rating-live-total');
    const barEl = document.getElementById('rating-live-bar');
    if (!totalEl) return;

    const overallMax = {{ $overallMax }};
    const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'Home', 'End'];

    function fieldValue(input) {
        if (input.type === 'radio' && !input.checked) return null;
        const value = parseInt(input.value, 10);
        return Number.isNaN(value) ? null : value;
    }

    function scoreBounds(input) {
        const min = parseInt(input.getAttribute('data-min') || input.getAttribute('min') || '0', 10);
        const max = parseInt(input.getAttribute('data-max') || input.getAttribute('max') || '0', 10);
        return {
            min: Number.isNaN(min) ? 0 : min,
            max: Number.isNaN(max) ? 0 : max,
        };
    }

    function sanitizeNumericScore(input) {
        const bounds = scoreBounds(input);
        let digits = String(input.value || '').replace(/[^\d]/g, '');
        if (digits === '') {
            input.value = '';
            input.setCustomValidity('');
            return;
        }

        let n = parseInt(digits, 10);
        while (digits.length > 1 && n > bounds.max) {
            digits = digits.slice(0, -1);
            n = parseInt(digits, 10);
        }
        if (Number.isNaN(n) || n > bounds.max) {
            n = bounds.max;
        }
        if (n < bounds.min) {
            n = bounds.min;
        }

        input.value = String(n);
        input.setCustomValidity('');
    }

    function recalculate() {
        let sum = 0;
        const sectionSums = {};

        document.querySelectorAll('.rating-score-input').forEach(function (input) {
            const value = fieldValue(input);
            if (value === null) return;
            sum += value;
            const section = input.getAttribute('data-section') || 'other';
            sectionSums[section] = (sectionSums[section] || 0) + value;
        });

        totalEl.textContent = sum + ' / ' + overallMax;

        if (barEl && overallMax > 0) {
            barEl.style.width = Math.min(100, Math.round((sum / overallMax) * 100)) + '%';
        }

        document.querySelectorAll('.section-subtotal').forEach(function (el) {
            el.textContent = String(sectionSums[el.getAttribute('data-section')] || 0);
        });
    }

    document.querySelectorAll('.rating-numeric-input').forEach(function (input) {
        const bounds = scoreBounds(input);

        input.addEventListener('keydown', function (e) {
            if (allowedKeys.indexOf(e.key) !== -1 || e.ctrlKey || e.metaKey) {
                return;
            }
            if (!/^\d$/.test(e.key)) {
                e.preventDefault();
                return;
            }

            const start = input.selectionStart;
            const end = input.selectionEnd;
            if (start == null || end == null) {
                return;
            }
            const next = input.value.slice(0, start) + e.key + input.value.slice(end);
            const n = parseInt(next.replace(/[^\d]/g, ''), 10);
            if (!Number.isNaN(n) && n > bounds.max) {
                e.preventDefault();
            }
        });

        input.addEventListener('paste', function (e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text') || '';
            const digits = pasted.replace(/[^\d]/g, '');
            if (digits === '') {
                return;
            }
            input.value = digits;
            sanitizeNumericScore(input);
            recalculate();
        });

        input.addEventListener('drop', function (e) {
            e.preventDefault();
        });

        input.addEventListener('input', function () {
            sanitizeNumericScore(input);
        });

        input.addEventListener('blur', function () {
            sanitizeNumericScore(input);
        });

        input.addEventListener('beforeinput', function (e) {
            if (e.inputType && e.inputType.indexOf('insert') === 0 && e.data && /\D/.test(e.data)) {
                e.preventDefault();
            }
        });
    });

    const form = document.getElementById('performance-rating-form');
    if (form) {
        form.addEventListener('submit', function (e) {
            let firstInvalid = null;

            form.querySelectorAll('.rating-numeric-input').forEach(function (input) {
                const bounds = scoreBounds(input);
                const raw = String(input.value || '').trim();
                const n = parseInt(raw, 10);
                const valid = raw !== '' && /^\d+$/.test(raw) && !Number.isNaN(n) && n >= bounds.min && n <= bounds.max;

                if (!valid) {
                    input.setCustomValidity('Enter a whole number from ' + bounds.min + ' to ' + bounds.max + '.');
                    if (!firstInvalid) firstInvalid = input;
                } else {
                    input.setCustomValidity('');
                }
            });

            if (firstInvalid) {
                e.preventDefault();
                firstInvalid.reportValidity();
                firstInvalid.focus();
            }
        });
    }

    document.querySelectorAll('.rating-score-input').forEach(function (input) {
        input.addEventListener('change', recalculate);
        input.addEventListener('input', recalculate);
    });
    recalculate();
})();
</script>
@endsection
