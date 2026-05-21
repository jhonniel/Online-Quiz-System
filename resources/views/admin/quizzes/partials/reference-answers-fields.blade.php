@php
    $index = $index ?? 0;
    $question = $question ?? null;
    $prefix = "questions[{$index}]";
@endphp
<label for="reference_answer_{{ $index }}" class="block text-sm font-medium text-gray-700">Reference answer</label>
<p class="mt-1 text-sm text-gray-500">
    <strong>Admin only.</strong> Not shown to quiz takers. Shown in manual grading when reviewing this text question.
</p>
<textarea name="{{ $prefix }}[correct_answer]"
          id="reference_answer_{{ $index }}"
          rows="3"
          placeholder="Optional reference for graders (e.g. key points or sample answer)"
          class="mt-2 block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md">{{ old("questions.{$index}.correct_answer", $question->correct_answer ?? '') }}</textarea>
