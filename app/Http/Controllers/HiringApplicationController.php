<?php

namespace App\Http\Controllers;

use App\Models\HiringApplication;
use App\Models\Setting;
use App\Models\University;
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

        // School options for internship applicants pulled from universities table
        $schoolOptions = University::active()
            ->orderBy('name')
            ->get()
            ->map(function ($u) {
                return $u->full_name;
            })
            ->values()
            ->all();

        return view('hiring.apply', compact('position', 'settings', 'errors', 'schoolOptions'));
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

        // Basic school options for internship applicants; can be extended later
        $schoolOptions = University::active()
            ->orderBy('name')
            ->get()
            ->map(function ($u) {
                return $u->full_name;
            })
            ->values()
            ->all();

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
            return back()->withErrors(['error' => 'Invalid position.'])->withInput()->with('settings', $settings)->with('schoolOptions', $schoolOptions);
        }

        // Find position
        $position = \App\Models\HiringPosition::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        Log::info('Position lookup', ['position_found' => $position ? 'yes' : 'no', 'position_id' => $position?->id]);

        if (!$position) {
            Log::warning('Position not found', ['slug' => $slug]);
            return back()->withErrors(['error' => 'Position not found or is no longer available.'])->withInput()->with('settings', $settings)->with('schoolOptions', $schoolOptions);
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
                'school' => [
                    'nullable',
                    'string',
                    'max:255',
                    \Illuminate\Validation\Rule::requiredIf(function () use ($position, $request) {
                        return strcasecmp($position->employment_type ?? '', 'Internship') === 0
                            && !$request->filled('school_other');
                    }),
                    \Illuminate\Validation\Rule::in(array_merge($schoolOptions, ['__other'])),
                ],
                'school_other' => [
                    'nullable',
                    'string',
                    'max:255',
                    \Illuminate\Validation\Rule::requiredIf(function () use ($position, $request) {
                        return strcasecmp($position->employment_type ?? '', 'Internship') === 0
                            && $request->input('school') === '__other';
                    }),
                ],
                'cover_letter' => 'nullable|string|max:5000',
                'resume_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            ], [
                'phone.required' => 'Phone number is required.',
                'birth_date.before' => 'Birth date must be in the past.',
                'resume_file.required' => 'Please upload your resume.',
                'school.required' => 'Please select your school.',
                'school_other.required' => 'Please enter your school name.',
            ]);

            Log::info('Validation passed', ['validated_data' => $validated]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput()->with('settings', $settings)->with('schoolOptions', $schoolOptions);
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

            $resumePath = null;
            if ($request->hasFile('resume_file')) {
                $resumePath = $request->file('resume_file')->store($resumeDir, $assetDisk);
            }

            // Handle school selection - if "Other", create or find university
            $schoolName = null;
            if ($request->school === '__other' && $request->filled('school_other')) {
                $customSchoolName = trim($request->school_other);
                // Check if university already exists (case-insensitive)
                $university = University::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($customSchoolName)])
                    ->first();

                if (!$university) {
                    // Create new university
                    $university = University::create([
                        'name' => $customSchoolName,
                        'code' => Str::upper(Str::limit(Str::slug($customSchoolName), 10, '')),
                        'is_active' => true,
                    ]);
                    Log::info('New university created from application', [
                        'university_id' => $university->id,
                        'name' => $customSchoolName,
                    ]);
                }

                $schoolName = $university->full_name;
            } elseif ($request->filled('school') && $request->school !== '__other') {
                $schoolName = $request->school;
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
                'school' => $schoolName,
                'position_applied' => $position->title,
                'cover_letter' => $request->cover_letter ?: null,
                'cover_letter_path' => null,
                'resume_path' => $resumePath,
                'resume_link' => null,
                'status' => 'pending',
            ]);

            Log::info('Application created successfully', ['application_id' => $application->id]);

            // Update position application count
            $position->increment('application_count');

            // Send notification email to admin(s) if email notifications are enabled
            $emailNotificationsEnabled = \App\Models\Setting::get('hiring_email_notifications', 'enabled');
            if ($emailNotificationsEnabled === 'enabled') {
                try {
                    // Clear cache first to ensure we get the latest settings
                    \Illuminate\Support\Facades\Cache::forget('setting.hiring_admin_notification_email');
                    \Illuminate\Support\Facades\Cache::forget('setting.leave_admin_notification_email');

                    // Get admin notification emails (comma-separated) or fallback to single admin email
                    $adminEmailsStr = \App\Models\Setting::get('hiring_admin_notification_email', '');

                    // Also try direct database query as fallback
                    if (empty($adminEmailsStr)) {
                        $setting = \App\Models\Setting::where('key', 'hiring_admin_notification_email')->first();
                        $adminEmailsStr = $setting ? $setting->value : '';
                    }

                    Log::info('Hiring application notification - checking admin emails', [
                        'hiring_admin_emails' => $adminEmailsStr,
                        'application_id' => $application->id
                    ]);

                    // If no hiring-specific admin emails, try leave admin notification emails as fallback
                    if (empty($adminEmailsStr) || trim($adminEmailsStr) === '') {
                        $adminEmailsStr = \App\Models\Setting::get('leave_admin_notification_email', '');
                        // Also try direct database query as fallback
                        if (empty($adminEmailsStr)) {
                            $setting = \App\Models\Setting::where('key', 'leave_admin_notification_email')->first();
                            $adminEmailsStr = $setting ? $setting->value : '';
                        }
                        Log::info('Hiring application notification - using leave admin emails as fallback', [
                            'leave_admin_emails' => $adminEmailsStr,
                            'application_id' => $application->id
                        ]);
                    }

                    // If still empty, use system default email
                    if (empty($adminEmailsStr) || trim($adminEmailsStr) === '') {
                        $adminEmailsStr = \App\Models\Setting::get('mail_from_address', config('mail.from.address'));
                        Log::info('Hiring application notification - using system default email', [
                            'default_email' => $adminEmailsStr,
                            'application_id' => $application->id
                        ]);
                    }

                    if (!empty($adminEmailsStr) && trim($adminEmailsStr) !== '') {
                        // Parse comma-separated emails
                        $adminEmails = array_filter(array_map('trim', explode(',', $adminEmailsStr)));
                        $validEmails = array_filter($adminEmails, function ($email) {
                            return filter_var($email, FILTER_VALIDATE_EMAIL);
                        });

                        Log::info('Hiring application notification - parsed emails', [
                            'total_emails' => count($adminEmails),
                            'valid_emails' => count($validEmails),
                            'valid_emails_list' => $validEmails,
                            'application_id' => $application->id
                        ]);

                        if (!empty($validEmails)) {
                            // Configure mail settings before sending
                            \App\Services\MailConfigService::configure();

                            // Log mail configuration for debugging
                            Log::info('Hiring application notification - mail configuration', [
                                'mail_driver' => config('mail.default'),
                                'mail_from' => config('mail.from.address'),
                                'valid_emails_count' => count($validEmails)
                            ]);

                            $sentCount = 0;
                            $failedCount = 0;
                            foreach ($validEmails as $email) {
                                try {
                                    // Send email synchronously (not queued) to ensure immediate delivery
                                    \Illuminate\Support\Facades\Mail::to($email)
                                        ->send(new \App\Mail\HiringApplicationReceived($application, $position));
                                    $sentCount++;
                                    Log::info('Hiring application notification email sent successfully', [
                                        'email' => $email,
                                        'application_id' => $application->id
                                    ]);
                                } catch (\Exception $emailException) {
                                    $failedCount++;
                                    Log::error('Failed to send hiring application notification email to individual admin', [
                                        'email' => $email,
                                        'error' => $emailException->getMessage(),
                                        'application_id' => $application->id,
                                        'trace' => $emailException->getTraceAsString()
                                    ]);
                                    // Continue sending to other emails even if one fails
                                }
                            }
                            if ($sentCount > 0) {
                                Log::info('Hiring application notification emails sent', [
                                    'sent' => $sentCount,
                                    'failed' => $failedCount,
                                    'total' => count($validEmails),
                                    'application_id' => $application->id
                                ]);
                            } else {
                                Log::warning('Hiring application notification - no valid emails found after parsing', [
                                    'admin_emails_str' => $adminEmailsStr,
                                    'parsed_emails' => $adminEmails,
                                    'valid_emails' => $validEmails,
                                    'application_id' => $application->id
                                ]);
                            }
                        } else {
                            Log::info('Hiring application notification - no admin emails found in settings', [
                                'admin_emails_str' => $adminEmailsStr,
                                'application_id' => $application->id
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send admin notification email', [
                        'error' => $e->getMessage(),
                        'application_id' => $application->id,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            Log::info('Application created successfully, redirecting to success page', [
                'application_id' => $application->id,
                'position_title' => $position->title
            ]);

            // Redirect to success page with application ID and position title
            return redirect()->route('hiring.application.success', [
                'application_id' => $application->id,
                'position_title' => $position->title
            ])->with('application_id', $application->id)
              ->with('position_title', $position->title)
              ->with('success', true);
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
