<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentPerformanceRating;
use App\Models\User;
use App\Models\UserActivity;
use App\Support\StudentPerformanceRatingForm;
use App\Support\StudentTrainingProgress;
use Illuminate\Http\Request;

class StudentPerformanceRatingController extends Controller
{
    public function edit(User $user)
    {
        $this->assertCanAccessPerformanceRatings($user);

        $rating = StudentPerformanceRating::query()
            ->with('rater')
            ->firstOrNew(['user_id' => $user->id]);
        $sections = StudentPerformanceRatingForm::sections();
        $totals = $rating->exists ? $rating->totals() : StudentPerformanceRatingForm::totals([]);

        return view('admin.student-management.performance-rating', [
            'student' => $user->loadMissing(['university', 'department']),
            'rating' => $rating,
            'sections' => $sections,
            'totals' => $totals,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->assertCanAccessPerformanceRatings($user);

        $actor = auth()->user();
        $validated = $request->validate(
            StudentPerformanceRatingForm::validationRules(),
            StudentPerformanceRatingForm::validationMessages()
        );

        $previous = StudentPerformanceRating::query()->where('user_id', $user->id)->first();
        $previousOverall = $previous ? $previous->overallScore() : null;

        $rating = StudentPerformanceRating::query()->updateOrCreate(
            ['user_id' => $user->id],
            array_merge($validated, [
                'rated_by' => $actor->id,
                'rated_by_name' => $actor->name,
                'rated_by_email' => $actor->email,
                'rated_at' => now(),
            ])
        );

        $rating->loadMissing('rater');
        $overall = $rating->overallScore();
        $max = $rating->overallMax();

        UserActivity::logActivity($actor, 'action', 'student_performance_rating_saved', [
            'description' => 'Saved student performance rating for '.$user->name,
            'target_user_id' => $user->id,
            'target_user_name' => $user->name,
            'target_user_email' => $user->email,
            'performed_via' => 'student_management_performance_rating',
            'overall_score' => $overall,
            'overall_max' => $max,
            'previous_overall_score' => $previousOverall,
            'rated_by_id' => $actor->id,
            'rated_by_name' => $actor->name,
            'rated_by_email' => $actor->email,
        ]);

        return redirect()
            ->route('admin.student-management.students.performance-rating.edit', $user)
            ->with('success', "Performance rating saved ({$overall}/{$max}) by {$actor->name}.");
    }

    private function assertCanAccessPerformanceRatings(User $student): void
    {
        $authUser = auth()->user();
        if (! $authUser || ! StudentPerformanceRatingForm::viewerCanAccess($authUser)) {
            abort(403, 'Access denied. You need the Student Performance Ratings permission to rate students.');
        }

        if ($student->role !== 'student') {
            abort(404);
        }

        $allowedDepartmentIds = $authUser->canAccessStudentManagement()
            ? $authUser->getAllowedStudentDepartmentIds()
            : null;

        if (is_array($allowedDepartmentIds) && $allowedDepartmentIds !== []) {
            if (! in_array((int) $student->department_id, array_map('intval', $allowedDepartmentIds), true)) {
                abort(403, 'Access denied.');
            }
        }

        if (! StudentTrainingProgress::hasMetRequiredTrainingHours($student)) {
            abort(403, 'Performance rating is available only after the student completes their required training hours.');
        }
    }
}
