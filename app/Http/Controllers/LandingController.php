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
use App\Models\Setting;

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

        // Helper function to get image URL
        $getImageUrl = function($imagePath) {
            // Handle "not found" string from Setting::get() default value or empty/null values
            if (empty($imagePath) || $imagePath === 'not found' || $imagePath === null || trim($imagePath) === '') {
                return null;
            }

            // If it's already a full URL, return it
            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                return $imagePath;
            }

            try {
                $storage = \Illuminate\Support\Facades\Storage::disk('digitalocean');

                // Try temporaryUrl first (for private files)
                try {
                    $url = $storage->temporaryUrl($imagePath, now()->addHours(24));
                    return $url;
                } catch (\Exception $e) {
                    // Fallback to regular url
                    try {
                        $url = $storage->url($imagePath);
                        return $url;
                    } catch (\Exception $e2) {
                        \Log::error('Failed to generate image URL', [
                            'path' => $imagePath,
                            'temporaryUrl_error' => $e->getMessage(),
                            'url_error' => $e2->getMessage()
                        ]);
                        return null;
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Exception in getImageUrl', [
                    'path' => $imagePath,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        };

        // Hero content (admin configurable)
        $systemDescription = Setting::get('system_description', 'A powerful, intuitive platform designed for modern educational assessment and learning management.');
        $heroTitle = Setting::get('hero_title', 'Transform Your Assessment Experience');
        $heroSubtitle = Setting::get('hero_subtitle', $systemDescription);
        $heroPrimaryButtonText = Setting::get('hero_primary_button_text', 'Get Started Free');
        $heroPrimaryButtonUrl = Setting::get('hero_primary_button_url', route('login'));
        $heroSecondaryButtonText = Setting::get('hero_secondary_button_text', 'Explore Features');
        $heroSecondaryButtonUrl = Setting::get('hero_secondary_button_url', route('landing.projects'));
        $heroBackgroundPath = Setting::get('hero_background_image', null);
        $heroBackgroundUrl = $getImageUrl($heroBackgroundPath);

        // Get employees/team members - Always show exactly 4 employees
        $employees = [];
        for ($i = 1; $i <= 4; $i++) {
            $imgPath = Setting::get("employee_{$i}_image", null);
            // Filter out invalid values
            if ($imgPath === 'not found' || empty($imgPath) || trim($imgPath) === '') {
                $imgPath = null;
            }

            $employees[] = [
                'name' => Setting::get("employee_{$i}_name", "Employee {$i}"),
                'position' => Setting::get("employee_{$i}_position", ''),
                'image' => $imgPath,
                'image_url' => $getImageUrl($imgPath),
            ];
        }

        // Get projects served - Always show exactly 3 projects
        // Main 3 projects
        $projects = [];
        for ($i = 1; $i <= 3; $i++) {
            $imgPath = Setting::get("project_{$i}_image", null);
            // Filter out invalid values
            if ($imgPath === 'not found' || empty($imgPath) || trim($imgPath) === '') {
                $imgPath = null;
            }

            $projectName = Setting::get("project_{$i}_name", "Project {$i}");
            // Only include projects that have a non-default name
            if (!empty($projectName) && $projectName !== "Project {$i}") {
                $projects[] = [
                    'name' => $projectName,
                    'description' => Setting::get("project_{$i}_description", ''),
                    'image' => $imgPath,
                    'image_url' => $getImageUrl($imgPath),
                    'url' => Setting::get("project_{$i}_url", '#'),
                ];
            }
        }

        // Get additional projects
        $additionalProjectsJson = Setting::get('additional_projects', '[]');
        $additionalProjects = json_decode($additionalProjectsJson, true) ?? [];

        // Merge additional projects with main projects and generate URLs
        if (!empty($additionalProjects) && is_array($additionalProjects)) {
            foreach ($additionalProjects as &$project) {
                // Only include projects that have a name (non-empty projects)
                if (empty($project['name'])) {
                    continue;
                }
                if (!empty($project['image'])) {
                    $project['image_url'] = $getImageUrl($project['image']);
                } else {
                    $project['image_url'] = null;
                }
            }
            unset($project);
            // Filter out empty projects before merging
            $additionalProjects = array_filter($additionalProjects, function($project) {
                return !empty($project['name']);
            });
            $projects = array_merge($projects, $additionalProjects);
        }

        return view('landing.index', compact(
            'totalQuizzes',
            'totalUsers',
            'recentQuizzes',
            'topStudents',
            'universityRanking',
            'quizPopularity',
            'quizPerformance',
            'heroTitle',
            'heroSubtitle',
            'heroPrimaryButtonText',
            'heroPrimaryButtonUrl',
            'heroSecondaryButtonText',
            'heroSecondaryButtonUrl',
            'heroBackgroundUrl',
            'employees',
            'projects'
        ));
    }

    public function projects()
    {
        // Helper function to get image URL
        $getImageUrl = function($imagePath) {
            // Handle "not found" string from Setting::get() default value or empty/null values
            if (empty($imagePath) || $imagePath === 'not found' || $imagePath === null || trim($imagePath) === '') {
                return null;
            }

            // If it's already a full URL, return it
            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                return $imagePath;
            }

            try {
                $storage = \Illuminate\Support\Facades\Storage::disk('digitalocean');

                // Try temporaryUrl first (for private files)
                try {
                    $url = $storage->temporaryUrl($imagePath, now()->addHours(24));
                    return $url;
                } catch (\Exception $e) {
                    // Fallback to regular url
                    try {
                        $url = $storage->url($imagePath);
                        return $url;
                    } catch (\Exception $e2) {
                        \Log::error('Failed to generate image URL', [
                            'path' => $imagePath,
                            'temporaryUrl_error' => $e->getMessage(),
                            'url_error' => $e2->getMessage()
                        ]);
                        return null;
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Exception in getImageUrl', [
                    'path' => $imagePath,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        };

        // Get all projects (main 3 + additional)
        $projects = [];

        // Main 3 projects
        for ($i = 1; $i <= 3; $i++) {
            $imgPath = Setting::get("project_{$i}_image", null);
            // Filter out invalid values
            if ($imgPath === 'not found' || empty($imgPath) || trim($imgPath) === '') {
                $imgPath = null;
            }

            $projectName = Setting::get("project_{$i}_name", "Project {$i}");
            // Include all projects that have a name
            if (!empty($projectName)) {
                $projects[] = [
                    'name' => $projectName,
                    'description' => Setting::get("project_{$i}_description", ''),
                    'image' => $imgPath,
                    'image_url' => $getImageUrl($imgPath),
                    'url' => Setting::get("project_{$i}_url", '#'),
                ];
            }
        }

        // Get additional projects
        $additionalProjectsJson = Setting::get('additional_projects', '[]');
        $additionalProjects = json_decode($additionalProjectsJson, true) ?? [];

        // Merge additional projects with main projects and generate URLs
        if (!empty($additionalProjects) && is_array($additionalProjects)) {
            foreach ($additionalProjects as &$project) {
                // Only include projects that have a name (non-empty projects)
                if (empty($project['name'])) {
                    continue;
                }
                if (!empty($project['image'])) {
                    $project['image_url'] = $getImageUrl($project['image']);
                } else {
                    $project['image_url'] = null;
                }
            }
            unset($project);
            // Filter out empty projects before merging
            $additionalProjects = array_filter($additionalProjects, function($project) {
                return !empty($project['name']);
            });
            $projects = array_merge($projects, $additionalProjects);
        }

        return view('landing.projects', compact('projects'));
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
