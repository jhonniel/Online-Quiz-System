@extends('layouts.user')

@section('content')
<div class="max-w-4xl mx-auto p-4 sm:p-6">
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="px-6 py-5 border-b border-gray-200">
            <h1 class="text-xl font-semibold text-gray-900">{{ $form->title }}</h1>
            @if($form->description)
                <p class="mt-1 text-sm text-gray-600">{{ $form->description }}</p>
            @endif
        </div>

        <form action="{{ url('/evaluation') }}" method="POST" class="p-6 space-y-6">
            @csrf
            @foreach((array) $form->questions as $index => $question)
                <div class="rounded-lg border border-gray-200 p-4">
                    <label class="block text-sm font-medium text-gray-800 mb-3">
                        {{ $index + 1 }}. {{ $question['question'] ?? '' }}
                        @if(!empty($question['required']))
                            <span class="text-red-600">*</span>
                        @endif
                    </label>

                    @if(($question['type'] ?? 'text') === 'rating')
                        <div class="flex items-center gap-4">
                            @for($rate = 1; $rate <= 5; $rate++)
                                <label class="inline-flex items-center gap-1 text-sm text-gray-700">
                                    <input type="radio"
                                           name="answers[{{ $index }}]"
                                           value="{{ $rate }}"
                                           class="text-indigo-600 focus:ring-indigo-500"
                                           {{ old('answers.' . $index) == $rate ? 'checked' : '' }}>
                                    <span>{{ $rate }}</span>
                                </label>
                            @endfor
                        </div>
                    @else
                        <textarea name="answers[{{ $index }}]"
                                  rows="4"
                                  class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Write your feedback here...">{{ old('answers.' . $index) }}</textarea>
                    @endif

                    @error('answers.' . $index)
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                    Submit Evaluation
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
