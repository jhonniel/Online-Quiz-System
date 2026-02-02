<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\User;
use App\Models\QuizAttempt;
use App\Models\University;
use App\Models\ContactMessage;
use App\Models\Stack;
use App\Models\News;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class LandingController extends Controller
{
    public function index()
    {
        // If user is already authenticated, redirect to their dashboard
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user is active and approved
            if ($user->is_active && $user->is_approved) {
                // Load adminPermission relationship to check permissions
                if (!$user->relationLoaded('adminPermission')) {
                    $user->load('adminPermission');
                }

                // Redirect to admin dashboard if user is admin or has admin permissions
                if ($user->isAdmin() || $user->hasAnyAdminPermission()) {
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

        // Get stacks (technologies used by the company)
        $stacks = Stack::active()->ordered()->get();

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

        // Helper function to get image URL (using proxy to avoid CORS)
        $getImageUrl = function($imagePath) {
            // Handle "not found" string from Setting::get() default value or empty/null values
            if (empty($imagePath) || $imagePath === 'not found' || $imagePath === null || trim($imagePath) === '' || trim($imagePath) === '/') {
                return null;
            }

            // Normalize the path
            $imagePath = trim($imagePath);

            // If it's already a full URL (external), return it as-is
            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                return $imagePath;
            }

            // Validate that the path looks like a valid file path (not just a slash or invalid)
            if (strlen($imagePath) < 2 || $imagePath === '/' || $imagePath === '\\') {
                \Log::warning('Invalid image path detected', ['path' => $imagePath]);
                return null;
            }

            // Use proxy route to avoid CORS issues
            // Encode the path to handle special characters
            $encodedPath = base64_encode($imagePath);
            // Base64 strings contain +, /, and = which need special handling in URLs
            // Laravel routes automatically URL-decode, so we manually encode these characters
            // This ensures + doesn't become space, / is preserved, and = padding works
            $urlEncodedPath = str_replace(['+', '/', '='], ['%2B', '%2F', '%3D'], $encodedPath);
            // Construct URL manually to avoid route helper encoding issues
            return url('/image-proxy/' . $urlEncodedPath);
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

        // Laptop image for projects section (default image)
        $laptopImageUrl = asset('images/laptop.png');

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
                $imageUrl = $getImageUrl($imgPath);
                \Log::info('Project image URL generated', [
                    'project' => $projectName,
                    'image_path' => $imgPath,
                    'image_url' => $imageUrl ? 'Generated' : 'NULL'
                ]);
                $projects[] = [
                    'name' => $projectName,
                    'description' => Setting::get("project_{$i}_description", ''),
                    'image' => $imgPath,
                    'image_url' => $imageUrl,
                    'url' => Setting::get("project_{$i}_url", '#'),
                ];
            }
        }

        // Get additional projects
        $additionalProjectsJson = Setting::get('additional_projects', '[]');
        // Handle both string (JSON) and array formats
        if (is_string($additionalProjectsJson)) {
            $additionalProjects = json_decode($additionalProjectsJson, true) ?? [];
        } elseif (is_array($additionalProjectsJson)) {
            $additionalProjects = $additionalProjectsJson;
        } else {
            $additionalProjects = [];
        }

        // Merge additional projects with main projects and generate URLs
        if (!empty($additionalProjects) && is_array($additionalProjects)) {
            foreach ($additionalProjects as &$project) {
                // Only include projects that have a name (non-empty projects)
                if (empty($project['name'])) {
                    continue;
                }
                if (!empty($project['image'])) {
                    $imageUrl = $getImageUrl($project['image']);
                    \Log::info('Additional project image URL generated', [
                        'project' => $project['name'],
                        'image_path' => $project['image'],
                        'image_url' => $imageUrl ? 'Generated' : 'NULL'
                    ]);
                    $project['image_url'] = $imageUrl;
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

        // Log all projects before passing to view
        \Log::info('Projects for landing page', [
            'total_projects' => count($projects),
            'projects' => array_map(function($p) {
                return [
                    'name' => $p['name'] ?? 'Unknown',
                    'has_image' => !empty($p['image']),
                    'has_image_url' => !empty($p['image_url']),
                    'image_url' => $p['image_url'] ?? null
                ];
            }, $projects)
        ]);

        // Get statistics counts
        $statisticsClientsCount = Setting::get('statistics_clients_count', '0');
        $statisticsProjectsCount = Setting::get('statistics_projects_count', '0');
        $statisticsLgusCount = Setting::get('statistics_lgus_count', '0');

        // Check if news section is enabled (for navbar)
        $newsSectionEnabled = Setting::get('news_section_enabled', '0') === '1';

        return view('landing.index', compact(
            'totalQuizzes',
            'totalUsers',
            'recentQuizzes',
            'stacks',
            'statisticsClientsCount',
            'statisticsProjectsCount',
            'statisticsLgusCount',
            'topStudents',
            'universityRanking',
            'quizPopularity',
            'quizPerformance',
            'heroTitle',
            'heroSubtitle',
            'heroPrimaryButtonText',
            'heroPrimaryButtonUrl',
            'heroSecondaryButtonText',
            'newsSectionEnabled',
            'heroSecondaryButtonUrl',
            'heroBackgroundUrl',
            'employees',
            'projects',
            'laptopImageUrl'
        ));
    }

    public function projects()
    {
        // Helper function to get image URL (using proxy to avoid CORS)
        $getImageUrl = function($imagePath) {
            // Handle "not found" string from Setting::get() default value or empty/null values
            if (empty($imagePath) || $imagePath === 'not found' || $imagePath === null || trim($imagePath) === '' || trim($imagePath) === '/') {
                \Log::info('Image path is empty or invalid', ['path' => $imagePath]);
                return null;
            }

            // Normalize the path
            $imagePath = trim($imagePath);

            // If it's already a full URL (external), return it as-is
            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                \Log::info('Image path is already a URL', ['url' => $imagePath]);
                return $imagePath;
            }

            // Validate that the path looks like a valid file path (not just a slash or invalid)
            if (strlen($imagePath) < 2 || $imagePath === '/' || $imagePath === '\\') {
                \Log::warning('Invalid image path detected', ['path' => $imagePath]);
                return null;
            }

            // Use proxy route to avoid CORS issues
            // Encode the path to handle special characters
            $encodedPath = base64_encode($imagePath);
            // Base64 strings contain +, /, and = which need special handling in URLs
            // Laravel routes automatically URL-decode, so we manually encode these characters
            // This ensures + doesn't become space, / is preserved, and = padding works
            $urlEncodedPath = str_replace(['+', '/', '='], ['%2B', '%2F', '%3D'], $encodedPath);
            // Construct URL manually to avoid route helper encoding issues
            return url('/image-proxy/' . $urlEncodedPath);
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
                $imageUrl = $getImageUrl($imgPath);
                \Log::info('Project image URL generated (projects page)', [
                    'project' => $projectName,
                    'image_path' => $imgPath,
                    'image_url' => $imageUrl ? 'Generated' : 'NULL'
                ]);
                $projects[] = [
                    'name' => $projectName,
                    'description' => Setting::get("project_{$i}_description", ''),
                    'image' => $imgPath,
                    'image_url' => $imageUrl,
                    'url' => Setting::get("project_{$i}_url", '#'),
                ];
            }
        }

        // Get additional projects
        $additionalProjectsJson = Setting::get('additional_projects', '[]');
        // Handle both string (JSON) and array formats
        if (is_string($additionalProjectsJson)) {
            $additionalProjects = json_decode($additionalProjectsJson, true) ?? [];
        } elseif (is_array($additionalProjectsJson)) {
            $additionalProjects = $additionalProjectsJson;
        } else {
            $additionalProjects = [];
        }

        // Merge additional projects with main projects and generate URLs
        if (!empty($additionalProjects) && is_array($additionalProjects)) {
            foreach ($additionalProjects as &$project) {
                // Only include projects that have a name (non-empty projects)
                if (empty($project['name'])) {
                    continue;
                }
                if (!empty($project['image'])) {
                    $imageUrl = $getImageUrl($project['image']);
                    \Log::info('Additional project image URL generated (projects page)', [
                        'project' => $project['name'],
                        'image_path' => $project['image'],
                        'image_url' => $imageUrl ? 'Generated' : 'NULL'
                    ]);
                    $project['image_url'] = $imageUrl;
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

        // Log all projects before passing to view
        \Log::info('Projects for projects page', [
            'total_projects' => count($projects),
            'projects' => array_map(function($p) {
                return [
                    'name' => $p['name'] ?? 'Unknown',
                    'has_image' => !empty($p['image']),
                    'has_image_url' => !empty($p['image_url']),
                    'image_url' => $p['image_url'] ?? null
                ];
            }, $projects)
        ]);

        return view('landing.projects', compact('projects'));
    }

    public function about()
    {
        // Get system name for default values
        $systemName = Setting::get('system_name', 'Our Company');

        // Load About Page settings
        $aboutPageHeroTitle = Setting::get('about_page_hero_title', 'About ' . $systemName);
        $aboutPageHeroSubtitle = Setting::get('about_page_hero_subtitle', 'Empowering education through technology');
        $aboutPageMission = Setting::get('about_page_mission', '');
        $aboutPageWhatWeOffer = Setting::get('about_page_what_we_offer', '');
        $aboutPageVision = Setting::get('about_page_vision', '');
        $aboutPageTeamTitle = Setting::get('about_page_team_title', 'Built for Education');
        $aboutPageTeamDescription = Setting::get('about_page_team_description', 'Designed by educators, for educators');
        $aboutPageCtaTitle = Setting::get('about_page_cta_title', 'Join Our Educational Community');
        $aboutPageCtaDescription = Setting::get('about_page_cta_description', 'Be part of the future of educational assessment');
        $aboutPageCtaButtonText = Setting::get('about_page_cta_button_text', 'Get Started Today');

        // Load About Page Features (4 cards)
        $aboutPageFeatures = [];
        for ($i = 1; $i <= 4; $i++) {
            $title = Setting::get("about_page_feature_{$i}_title", '');
            $description = Setting::get("about_page_feature_{$i}_description", '');
            if (!empty($title) || !empty($description)) {
                $aboutPageFeatures[] = [
                    'title' => $title,
                    'description' => $description,
                ];
            }
        }

        // Load About Page Team Features (3 items)
        $aboutPageTeamFeatures = [];
        for ($i = 1; $i <= 3; $i++) {
            $title = Setting::get("about_page_team_feature_{$i}_title", '');
            $description = Setting::get("about_page_team_feature_{$i}_description", '');
            if (!empty($title) || !empty($description)) {
                $aboutPageTeamFeatures[] = [
                    'title' => $title,
                    'description' => $description,
                ];
            }
        }

        return view('landing.about', compact(
            'aboutPageHeroTitle',
            'aboutPageHeroSubtitle',
            'aboutPageMission',
            'aboutPageWhatWeOffer',
            'aboutPageVision',
            'aboutPageTeamTitle',
            'aboutPageTeamDescription',
            'aboutPageCtaTitle',
            'aboutPageCtaDescription',
            'aboutPageCtaButtonText',
            'aboutPageFeatures',
            'aboutPageTeamFeatures'
        ));
    }

    public function news()
    {
        // Get published news
        $news = News::published()->latest()->paginate(12);
        
        return view('landing.news', compact('news'));
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

    /**
     * Proxy images from DigitalOcean Spaces to avoid CORS issues
     */
    public function imageProxy($path)
    {
        try {
            // Laravel automatically URL-decodes route parameters
            // We manually encoded +, /, and = as %2B, %2F, %3D
            // Laravel will decode %3D to =, %2F to /, and %2B to +
            // So the path should already be a valid base64 string
            $decodedPath = $path;

            // Replace spaces with + (in case Laravel converted + to spaces)
            $decodedPath = str_replace(' ', '+', $decodedPath);

            // Decode the base64 string
            $imagePath = base64_decode($decodedPath, true);

            if (empty($imagePath) || $imagePath === false) {
                \Log::warning('Failed to decode image path', [
                    'raw_path' => $path,
                    'decoded_path' => $decodedPath,
                    'path_length' => strlen($path ?? ''),
                    'first_50_chars' => substr($path ?? '', 0, 50),
                    'last_10_chars' => substr($path ?? '', -10),
                    'has_plus' => strpos($path, '+') !== false,
                    'has_space' => strpos($path, ' ') !== false,
                    'has_equals' => strpos($path, '=') !== false
                ]);
                abort(404, 'Image path not found');
            }

            // Validate the decoded path
            $imagePath = trim($imagePath);
            if (strlen($imagePath) < 2 || $imagePath === '/' || $imagePath === '\\') {
                \Log::warning('Invalid image path format', [
                    'path' => $imagePath,
                    'encoded_path' => $path,
                    'path_length' => strlen($imagePath)
                ]);
                abort(404, 'Invalid image path');
            }

            // Try digitalocean disk first, fallback to public disk
            $fileContents = null;
            $storage = null;
            
            // Check if digitalocean disk is configured
            $doConfig = config('filesystems.disks.digitalocean', []);
            $isDoConfigured = !empty($doConfig['bucket']) && !empty($doConfig['key']) && !empty($doConfig['secret']);
            
            if ($isDoConfigured) {
                try {
                    $storage = Storage::disk('digitalocean');
                    // Check if file exists
                    if ($storage->exists($imagePath)) {
                        $fileContents = $storage->get($imagePath);
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Failed to access digitalocean disk', [
                        'path' => $imagePath,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Fallback to public disk if digitalocean failed or not configured
            if ($fileContents === null) {
                try {
                    $storage = Storage::disk('public');
                    if ($storage->exists($imagePath)) {
                        $fileContents = $storage->get($imagePath);
                    } else {
                        \Log::warning('Image file does not exist in storage', [
                            'path' => $imagePath,
                            'disk' => 'public',
                            'decoded_from' => $path
                        ]);
                        abort(404, 'Image not found');
                    }
                } catch (\Throwable $e) {
                    \Log::error('Failed to access public disk', [
                        'path' => $imagePath,
                        'error' => $e->getMessage()
                    ]);
                    abort(404, 'Image not found');
                }
            }

            \Log::info('Image proxy success', [
                'image_path' => $imagePath,
                'encoded_path' => $path,
                'disk' => $isDoConfigured && $fileContents !== null ? 'digitalocean' : 'public'
            ]);

            // Determine content type based on file extension
            $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
            $contentType = match($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                default => 'image/jpeg'
            };

            // Return response with proper headers (including CORS)
            return Response::make($fileContents, 200, [
                'Content-Type' => $contentType,
                'Content-Length' => strlen($fileContents),
                'Cache-Control' => 'public, max-age=86400', // Cache for 24 hours
                'Access-Control-Allow-Origin' => '*', // Allow CORS
                'Access-Control-Allow-Methods' => 'GET',
                'Access-Control-Allow-Headers' => 'Content-Type',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error proxying image', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            abort(500, 'Error loading image');
        }
    }

    /**
     * Stream the privacy policy PDF
     */
    public function privacyPolicy()
    {
        $privacyPolicyPdfPath = Setting::get('privacy_policy_pdf');

        if (!$privacyPolicyPdfPath) {
            abort(404, 'Privacy Policy PDF not found.');
        }

        $assetDisk = 'digitalocean';

        try {
            if (Storage::disk($assetDisk)->exists($privacyPolicyPdfPath)) {
                // Try to get a temporary URL for streaming
                if (method_exists(Storage::disk($assetDisk), 'temporaryUrl')) {
                    $url = Storage::disk($assetDisk)->temporaryUrl($privacyPolicyPdfPath, now()->addMinutes(60));
                    return redirect($url);
                } else {
                    // Fallback: stream the file directly
                    $file = Storage::disk($assetDisk)->get($privacyPolicyPdfPath);
                    $fileName = basename($privacyPolicyPdfPath);
                    return Response::make($file, 200, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Fallback to public disk
            if (Storage::disk('public')->exists($privacyPolicyPdfPath)) {
                $file = Storage::disk('public')->get($privacyPolicyPdfPath);
                $fileName = basename($privacyPolicyPdfPath);
                return Response::make($file, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                ]);
            }
        }

        abort(404, 'Privacy Policy PDF not found.');
    }

    /**
     * Stream the TOR (Term of Reference) PDF
     */
    public function torPdf()
    {
        $torPdfPath = Setting::get('hiring_tor_pdf');

        if (!$torPdfPath) {
            abort(404, 'TOR PDF not found.');
        }

        $assetDisk = 'digitalocean';

        try {
            if (Storage::disk($assetDisk)->exists($torPdfPath)) {
                // Try to get a temporary URL for streaming
                if (method_exists(Storage::disk($assetDisk), 'temporaryUrl')) {
                    $url = Storage::disk($assetDisk)->temporaryUrl($torPdfPath, now()->addMinutes(60));
                    return redirect($url);
                } else {
                    // Fallback: stream the file directly
                    $file = Storage::disk($assetDisk)->get($torPdfPath);
                    $fileName = basename($torPdfPath);
                    return Response::make($file, 200, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Fallback to public disk
            if (Storage::disk('public')->exists($torPdfPath)) {
                $file = Storage::disk('public')->get($torPdfPath);
                $fileName = basename($torPdfPath);
                return Response::make($file, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                ]);
            }
        }

        abort(404, 'TOR PDF not found.');
    }
}
