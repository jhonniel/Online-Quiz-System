<?php

namespace App\Http\Controllers;

use App\Models\HiringApplication;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HiringApplicationController extends Controller
{
    /**
     * Show the hiring application form (public access - no authentication required)
     */
    public function show($slug = null)
    {
        // Check if public access to hiring applications is enabled
        $publicAccessEnabled = Setting::get('hiring_application_public_access', 'disabled');

        if ($publicAccessEnabled !== 'enabled') {
            abort(404, 'Hiring applications are currently not accepting new submissions.');
        }

        // If no slug provided, show list of available positions
        if (!$slug) {
            $positions = \App\Models\HiringPosition::where('is_active', true)
                ->where(function($query) {
                    $query->whereNull('application_deadline')
                          ->orWhere('application_deadline', '>=', now());
                })
                ->orderBy('title')
                ->get();

            $settings = [
                'hiring_process_description' => Setting::get('hiring_process_description', '') ?? '',
                'hiring_instructions' => Setting::get('hiring_instructions', '') ?? '',
                'hiring_stages' => Setting::get('hiring_stages', '') ?? '',
                'hiring_application_url' => Setting::get('hiring_application_url', 'hiring/apply') ?? 'hiring/apply',
            ];

        return view('hiring.positions', compact('positions', 'settings'));
        }

        // Find position by slug
        $position = \App\Models\HiringPosition::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Check if position is accepting applications
        if (!$position->isAcceptingApplications()) {
            abort(404, 'This position is no longer accepting applications.');
        }

        $settings = [
            'hiring_process_description' => Setting::get('hiring_process_description', '') ?? '',
            'hiring_instructions' => Setting::get('hiring_instructions', '') ?? '',
            'hiring_stages' => Setting::get('hiring_stages', '') ?? '',
            'hiring_application_url' => Setting::get('hiring_application_url', 'hiring/apply') ?? 'hiring/apply',
        ];

        // Ensure errors variable is available in the view
        // Laravel automatically shares $errors with views, but we'll ensure it's set
        $errors = session()->get('errors');
        if (!$errors) {
            $errors = new \Illuminate\Support\ViewErrorBag();
        }

        return view('hiring.apply', compact('position', 'settings', 'errors'));
    }

    /**
     * Store the hiring application (public access - no authentication required)
     */
    public function store(Request $request, $slug = null)
    {
        // Log the request for debugging
        Log::info('Hiring application store method called', [
            'slug' => $slug,
            'route_slug' => $request->route('slug'),
            'all_input' => $request->all(),
            'method' => $request->method(),
        ]);

        // Prepare settings for view (needed for redirects)
        $settings = [
            'hiring_process_description' => Setting::get('hiring_process_description', '') ?? '',
            'hiring_instructions' => Setting::get('hiring_instructions', '') ?? '',
            'hiring_stages' => Setting::get('hiring_stages', '') ?? '',
            'hiring_application_url' => Setting::get('hiring_application_url', 'hiring/apply') ?? 'hiring/apply',
        ];

        // Check if public access to hiring applications is enabled
        $publicAccessEnabled = Setting::get('hiring_application_public_access', 'disabled');

        Log::info('Public access check', ['enabled' => $publicAccessEnabled]);

        if ($publicAccessEnabled !== 'enabled') {
            Log::warning('Public access disabled');
            return back()->withErrors(['error' => 'Hiring applications are currently not accepting new submissions.'])->withInput()->with('settings', $settings);
        }

        // Get slug from route parameter or request
        $slug = $slug ?? $request->route('slug');

        Log::info('Slug resolved', ['slug' => $slug]);

        if (!$slug) {
            Log::warning('No slug found');
            return back()->withErrors(['error' => 'Invalid position.'])->withInput()->with('settings', $settings);
        }

        // Find position
        $position = \App\Models\HiringPosition::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        Log::info('Position lookup', ['position_found' => $position ? 'yes' : 'no', 'position_id' => $position?->id]);

        if (!$position) {
            Log::warning('Position not found', ['slug' => $slug]);
            return back()->withErrors(['error' => 'Position not found or is no longer available.'])->withInput()->with('settings', $settings);
        }

        if (!$position->isAcceptingApplications()) {
            Log::warning('Position not accepting applications', ['position_id' => $position->id]);
            return back()->withErrors(['error' => 'This position is no longer accepting applications.'])->withInput()->with('settings', $settings);
        }

        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'birth_date' => 'nullable|date|before:today',
                'address' => 'nullable|string|max:1000',
                'cover_letter' => 'nullable|string|max:5000',
                'cover_letter_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'resume_link' => 'nullable|url|max:500',
                'resume_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            ], [
                'phone.required' => 'Phone number is required.',
                'birth_date.before' => 'Birth date must be in the past.',
                'resume_link.url' => 'Please provide a valid URL for the resume link.',
                'resume_file.required' => 'Please upload your resume.',
            ]);

            Log::info('Validation passed', ['validated_data' => $validated]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput()->with('settings', $settings);
        }

        // Check if email already applied to this position (case-insensitive)
        $email = strtolower(trim($request->email));

        $existingApplication = HiringApplication::whereRaw('LOWER(TRIM(email)) = ?', [$email])
            ->where('hiring_position_id', $position->id)
            ->first();

        Log::info('Duplicate check performed', [
            'submitted_email' => $request->email,
            'normalized_email' => $email,
            'position_id' => $position->id,
            'existing_found' => $existingApplication ? 'yes' : 'no',
            'existing_id' => $existingApplication?->id
        ]);

        if ($existingApplication) {
            Log::warning('Duplicate application attempt blocked', [
                'email' => $email,
                'position_id' => $position->id,
                'existing_application_id' => $existingApplication->id,
                'existing_email' => $existingApplication->email
            ]);

            // Redirect back with error message and preserve all input
            $errorMessage = 'You have already applied to this position with this email address. Please use a different email address or contact HR if you need to update your application.';

            // Use withInput() to preserve all form data
            return redirect()->back()
                ->withErrors(['email' => $errorMessage])
                ->withInput($request->all())
                ->with('settings', $settings);
        }

        try {
            // Handle file uploads to DigitalOcean with root path
            $assetDisk = 'digitalocean';
            $assetRoot = trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
            $resumeDir = $assetRoot ? $assetRoot . '/hiring/resumes' : 'hiring/resumes';
            $coverDir = $assetRoot ? $assetRoot . '/hiring/cover-letters' : 'hiring/cover-letters';

            $resumePath = null;
            if ($request->hasFile('resume_file')) {
                $resumePath = $request->file('resume_file')->store($resumeDir, $assetDisk);
            }

            $coverLetterPath = null;
            if ($request->hasFile('cover_letter_file')) {
                $coverLetterPath = $request->file('cover_letter_file')->store($coverDir, $assetDisk);
            }

            // Create new application (normalize email to lowercase for consistency)
            $application = HiringApplication::create([
                'hiring_position_id' => $position->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => strtolower(trim($request->email)), // Normalize email
                'phone' => $request->phone,
                'birth_date' => $request->birth_date ?: null,
                'address' => $request->address ?: null,
                'position_applied' => $position->title,
                'cover_letter' => $request->cover_letter ?: null,
                'cover_letter_path' => $coverLetterPath,
                'resume_path' => $resumePath,
                'resume_link' => $request->resume_link,
                'status' => 'pending',
            ]);

            Log::info('Application created successfully', ['application_id' => $application->id]);

            // Update position application count
            $position->increment('application_count');

            // Send notification email to admin if email notifications are enabled
            $emailNotificationsEnabled = \App\Models\Setting::get('hiring_email_notifications', 'enabled');
            if ($emailNotificationsEnabled === 'enabled') {
                try {
                    // Get admin email or use system default
                    $adminEmail = \App\Models\Setting::get('mail_from_address', config('mail.from.address'));

                    if ($adminEmail) {
                        \Illuminate\Support\Facades\Mail::to($adminEmail)
                            ->send(new \App\Mail\HiringApplicationReceived($application, $position));
                        Log::info('Admin notification email sent', ['admin_email' => $adminEmail]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send admin notification email', [
                        'error' => $e->getMessage(),
                        'application_id' => $application->id
                    ]);
                }
            }

            Log::info('Application created successfully, redirecting to success page', [
                'application_id' => $application->id,
                'position_title' => $position->title
            ]);

            // Redirect to success page with application ID as query parameter
            // This ensures the data is available even if session fails
            return redirect()->route('hiring.application.success', [
                'application_id' => $application->id,
                'position_title' => $position->title
            ])->with('application_id', $application->id)
              ->with('position_title', $position->title);
        } catch (\Exception $e) {
            Log::error('Error creating hiring application', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'An error occurred while submitting your application: ' . $e->getMessage()])->withInput()->with('settings', $settings);
        }
    }

    /**
     * Show the success page after application submission
     */
    public function success(Request $request)
    {
        // Get application ID from session or request
        $applicationId = session()->get('application_id') ?? $request->get('application_id');
        $positionTitle = session()->get('position_title') ?? $request->get('position_title');

        Log::info('Success page accessed', [
            'application_id' => $applicationId,
            'position_title' => $positionTitle,
            'session_has_application_id' => session()->has('application_id'),
            'request_has_application_id' => $request->has('application_id'),
            'all_session_keys' => array_keys(session()->all()),
            'request_all' => $request->all()
        ]);

        // Always show success page - don't redirect to home
        // The success page will display regardless of whether we have the application ID
        return view('hiring.success', [
            'application_id' => $applicationId,
            'position_title' => $positionTitle
        ]);
    }

    public function acceptWithToken($token)
    {
        $application = HiringApplication::where('acceptance_token', $token)
            ->where('status', 'accepted')
            ->first();

        if (!$application || !$application->isTokenValid()) {
            abort(404, 'Invalid or expired acceptance link.');
        }

        return view('hiring.accept', compact('application'));
    }
}
