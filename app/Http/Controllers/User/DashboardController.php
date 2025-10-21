<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\QuizAssignment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Get all active quizzes
        $allQuizzes = \App\Models\Quiz::where('is_active', true)
            ->with(['creator', 'assignments' => function($query) {
                $query->where('user_id', auth()->id());
            }])
            ->get();

        // Get assigned quizzes for statistics
        $assignedQuizzes = QuizAssignment::where('user_id', auth()->id())
            ->with(['quiz.creator'])
            ->whereHas('quiz', function($query) {
                $query->where('is_active', true);
            })
            ->get();

        $completedQuizzes = $assignedQuizzes->where('is_completed', true)->count();
        $pendingQuizzes = $assignedQuizzes->where('is_completed', false)->count();
        $totalQuizzes = $allQuizzes->count(); // Show total available quizzes

        // Get ongoing quiz (in progress)
        $ongoingQuiz = QuizAssignment::where('user_id', auth()->id())
            ->where('status', 'in_progress')
            ->where('is_completed', false)
            ->with('quiz')
            ->first();

        return view('user.dashboard', compact(
            'allQuizzes',
            'assignedQuizzes',
            'completedQuizzes',
            'pendingQuizzes',
            'totalQuizzes',
            'ongoingQuiz'
        ));
    }
}
