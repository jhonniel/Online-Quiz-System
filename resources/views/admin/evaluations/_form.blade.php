@csrf
@if(isset($form->id))
    @method('PUT')
@endif

<div class="space-y-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
        <input type="text" name="title" value="{{ old('title', $form->title) }}"
               class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
        <textarea name="description" rows="3"
                  class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $form->description) }}</textarea>
    </div>

    <div x-data="evaluationQuestionsBuilder()">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-gray-800">Questions</h3>
            <button type="button" @click="addQuestion"
                    class="inline-flex items-center px-3 py-1.5 text-sm rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
                Add Question
            </button>
        </div>
        <template x-for="(question, index) in questions" :key="index">
            <div class="border border-gray-200 rounded-lg p-4 mb-3">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                    <div class="md:col-span-4">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Question</label>
                        <input type="text" :name="`questions[${index}][question]`" x-model="question.question"
                               class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                        <select :name="`questions[${index}][type]`" x-model="question.type"
                                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="rating">Rating (1-5)</option>
                            <option value="text">Text</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Required</label>
                        <label class="inline-flex items-center mt-2">
                            <input type="checkbox" :name="`questions[${index}][required]`" value="1" x-model="question.required"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Yes</span>
                        </label>
                    </div>
                </div>
                <div class="mt-3 text-right">
                    <button type="button" @click="removeQuestion(index)" x-show="questions.length > 1"
                            class="text-sm text-red-600 hover:text-red-700">Remove</button>
                </div>
            </div>
        </template>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ url('/admin/evaluations') }}" class="px-4 py-2 text-sm rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
        <button type="submit" class="px-4 py-2 text-sm rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
            Save Form
        </button>
    </div>
</div>

@section('scripts')
<script type="application/json" id="evaluation-questions-data">{!! json_encode(old('questions', $form->questions ?? [['question' => '', 'type' => 'rating', 'required' => true]])) !!}</script>
<script>
    function evaluationQuestionsBuilder() {
        const initialQuestionsNode = document.getElementById('evaluation-questions-data');
        const initialQuestions = initialQuestionsNode ? JSON.parse(initialQuestionsNode.textContent) : [{ question: '', type: 'rating', required: true }];
        return {
            questions: initialQuestions,
            addQuestion() {
                this.questions.push({ question: '', type: 'rating', required: true });
            },
            removeQuestion(index) {
                this.questions.splice(index, 1);
            }
        }
    }
</script>
@endsection
