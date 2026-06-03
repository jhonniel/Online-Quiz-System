<?php

namespace App\Http\Controllers;

use App\Models\HiringApplication;
use App\Models\Setting;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class HiringApplicationController extends Controller
{
    /**
     * Show the hiring application form (public access - no authentication required)
     */
    public function show($slug = null)
    {
        $publicAccessEnabled = Setting::get('hiring_application_public_access', 'disabled');
        $disabledSettings = [
            'system_name' => Setting::get('system_name', config('app.name', 'Careers')),
            'hiring_application_url' => Setting::get('hiring_application_url', 'hiring/apply') ?? 'hiring/apply',
        ];

        if ($publicAccessEnabled !== 'enabled') {
            $positionTitle = null;
            if ($slug) {
                $positionTitle = \App\Models\HiringPosition::where('slug', $slug)->value('title');
            }

            return response()
                ->view('hiring.apply-disabled', [
                    'settings' => $disabledSettings,
                    'positionTitle' => $positionTitle,
                ], 503);
        }

        // If no slug provided, show list of available positions
        if (! $slug) {
            $positions = \App\Models\HiringPosition::where('is_active', true)
                ->where(function ($query) {
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
                'hiring_tor_pdf' => Setting::get('hiring_tor_pdf') ?? null,
                'privacy_policy_pdf' => Setting::get('privacy_policy_pdf') ?? null,
            ];

            return view('hiring.positions', compact('positions', 'settings'));
        }

        // Find position by slug
        $position = \App\Models\HiringPosition::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Check if position is accepting applications
        if (! $position->isAcceptingApplications()) {
            return response()
                ->view('hiring.apply-disabled', [
                    'settings' => $disabledSettings,
                    'positionTitle' => $position->title,
                    'closedReason' => 'deadline_or_inactive',
                ], 503);
        }

        $settings = [
            'hiring_process_description' => Setting::get('hiring_process_description', '') ?? '',
            'hiring_instructions' => Setting::get('hiring_instructions', '') ?? '',
            'hiring_stages' => Setting::get('hiring_stages', '') ?? '',
            'hiring_application_url' => Setting::get('hiring_application_url', 'hiring/apply') ?? 'hiring/apply',
            'hiring_tor_pdf' => Setting::get('hiring_tor_pdf') ?? null,
            'privacy_policy_pdf' => Setting::get('privacy_policy_pdf') ?? null,
        ];

        // Ensure errors variable is available in the view
        // Laravel automatically shares $errors with views, but we'll ensure it's set
        $errors = session()->get('errors');
        if (! $errors) {
            $errors = new \Illuminate\Support\ViewErrorBag;
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
            'hiring_tor_pdf' => Setting::get('hiring_tor_pdf') ?? null,
            'privacy_policy_pdf' => Setting::get('privacy_policy_pdf') ?? null,
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

            return back()
                ->withErrors(['error' => 'Hiring applications are currently not accepting new submissions.'])
                ->withInput()
                ->with('settings', $settings)
                ->with('schoolOptions', $schoolOptions);
        }

        // Get slug from route parameter or request
        $slug = $slug ?? $request->route('slug');

        Log::info('Slug resolved', ['slug' => $slug]);

        if (! $slug) {
            Log::warning('No slug found');

            return back()->withErrors(['error' => 'Invalid position.'])->withInput()->with('settings', $settings)->with('schoolOptions', $schoolOptions);
        }

        // Find position
        $position = \App\Models\HiringPosition::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        Log::info('Position lookup', ['position_found' => $position ? 'yes' : 'no', 'position_id' => $position?->id]);

        if (! $position) {
            Log::warning('Position not found', ['slug' => $slug]);

            return back()->withErrors(['error' => 'Position not found or is no longer available.'])->withInput()->with('settings', $settings)->with('schoolOptions', $schoolOptions);
        }

        if (! $position->isAcceptingApplications()) {
            Log::warning('Position not accepting applications', ['position_id' => $position->id]);

            return back()
                ->withErrors(['error' => 'This position is no longer accepting applications.'])
                ->withInput()
                ->with('settings', $settings)
                ->with('schoolOptions', $schoolOptions);
        }

        $isInternship = strcasecmp($position->employment_type ?? '', 'Internship') === 0;
        $maxBirthDate = now()->subYears(18)->format('Y-m-d');

        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:40',
            'birth_date' => [
                'required',
                'date',
                'after:1900-01-01',
                'before_or_equal:'.$maxBirthDate,
            ],
            'address' => 'required|string|min:10|max:1000',
            'cover_letter' => 'required|string|min:40|max:5000',
            'resume_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ];

        // Only internship posts need school in the allowed list; avoids stray/autofill values breaking non-internship applies.
        if ($isInternship) {
            $rules['school'] = [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(function () use ($request) {
                    return ! $request->filled('school_other');
                }),
                Rule::in(array_merge($schoolOptions, ['__other'])),
            ];
            $rules['school_other'] = [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(function () use ($request) {
                    return $request->input('school') === '__other';
                }),
            ];
        }

        try {
            $validated = $request->validate($rules, [
                'first_name.required' => 'Please enter your first name.',
                'last_name.required' => 'Please enter your last name.',
                'email.required' => 'Please enter a valid email address.',
                'email.email' => 'Please enter a valid email address.',
                'phone.required' => 'Phone number is required.',
                'phone.max' => 'Phone number is too long (maximum 40 characters).',
                'birth_date.required' => 'Please enter your date of birth.',
                'birth_date.before_or_equal' => 'You must be at least 18 years old to apply.',
                'birth_date.after' => 'Please enter a valid date of birth.',
                'address.required' => 'Please enter your address.',
                'address.min' => 'Please enter a complete address (at least 10 characters).',
                'cover_letter.required' => 'Please write a cover letter.',
                'cover_letter.min' => 'Cover letter must be at least 40 characters.',
                'cover_letter.max' => 'Cover letter may not exceed 5000 characters.',
                'resume_file.required' => 'Please upload your resume.',
                'resume_file.mimes' => 'Resume must be a PDF, Word document, or image (JPG/PNG).',
                'resume_file.max' => 'Resume must be 5 MB or smaller.',
                'school.required' => 'Please select your school or choose Other.',
                'school.in' => 'Please select a school from the list, or choose Other and enter your school name.',
                'school_other.required' => 'Please enter your school name.',
            ]);

            Log::info('Validation passed', ['validated_data' => $validated]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);

            return back()->withErrors($e->errors())->withInput()->with('settings', $settings)->with('schoolOptions', $schoolOptions);
        }

        // Check if email already applied to this position (case-insensitive).
        // Include trashed rows so we can clear stale unique-index rows before insert.
        $email = strtolower(trim($request->email));

        $existingApplication = HiringApplication::withTrashed()
            ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
            ->where('hiring_position_id', $position->id)
            ->first();

        Log::info('Duplicate check performed', [
            'submitted_email' => $request->email,
            'normalized_email' => $email,
            'position_id' => $position->id,
            'existing_found' => $existingApplication ? 'yes' : 'no',
            'existing_id' => $existingApplication?->id,
            'existing_trashed' => $existingApplication?->trashed() ? 'yes' : 'no',
        ]);

        if ($existingApplication && ! $existingApplication->trashed()) {
            Log::warning('Duplicate application attempt blocked', [
                'email' => $email,
                'position_id' => $position->id,
                'existing_application_id' => $existingApplication->id,
                'existing_email' => $existingApplication->email,
            ]);

            // Redirect back with error message and preserve all input
            $errorMessage = 'You have already applied to this position with this email address. Please use a different email address or contact HR if you need to update your application.';

            // Use withInput() to preserve all form data
            return redirect()->back()
                ->withErrors(['email' => $errorMessage])
                ->withInput($request->all())
                ->with('settings', $settings)
                ->with('schoolOptions', $schoolOptions);
        }

        if ($existingApplication && $existingApplication->trashed()) {
            // Unique (email, hiring_position_id) still counts soft-deleted rows in MySQL.
            $existingApplication->forceDelete();
        }

        try {
            // Store on Spaces when configured; otherwise public disk so admin preview/download works locally
            $assetDisk = $this->isDigitalOceanSpacesConfiguredForHiring() ? 'digitalocean' : 'public';
            $assetRoot = trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
            $resumeDir = $assetRoot ? $assetRoot.'/hiring/resumes' : 'hiring/resumes';

            $resumePath = null;
            if ($request->hasFile('resume_file')) {
                $resumePath = $request->file('resume_file')->store($resumeDir, $assetDisk);
                if ($resumePath === false) {
                    return back()
                        ->withErrors(['resume_file' => 'We could not save your resume. Please try again or use a smaller file.'])
                        ->withInput($request->except('resume_file'))
                        ->with('settings', $settings)
                        ->with('schoolOptions', $schoolOptions);
                }
            }

            // Handle school selection - if "Other", create or find university
            $schoolName = null;
            $universityId = null;
            if ($request->school === '__other' && $request->filled('school_other')) {
                $customSchoolName = trim($request->school_other);
                // Check if university already exists (case-insensitive)
                $university = University::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($customSchoolName)])
                    ->first();

                if (! $university) {
                    $slugForCode = Str::slug($customSchoolName);
                    $code = $slugForCode !== ''
                        ? Str::upper(Str::limit($slugForCode, 10, ''))
                        : null;

                    // Create new university
                    $university = University::create([
                        'name' => $customSchoolName,
                        'code' => $code,
                        'is_active' => true,
                    ]);
                    Log::info('New university created from application', [
                        'university_id' => $university->id,
                        'name' => $customSchoolName,
                    ]);
                }

                $universityId = $university->id;
                $schoolName = $university->full_name;
            } elseif ($request->filled('school') && $request->school !== '__other') {
                $schoolName = $request->school;
                $universityId = University::resolveIdFromSchoolLabel($schoolName);
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
                'university_id' => $universityId,
                'position_applied' => $position->title,
                'cover_letter' => $request->cover_letter ?: null,
                'cover_letter_path' => null,
                'resume_path' => $resumePath,
                'resume_link' => null,
                'status' => 'pending',
            ]);

            // Update position application count
            $position->increment('application_count');

            // Queue admin notification after the redirect so slow/failing SMTP never blocks the applicant.
            dispatch(fn () => static::notifyAdminsOfNewHiringApplication($application, $position))
                ->afterResponse();

            Log::info('Application created successfully, redirecting to success page', [
                'application_id' => $application->id,
                'position_title' => $position->title,
            ]);

            $successQuery = http_build_query([
                'application_id' => $application->id,
                'position_title' => $position->title,
            ]);

            // Use away() so Location stays a same-origin path (redirect()->to() would prepend APP_URL).
            return redirect()->away('/hiring/application/success?'.$successQuery)
                ->with('application_id', $application->id)
                ->with('position_title', $position->title)
                ->with('success', true);
        } catch (\Throwable $e) {
            Log::error('Error creating hiring application', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withErrors(['error' => 'An error occurred while submitting your application: '.$e->getMessage()])
                ->withInput()
                ->with('settings', $settings)
                ->with('schoolOptions', $schoolOptions);
        }
    }

    /**
     * Notify admins of a new hiring application. Dispatched after the HTTP response so slow or failing mail cannot block the applicant redirect.
     */
    private static function notifyAdminsOfNewHiringApplication(HiringApplication $application, \App\Models\HiringPosition $position): void
    {
        $emailNotificationsEnabled = Setting::get('hiring_email_notifications', 'enabled');
        if ($emailNotificationsEnabled !== 'enabled') {
            return;
        }

        try {
            \Illuminate\Support\Facades\Cache::forget('setting.hiring_admin_notification_email');
            \Illuminate\Support\Facades\Cache::forget('setting.leave_admin_notification_email');

            $adminEmailsStr = Setting::get('hiring_admin_notification_email', '');

            if (empty($adminEmailsStr)) {
                $setting = Setting::where('key', 'hiring_admin_notification_email')->first();
                $adminEmailsStr = $setting ? $setting->value : '';
            }

            Log::info('Hiring application notification - checking admin emails', [
                'hiring_admin_emails' => $adminEmailsStr,
                'application_id' => $application->id,
            ]);

            if (empty($adminEmailsStr) || trim($adminEmailsStr) === '') {
                $adminEmailsStr = Setting::get('leave_admin_notification_email', '');
                if (empty($adminEmailsStr)) {
                    $setting = Setting::where('key', 'leave_admin_notification_email')->first();
                    $adminEmailsStr = $setting ? $setting->value : '';
                }
                Log::info('Hiring application notification - using leave admin emails as fallback', [
                    'leave_admin_emails' => $adminEmailsStr,
                    'application_id' => $application->id,
                ]);
            }

            if (empty($adminEmailsStr) || trim($adminEmailsStr) === '') {
                $adminEmailsStr = Setting::get('mail_from_address', config('mail.from.address'));
                Log::info('Hiring application notification - using system default email', [
                    'default_email' => $adminEmailsStr,
                    'application_id' => $application->id,
                ]);
            }

            if (empty($adminEmailsStr) || trim($adminEmailsStr) === '') {
                return;
            }

            $adminEmails = array_filter(array_map('trim', explode(',', $adminEmailsStr)));
            $validEmails = array_filter($adminEmails, function ($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL);
            });

            Log::info('Hiring application notification - parsed emails', [
                'total_emails' => count($adminEmails),
                'valid_emails' => count($validEmails),
                'valid_emails_list' => $validEmails,
                'application_id' => $application->id,
            ]);

            if (empty($validEmails)) {
                Log::info('Hiring application notification - no admin emails found in settings', [
                    'admin_emails_str' => $adminEmailsStr,
                    'application_id' => $application->id,
                ]);

                return;
            }

            \App\Services\MailConfigService::configure();

            Log::info('Hiring application notification - mail configuration', [
                'mail_driver' => config('mail.default'),
                'mail_from' => config('mail.from.address'),
                'valid_emails_count' => count($validEmails),
            ]);

            $sentCount = 0;
            $failedCount = 0;
            foreach ($validEmails as $email) {
                try {
                    Mail::to($email)->send(new \App\Mail\HiringApplicationReceived($application, $position));
                    $sentCount++;
                    Log::info('Hiring application notification email sent successfully', [
                        'email' => $email,
                        'application_id' => $application->id,
                    ]);
                } catch (\Throwable $emailException) {
                    $failedCount++;
                    Log::error('Failed to send hiring application notification email to individual admin', [
                        'email' => $email,
                        'error' => $emailException->getMessage(),
                        'application_id' => $application->id,
                        'trace' => $emailException->getTraceAsString(),
                    ]);
                }
            }

            if ($sentCount > 0) {
                Log::info('Hiring application notification emails sent', [
                    'sent' => $sentCount,
                    'failed' => $failedCount,
                    'total' => count($validEmails),
                    'application_id' => $application->id,
                ]);
            } else {
                Log::warning('Hiring application notification - no emails sent successfully', [
                    'admin_emails_str' => $adminEmailsStr,
                    'application_id' => $application->id,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send admin notification email', [
                'error' => $e->getMessage(),
                'application_id' => $application->id,
                'trace' => $e->getTraceAsString(),
            ]);
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
            'request_all' => $request->all(),
        ]);

        // Always show success page - don't redirect to home
        // The success page will display regardless of whether we have the application ID
        return view('hiring.success', [
            'application_id' => $applicationId,
            'position_title' => $positionTitle,
        ]);
    }

    private function isDigitalOceanSpacesConfiguredForHiring(): bool
    {
        $d = config('filesystems.disks.digitalocean', []);

        return ! empty($d['bucket'])
            && ! empty($d['key'])
            && ! empty($d['secret'])
            && ! empty($d['endpoint']);
    }

    public function acceptWithToken($token)
    {
        $application = HiringApplication::where('acceptance_token', $token)
            ->where('status', 'accepted')
            ->first();

        if (! $application || ! $application->isTokenValid()) {
            abort(404, 'Invalid or expired acceptance link.');
        }

        return view('hiring.accept', compact('application'));
    }
}
