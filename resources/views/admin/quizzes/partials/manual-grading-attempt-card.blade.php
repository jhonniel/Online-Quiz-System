@php
    $compactHeader = $compactHeader ?? false;
@endphp
<article class="manual-grading-attempt-card overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-900/5"
         id="attempt-{{ $attempt->id }}">
    <div class="border-l-4 {{ $attempt->question->question_type === 'text' ? 'border-purple-500' : 'border-emerald-500' }}">
        <header class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    @unless($compactHeader)
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h3 class="text-base font-bold text-gray-900">{{ $attempt->user->name }}</h3>
                            <span class="text-xs text-gray-400">·</span>
                            <span class="text-sm font-medium text-gray-600">{{ $attempt->quiz->title }}</span>
                        </div>
                    @endunless
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-800 ring-1 ring-inset ring-amber-600/20">
                            {{ $attempt->question->points }} {{ $attempt->question->points === 1 ? 'pt' : 'pts' }}
                        </span>
                        @if($attempt->question->question_type === 'text')
                            <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-semibold text-purple-800 ring-1 ring-inset ring-purple-600/20">Text</span>
                        @else
                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-800 ring-1 ring-inset ring-emerald-600/20">Fill in blank</span>
                        @endif
                        <span class="text-xs text-gray-500">
                            Submitted {{ $attempt->created_at->format('M j, Y g:i A') }}
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <div class="p-5 space-y-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Question</p>
                <p class="text-sm text-gray-900 leading-relaxed rounded-lg bg-gray-50 border border-gray-100 px-4 py-3">{{ $attempt->question->question_text }}</p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Student answer</p>
                <p class="text-sm text-gray-900 leading-relaxed rounded-lg bg-sky-50 border border-sky-200 px-4 py-3 whitespace-pre-wrap">{{ $attempt->user_answer }}</p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Reference answer(s)</p>
                @if(in_array($attempt->question->question_type, ['text', 'fill_blank']) &&
                    ($attempt->question->correct_answer || $attempt->question->alternative_answer_1 || $attempt->question->alternative_answer_2 || $attempt->question->alternative_answer_3))
                    <div class="space-y-2 rounded-lg border border-emerald-100 bg-emerald-50/50 px-4 py-3">
                        @if($attempt->question->correct_answer)
                            <p class="text-sm text-gray-800"><span class="font-semibold text-emerald-800">Main:</span> {{ $attempt->question->correct_answer }}</p>
                        @endif
                        @foreach(['alternative_answer_1' => 'Alt 1', 'alternative_answer_2' => 'Alt 2', 'alternative_answer_3' => 'Alt 3'] as $field => $label)
                            @if($attempt->question->{$field})
                                <p class="text-sm text-gray-800"><span class="font-semibold text-emerald-800">{{ $label }}:</span> {{ $attempt->question->{$field} }}</p>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-amber-900 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                        No fixed answer key — grade on accuracy and completeness.
                    </p>
                @endif
            </div>

            <form class="grade-form border-t border-gray-100 pt-5" data-attempt-id="{{ $attempt->id }}">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">Verdict</p>
                        <div class="flex flex-wrap gap-4">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="is_correct" value="1" class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300">
                                <span class="ml-2 text-sm font-medium text-gray-800">Correct</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="is_correct" value="0" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300" checked>
                                <span class="ml-2 text-sm font-medium text-gray-800">Incorrect</span>
                            </label>
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-4">
                        <label for="points_earned_{{ $attempt->id }}" class="text-xs font-semibold uppercase tracking-wide text-gray-500">Points earned</label>
                        <div class="mt-2 flex items-center gap-3">
                            <input type="number"
                                   name="points_earned"
                                   id="points_earned_{{ $attempt->id }}"
                                   min="0"
                                   max="{{ $attempt->question->points }}"
                                   value="0"
                                   class="w-24 px-3 py-2 text-sm font-semibold border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            <span class="text-sm text-gray-500">/ {{ $attempt->question->points }} max</span>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <label for="feedback_{{ $attempt->id }}" class="text-xs font-semibold uppercase tracking-wide text-gray-500">Feedback (optional)</label>
                    <textarea name="feedback"
                              id="feedback_{{ $attempt->id }}"
                              rows="2"
                              class="mt-2 w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                              placeholder="Optional note for the student…"></textarea>
                </div>
                <div class="mt-5 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Submit grade
                    </button>
                </div>
            </form>
        </div>
    </div>
</article>
