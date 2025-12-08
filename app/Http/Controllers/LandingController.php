<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\User;
use App\Models\QuizAttempt;
use App\Models\University;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LandingController extends Controller
{
    public function index()
    {
        // If user is already authenticated, redirect to their dashboard
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user is active and approved
            if ($user->is_active && $user->is_approved) {
                if ($user->isAdmin()) {
                    return redirect()->route('admin.dashboard');
                } else {
                    return redirect()->route('user.dashboard');
                }
            }
        }

        // Check maintenance mode
        $maintenanceMode = \App\Models\Setting::get('maintenance_mode', 'disabled');
        if ($maintenanceMode === 'enabled') {
            $settings = \App\Models\Setting::all()->keyBy('key');
            return response()->view('maintenance', compact('settings'), 503);
        }

        // Get some statistics for the landing page
        $totalQuizzes = Quiz::where('is_active', true)->count();
        $totalUsers = User::where('role', 'user')->where('is_active', true)->count();
        $recentQuizzes = Quiz::where('is_active', true)->latest()->get();

        // Get ranking data for public display
        // Top Students by Total Score (public version - only show top 5)
        $topStudents = User::where('role', 'user')
            ->where('is_active', true)
            ->select('users.*', DB::raw('COALESCE(SUM(quiz_attempts.points_earned), 0) as total_score'))
            ->leftJoin('quiz_attempts', 'users.id', '=', 'quiz_attempts.user_id')
            ->groupBy('users.id')
            ->orderBy('total_score', 'desc')
            ->take(5)
            ->get();

        // University Student Count Ranking (public version - only show top 5)
        $universityRanking = University::withCount(['users' => function($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('users_count', 'desc')
            ->take(5)
            ->get();

        // Quiz Popularity Ranking (public version - only show top 5)
        $quizPopularity = Quiz::where('is_active', true)
            ->select('quizzes.*', DB::raw('COUNT(DISTINCT quiz_attempts.user_id) as student_count'))
            ->leftJoin('quiz_attempts', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
            ->groupBy('quizzes.id')
            ->orderBy('student_count', 'desc')
            ->take(5)
            ->get();

        // Quiz Performance Ranking (public version - only show top 5)
        $quizPerformance = Quiz::where('is_active', true)
            ->select('quizzes.*',
                DB::raw('COUNT(DISTINCT quiz_attempts.user_id) as student_count'),
                DB::raw('COALESCE(AVG(quiz_attempts.points_earned), 0) as average_score'),
                DB::raw('COALESCE(MAX(quiz_attempts.points_earned), 0) as highest_score')
            )
            ->leftJoin('quiz_attempts', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
            ->groupBy('quizzes.id')
            ->orderBy('average_score', 'desc')
            ->take(5)
            ->get();

        return view('landing.index', compact(
            'totalQuizzes',
            'totalUsers',
            'recentQuizzes',
            'topStudents',
            'universityRanking',
            'quizPopularity',
            'quizPerformance'
        ));
    }

    public function features()
    {
        return view('landing.features');
    }

    public function about()
    {
        return view('landing.about');
    }

    public function contact()
    {
        return view('landing.contact');
    }

    public function storeContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        ContactMessage::create([
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'new',
        ]);

        return redirect()->route('landing.contact')
            ->with('success', 'Thank you for your message! We will get back to you soon.');
    }
}
