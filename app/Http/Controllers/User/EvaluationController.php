<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Dtr;
use App\Models\EvaluationForm;
use App\Models\EvaluationSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EvaluationController extends Controller
{
    public function create()
    {
        $user = auth()->user();
        abort_unless($user->role === 'student', 403);
        abort_if(!Schema::hasTable('evaluation_forms') || !Schema::hasTable('evaluation_submissions'), 404, 'Evaluation is not available yet.');

        $form = EvaluationForm::query()->where('is_active', true)->latest('updated_at')->first();
        abort_if(!$form, 404, 'No active evaluation form found.');

        abort_if(!$this->isEligible($user->id, (float) ($user->required_training_hours ?? 0)), 403, 'Evaluation is only available after completing required hours.');

        $alreadySubmitted = EvaluationSubmission::query()
            ->where('evaluation_form_id', $form->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadySubmitted) {
            return redirect('/dashboard')->with('success', 'You already submitted the evaluation form.');
        }

        return view('user.evaluation.create', compact('form'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->role === 'student', 403);
        abort_if(!Schema::hasTable('evaluation_forms') || !Schema::hasTable('evaluation_submissions'), 404, 'Evaluation is not available yet.');

        $form = EvaluationForm::query()->where('is_active', true)->latest('updated_at')->first();
        abort_if(!$form, 404, 'No active evaluation form found.');
        abort_if(!$this->isEligible($user->id, (float) ($user->required_training_hours ?? 0)), 403, 'Evaluation is only available after completing required hours.');

        $alreadySubmitted = EvaluationSubmission::query()
            ->where('evaluation_form_id', $form->id)
            ->where('user_id', $user->id)
            ->exists();
        if ($alreadySubmitted) {
            return redirect('/dashboard')->with('success', 'You already submitted the evaluation form.');
        }

        $rules = [];
        foreach ((array) $form->questions as $index => $question) {
            $field = 'answers.' . $index;
            $required = !empty($question['required']);
            if (($question['type'] ?? 'text') === 'rating') {
                $rules[$field] = ($required ? 'required' : 'nullable') . '|integer|min:1|max:5';
            } else {
                $rules[$field] = ($required ? 'required' : 'nullable') . '|string|max:2000';
            }
        }

        $validated = $request->validate($rules);
        $answers = $validated['answers'] ?? [];

        $responses = collect((array) $form->questions)->map(function ($question, $index) use ($answers) {
            return [
                'question' => (string) ($question['question'] ?? ''),
                'type' => (string) ($question['type'] ?? 'text'),
                'required' => (bool) ($question['required'] ?? false),
                'answer' => $answers[$index] ?? null,
            ];
        })->all();

        EvaluationSubmission::create([
            'evaluation_form_id' => $form->id,
            'user_id' => $user->id,
            'responses' => $responses,
            'submitted_at' => now(),
        ]);

        if (Schema::hasColumn('users', 'evaluation_forced_at')) {
            \App\Models\User::whereKey($user->id)->update([
                'evaluation_forced_at' => null,
            ]);
        }

        return redirect('/dashboard')->with('success', 'Thank you! Your evaluation was submitted.');
    }

    private function isEligible(int $userId, float $requiredHours): bool
    {
        if (Schema::hasColumn('users', 'evaluation_forced_at')) {
            $isForced = \App\Models\User::query()
                ->whereKey($userId)
                ->whereNotNull('evaluation_forced_at')
                ->exists();

            if ($isForced) {
                return true;
            }
        }

        if ($requiredHours <= 0) {
            return false;
        }

        $loggedHours = (float) Dtr::query()
            ->where('user_id', $userId)
            ->sum('total_hours');

        return $loggedHours >= $requiredHours;
    }
}
