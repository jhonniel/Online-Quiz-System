<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\HiringApplication;
use App\Models\QuizAssignment;
use Illuminate\Http\Request;

class HiringApplicationController extends Controller
{
    /**
     * Show the applicant's hiring application status.
     */
    public function show()
    {
        $user = auth()->user();
        
        // Only allow applicants to access this page
        if ($user->role !== 'applicant') {
            abort(403, 'Only applicants can view their application status.');
        }

        // Get the application linked to this user
        $application = HiringApplication::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->with(['hiringPosition', 'reviewer'])
            ->first();

        if (!$application) {
            return view('user.hiring-application.show', [
                'application' => null,
                'assignedQuizCount' => 0,
                'message' => 'No application found. Please contact the administrator if you believe this is an error.',
            ]);
        }

        $assignedQuizCount = 0;
        if ($user->hasInternshipQuizPortalAccess()) {
            $assignedQuizCount = QuizAssignment::query()
                ->where('user_id', $user->id)
                ->whereHas('quiz', fn ($query) => $query->where('is_active', true))
                ->count();
        }

        return view('user.hiring-application.show', compact('application', 'assignedQuizCount'));
    }
}
