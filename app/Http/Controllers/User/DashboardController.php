<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\QuizAssignment;
use App\Models\TicketReport;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Technician users get a ticket-focused dashboard.
        if ($user->role === 'technician') {
            $assignedTicketsQuery = TicketReport::query()
                ->where('assigned_to_user_id', $user->id);

            $assignedTotal = (clone $assignedTicketsQuery)->count();
            $assignedOpen = (clone $assignedTicketsQuery)
                ->whereIn('status', [
                    TicketReport::STATUS_OPEN,
                    TicketReport::STATUS_PROCESSING,
                    TicketReport::STATUS_NEEDS_INVESTIGATION,
                ])->count();
            $assignedResolved = (clone $assignedTicketsQuery)
                ->whereIn('status', [
                    TicketReport::STATUS_RESOLVED,
                    TicketReport::STATUS_CLOSED,
                ])->count();
            $assignedPendingPayment = (clone $assignedTicketsQuery)
                ->where('payment_status', TicketReport::PAYMENT_STATUS_PENDING)
                ->count();

            $recentAssignedTickets = (clone $assignedTicketsQuery)
                ->latest()
                ->take(10)
                ->get();

            return view('user.technician-dashboard', compact(
                'assignedTotal',
                'assignedOpen',
                'assignedResolved',
                'assignedPendingPayment',
                'recentAssignedTickets'
            ));
        }
        
        // For applicants, only show assigned quizzes (not all available quizzes)
        if ($user->role === 'applicant') {
            // Get only assigned quizzes for applicants
            $assignedQuizzes = QuizAssignment::where('user_id', $user->id)
                ->with(['quiz.creator'])
                ->whereHas('quiz', function($query) {
                    $query->where('is_active', true);
                })
                ->get();

            $allQuizzes = $assignedQuizzes->map(function($assignment) {
                return $assignment->quiz;
            });

            $completedQuizzes = $assignedQuizzes->where('is_completed', true)->count();
            $pendingQuizzes = $assignedQuizzes->where('is_completed', false)->count();
            $totalQuizzes = $assignedQuizzes->count(); // Only count assigned quizzes

            // Get ongoing quiz (in progress)
            $ongoingQuiz = QuizAssignment::where('user_id', $user->id)
                ->where('status', 'in_progress')
                ->where('is_completed', false)
                ->with('quiz')
                ->first();
        } else {
            // For other roles (student, employee), show all active quizzes
            $allQuizzes = \App\Models\Quiz::where('is_active', true)
                ->with(['creator', 'assignments' => function($query) {
                    $query->where('user_id', auth()->id());
                }])
                ->get();

            // Get assigned quizzes for statistics
            $assignedQuizzes = QuizAssignment::where('user_id', $user->id)
                ->with(['quiz.creator'])
                ->whereHas('quiz', function($query) {
                    $query->where('is_active', true);
                })
                ->get();

            $completedQuizzes = $assignedQuizzes->where('is_completed', true)->count();
            $pendingQuizzes = $assignedQuizzes->where('is_completed', false)->count();
            $totalQuizzes = $allQuizzes->count(); // Show total available quizzes

            // Get ongoing quiz (in progress)
            $ongoingQuiz = QuizAssignment::where('user_id', $user->id)
                ->where('status', 'in_progress')
                ->where('is_completed', false)
                ->with('quiz')
                ->first();
        }

        return view('user.dashboard', compact(
            'allQuizzes',
            'assignedQuizzes',
            'completedQuizzes',
            'pendingQuizzes',
            'totalQuizzes',
            'ongoingQuiz'
        ));
    }

    /**
     * Display the Term of Reference (TOR) PDF in an iframe
     */
    public function tor()
    {
        return view('user.tor');
    }
}
