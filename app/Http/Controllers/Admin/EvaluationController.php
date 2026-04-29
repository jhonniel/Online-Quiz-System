<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvaluationForm;
use App\Models\EvaluationSubmission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class EvaluationController extends Controller
{
    public function index()
    {
        if (!Schema::hasTable('evaluation_forms') || !Schema::hasTable('evaluation_submissions')) {
            return redirect('/admin/notifications')
                ->with('error', 'Evaluation tables are not ready yet. Please run migrations.');
        }

        $forms = EvaluationForm::query()
            ->with('creator:id,name')
            ->withCount('submissions')
            ->latest()
            ->get();

        $students = User::query()
            ->where('role', 'student')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'evaluation_forced_at']);

        return view('admin.evaluations.index', compact('forms', 'students'));
    }

    public function create()
    {
        if (!Schema::hasTable('evaluation_forms')) {
            return redirect('/admin/notifications')
                ->with('error', 'Evaluation tables are not ready yet. Please run migrations.');
        }

        return view('admin.evaluations.create', [
            'form' => new EvaluationForm([
                'questions' => [
                    ['question' => '', 'type' => 'rating', 'required' => true],
                ],
            ]),
        ]);
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('evaluation_forms')) {
            return redirect('/admin/notifications')
                ->with('error', 'Evaluation tables are not ready yet. Please run migrations.');
        }

        $validated = $this->validateForm($request);
        $validated['created_by'] = auth()->id();

        EvaluationForm::create($validated);

        return redirect('/admin/evaluations')->with('success', 'Evaluation form created.');
    }

    public function edit(EvaluationForm $evaluation)
    {
        if (!Schema::hasTable('evaluation_forms')) {
            return redirect('/admin/notifications')
                ->with('error', 'Evaluation tables are not ready yet. Please run migrations.');
        }

        return view('admin.evaluations.edit', [
            'form' => $evaluation,
        ]);
    }

    public function update(Request $request, EvaluationForm $evaluation)
    {
        if (!Schema::hasTable('evaluation_forms')) {
            return redirect('/admin/notifications')
                ->with('error', 'Evaluation tables are not ready yet. Please run migrations.');
        }

        $validated = $this->validateForm($request);
        $evaluation->update($validated);

        return redirect('/admin/evaluations')->with('success', 'Evaluation form updated.');
    }

    public function destroy(EvaluationForm $evaluation)
    {
        if (!Schema::hasTable('evaluation_forms')) {
            return redirect('/admin/notifications')
                ->with('error', 'Evaluation tables are not ready yet. Please run migrations.');
        }

        $evaluation->delete();

        return redirect('/admin/evaluations')->with('success', 'Evaluation form removed.');
    }

    public function activate(EvaluationForm $evaluation)
    {
        if (!Schema::hasTable('evaluation_forms')) {
            return redirect('/admin/notifications')
                ->with('error', 'Evaluation tables are not ready yet. Please run migrations.');
        }

        EvaluationForm::query()->update(['is_active' => false]);
        $evaluation->update(['is_active' => true]);

        return redirect('/admin/evaluations')->with('success', 'Evaluation form is now active.');
    }

    public function submissions(EvaluationForm $evaluation)
    {
        if (!Schema::hasTable('evaluation_forms') || !Schema::hasTable('evaluation_submissions')) {
            return redirect('/admin/notifications')
                ->with('error', 'Evaluation tables are not ready yet. Please run migrations.');
        }

        $submissions = $evaluation->submissions()
            ->with('user:id,name,email')
            ->latest('submitted_at')
            ->paginate(20);

        return view('admin.evaluations.submissions', compact('evaluation', 'submissions'));
    }

    public function forceSend(Request $request)
    {
        if (!Schema::hasTable('users')) {
            return redirect('/admin/evaluations')->with('error', 'Users table not found.');
        }

        $validated = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
        ]);

        $student = User::query()
            ->where('id', $validated['student_id'])
            ->where('role', 'student')
            ->first();

        if (!$student) {
            return redirect('/admin/evaluations')->with('error', 'Selected user is not a student.');
        }

        if (Schema::hasColumn('users', 'evaluation_forced_at')) {
            $student->update([
                'evaluation_forced_at' => now(),
            ]);
        }

        return redirect('/admin/evaluations')
            ->with('success', 'Evaluation was forced for ' . $student->name . '.');
    }

    public function reviews()
    {
        if (!Schema::hasTable('evaluation_forms') || !Schema::hasTable('evaluation_submissions')) {
            return redirect('/admin/notifications')
                ->with('error', 'Evaluation tables are not ready yet. Please run migrations.');
        }

        $reviews = EvaluationSubmission::query()
            ->with([
                'user:id,name,email',
                'form:id,title',
            ])
            ->latest('submitted_at')
            ->paginate(20);

        return view('admin.evaluations.reviews', compact('reviews'));
    }

    private function validateForm(Request $request): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string|max:500',
            'questions.*.type' => ['required', Rule::in(['rating', 'text'])],
            'questions.*.required' => 'nullable|boolean',
        ]);

        $validated['questions'] = collect($validated['questions'])
            ->map(function ($question) {
                return [
                    'question' => trim((string) ($question['question'] ?? '')),
                    'type' => $question['type'] ?? 'text',
                    'required' => (bool) ($question['required'] ?? false),
                ];
            })
            ->filter(fn ($q) => $q['question'] !== '')
            ->values()
            ->all();

        if (empty($validated['questions'])) {
            abort(422, 'At least one valid question is required.');
        }

        return $validated;
    }
}
