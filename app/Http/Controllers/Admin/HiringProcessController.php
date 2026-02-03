<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptHistory;
use App\Models\User;
use App\Models\HiringApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HiringProcessController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $settings = Setting::all()->keyBy('key');
        $settingsArray = [];
        foreach ($settings as $key => $setting) {
            $settingsArray[$key] = $setting->value;
        }

        // Get hiring process statistics (filtered by position if user has restrictions)
        $stats = $this->getHiringStats($user);

        return view('admin.hiring-process.index', compact('settingsArray', 'stats'));
    }

    private function getHiringStats($user = null)
    {
        $minimumScore = Setting::get('minimum_quiz_score', 70);
        $autoApproveScore = Setting::get('auto_approve_score', 90);

        // Get allowed position IDs for the current user
        $allowedPositionIds = $user ? $user->getAllowedPositionIds() : null;

        // Build query for hiring applications
        $applicationsQuery = HiringApplication::with(['user.quizAttemptHistory' => function($query) {
            $query->whereNotNull('score');
        }]);

        // Filter by allowed positions if user has restrictions
        if ($allowedPositionIds !== null) {
            if (!empty($allowedPositionIds)) {
                $applicationsQuery->whereIn('hiring_position_id', $allowedPositionIds);
            } else {
                // Empty array means no access
                $applicationsQuery->whereRaw('1 = 0'); // Return no results
            }
        }

        $applications = $applicationsQuery->get();
        $applicantUserIds = $applications->pluck('user_id')->unique()->filter();

        // Get quiz attempts for filtered applicants only
        $attempts = QuizAttemptHistory::with(['user', 'quiz'])
            ->whereNotNull('score')
            ->whereIn('user_id', $applicantUserIds->isEmpty() ? [-1] : $applicantUserIds)
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
        $user = Auth::user();
        $minimumScore = Setting::get('minimum_quiz_score', 70);
        $autoApproveScore = Setting::get('auto_approve_score', 90);

        // Get allowed position IDs for the current user
        $allowedPositionIds = $user->getAllowedPositionIds();

        // Build query for hiring applications
        $applicationsQuery = HiringApplication::with(['hiringPosition', 'user.quizAttemptHistory' => function($query) {
            $query->whereNotNull('score')->orderBy('score', 'desc');
        }]);

        // Filter by allowed positions if user has restrictions
        if ($allowedPositionIds !== null) {
            // User has position restrictions - only show applications for allowed positions
            if (!empty($allowedPositionIds)) {
                $applicationsQuery->whereIn('hiring_position_id', $allowedPositionIds);
            } else {
                // Empty array means no access
                $applicationsQuery->whereRaw('1 = 0'); // Return no results
            }
        }
        // If $allowedPositionIds is null, user can see all positions (super admin or no restrictions)

        $applications = $applicationsQuery->get();

        // Get all users with their best quiz scores, filtered by hiring applications
        $applicantUserIds = $applications->pluck('user_id')->unique()->filter();

        // If no applications match the filter, return empty collection
        if ($applicantUserIds->isEmpty()) {
            $applicants = collect();
        } else {
            $applicants = User::whereIn('id', $applicantUserIds)
                ->whereHas('quizAttemptHistory', function($query) {
                    $query->whereNotNull('score');
                })
                ->with(['quizAttemptHistory' => function($query) {
                    $query->orderBy('score', 'desc');
                }, 'hiringApplications.hiringPosition'])
                ->get()
                ->map(function($user) use ($minimumScore, $autoApproveScore, $allowedPositionIds) {
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

                    // Get positions this user applied for (filtered by allowed positions)
                    $userApplications = $user->hiringApplications;
                    if ($allowedPositionIds !== null && !empty($allowedPositionIds)) {
                        $userApplications = $userApplications->whereIn('hiring_position_id', $allowedPositionIds);
                    }
                    $positions = $userApplications->pluck('hiringPosition.title')->filter()->unique()->values();

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'best_score' => $bestAttempt ? $bestAttempt->score : 0,
                        'best_quiz' => $bestAttempt ? $bestAttempt->quiz->title : 'N/A',
                        'status' => $status,
                        'attempts_count' => $user->quizAttemptHistory->whereNotNull('score')->count(),
                        'last_attempt' => $bestAttempt ? $bestAttempt->created_at : null,
                        'positions' => $positions,
                    ];
                })
                ->sortByDesc('best_score')
                ->values();
        }

        return view('admin.hiring-process.applicants', compact('applicants', 'minimumScore', 'autoApproveScore'));
    }
}

