<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HiringProcessController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        $settingsArray = [];
        foreach ($settings as $key => $setting) {
            $settingsArray[$key] = $setting->value;
        }

        // Get hiring process statistics
        $stats = $this->getHiringStats();

        return view('admin.hiring-process.index', compact('settingsArray', 'stats'));
    }

    private function getHiringStats()
    {
        $minimumScore = Setting::get('minimum_quiz_score', 70);
        $autoApproveScore = Setting::get('auto_approve_score', 90);

        // Get all quiz attempts with their scores
        $attempts = QuizAttemptHistory::with(['user', 'quiz'])
            ->whereNotNull('score')
            ->get();

        $totalApplicants = $attempts->unique('user_id')->count();
        $passedApplicants = $attempts->filter(function($attempt) use ($minimumScore) {
            return $attempt->score >= $minimumScore;
        })->unique('user_id')->count();
        
        $autoApprovedApplicants = $autoApproveScore ? $attempts->filter(function($attempt) use ($autoApproveScore) {
            return $attempt->score >= $autoApproveScore;
        })->unique('user_id')->count() : 0;

        $pendingReview = $attempts->filter(function($attempt) use ($minimumScore, $autoApproveScore) {
            $score = $attempt->score;
            return $score >= $minimumScore && ($autoApproveScore ? $score < $autoApproveScore : true);
        })->unique('user_id')->count();

        return [
            'total_applicants' => $totalApplicants,
            'passed_applicants' => $passedApplicants,
            'auto_approved' => $autoApprovedApplicants,
            'pending_review' => $pendingReview,
            'failed_applicants' => $totalApplicants - $passedApplicants,
        ];
    }

    public function applicants()
    {
        $minimumScore = Setting::get('minimum_quiz_score', 70);
        $autoApproveScore = Setting::get('auto_approve_score', 90);

        // Get all users with their best quiz scores
        $applicants = User::whereHas('quizAttemptHistory', function($query) {
            $query->whereNotNull('score');
        })
        ->with(['quizAttemptHistory' => function($query) {
            $query->orderBy('score', 'desc');
        }])
        ->get()
        ->map(function($user) use ($minimumScore, $autoApproveScore) {
            $bestAttempt = $user->quizAttemptHistory->whereNotNull('score')->sortByDesc('score')->first();
            
            $status = 'failed';
            if ($bestAttempt) {
                $score = $bestAttempt->score;
                if ($autoApproveScore && $score >= $autoApproveScore) {
                    $status = 'auto_approved';
                } elseif ($score >= $minimumScore) {
                    $status = 'pending_review';
                }
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'best_score' => $bestAttempt ? $bestAttempt->score : 0,
                'best_quiz' => $bestAttempt ? $bestAttempt->quiz->title : 'N/A',
                'status' => $status,
                'attempts_count' => $user->quizAttemptHistory->whereNotNull('score')->count(),
                'last_attempt' => $bestAttempt ? $bestAttempt->created_at : null,
            ];
        })
        ->sortByDesc('best_score')
        ->values();

        return view('admin.hiring-process.applicants', compact('applicants', 'minimumScore', 'autoApproveScore'));
    }
}

