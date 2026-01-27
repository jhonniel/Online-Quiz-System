<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HiringApplication;
use App\Models\UserActivity;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class HiringApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = HiringApplication::with(['reviewer', 'user', 'hiringPosition']);

        $search = trim((string) $request->input('search', ''));
        $searchTokens = $search !== '' ? preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) : [];

        // Filter by position if provided
        if ($request->has('position') && $request->position) {
            $query->where('hiring_position_id', $request->position);
        }

        // Search
        if ($search !== '') {
            $query->where(function ($q) use ($search, $searchTokens) {
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }

                $q->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('position_applied', 'like', "%{$search}%")
                    ->orWhereHas('hiringPosition', function ($hp) use ($search) {
                        $hp->where('title', 'like', "%{$search}%");
                    });

                // Support searching full names like "Juan Dela Cruz" by requiring each token to match
                if (count($searchTokens) > 1) {
                    $q->orWhere(function ($andQ) use ($searchTokens) {
                        foreach ($searchTokens as $token) {
                            $andQ->where(function ($tokenQ) use ($token) {
                                $tokenQ->where('first_name', 'like', "%{$token}%")
                                    ->orWhere('last_name', 'like', "%{$token}%")
                                    ->orWhere('email', 'like', "%{$token}%")
                                    ->orWhere('phone', 'like', "%{$token}%")
                                    ->orWhere('status', 'like', "%{$token}%")
                                    ->orWhere('position_applied', 'like', "%{$token}%")
                                    ->orWhereHas('hiringPosition', function ($hp) use ($token) {
                                        $hp->where('title', 'like', "%{$token}%");
                                    });
                            });
                        }
                    });
                }
            });
        }

        // Get per page value (default 20, options: 10, 20, 50, 100)
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;

        $applications = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        $positionFilter = $request->position;
        $positions = \App\Models\HiringPosition::orderBy('title')->get();

        $baseQuery = HiringApplication::query();
        if ($positionFilter) {
            $baseQuery->where('hiring_position_id', $positionFilter);
        }
        if ($search !== '') {
            $baseQuery->where(function ($q) use ($search, $searchTokens) {
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }

                $q->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('position_applied', 'like', "%{$search}%")
                    ->orWhereHas('hiringPosition', function ($hp) use ($search) {
                        $hp->where('title', 'like', "%{$search}%");
                    });

                if (count($searchTokens) > 1) {
                    $q->orWhere(function ($andQ) use ($searchTokens) {
                        foreach ($searchTokens as $token) {
                            $andQ->where(function ($tokenQ) use ($token) {
                                $tokenQ->where('first_name', 'like', "%{$token}%")
                                    ->orWhere('last_name', 'like', "%{$token}%")
                                    ->orWhere('email', 'like', "%{$token}%")
                                    ->orWhere('phone', 'like', "%{$token}%")
                                    ->orWhere('status', 'like', "%{$token}%")
                                    ->orWhere('position_applied', 'like', "%{$token}%")
                                    ->orWhereHas('hiringPosition', function ($hp) use ($token) {
                                        $hp->where('title', 'like', "%{$token}%");
                                    });
                            });
                        }
                    });
                }
            });
        }

        $stats = [
            'total' => $baseQuery->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'accepted' => (clone $baseQuery)->where('status', 'accepted')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
            'interview_scheduled' => (clone $baseQuery)->where('status', 'interview_scheduled')->count(),
            'done_interview' => (clone $baseQuery)->where('status', 'done_interview')->count(),
        ];

        return view('admin.hiring-applications.index', compact('applications', 'stats', 'positions', 'positionFilter', 'perPage', 'search'));
    }

    public function show(HiringApplication $application)
    {
        $application->load(['reviewer', 'user', 'hiringPosition']);

        // Get activity logs for this application
        // Query all hiring application actions, then filter by application_id in metadata
        $activityLogs = \App\Models\UserActivity::where('activity_type', 'action')
            ->where('action', 'like', 'hiring_application_%')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function ($log) use ($application) {
                return isset($log->metadata['application_id']) &&
                       $log->metadata['application_id'] == $application->id;
            })
            ->values(); // Re-index the collection

        return view('admin.hiring-applications.show', compact('application', 'activityLogs'));
    }

    public function accept(Request $request, HiringApplication $application)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
            'interview_date' => 'required|date|after_or_equal:now',
        ]);

        // Generate a random password for the applicant
        $password = \Illuminate\Support\Str::random(12);

        // Find university by matching school name from application
        $universityId = null;
        if ($application->school) {
            // Try to match by full_name first (includes location), then by name
            // PostgreSQL-compatible concatenation
            $university = \App\Models\University::where(function($query) use ($application) {
                $query->whereRaw(
                    "name || CASE WHEN location IS NOT NULL AND location <> '' THEN ' (' || location || ')' ELSE '' END = ?",
                    [$application->school]
                )->orWhere('name', $application->school);
            })->first();

            if ($university) {
                $universityId = $university->id;
            }
        }

        // Check if user already exists with this email
        $user = \App\Models\User::where('email', $application->email)->first();

        if (!$user) {
            // Create new user account with role 'applicant'
            $userData = [
                'name' => $application->full_name,
                'email' => $application->email,
                'password' => \Illuminate\Support\Facades\Hash::make($password),
                'role' => 'applicant',
                'is_active' => true,
                'is_approved' => true,
            ];

            // Add university_id if found
            if ($universityId) {
                $userData['university_id'] = $universityId;
            }

            $user = \App\Models\User::create($userData);
        } else {
            // Update existing user to applicant role and activate
            $updateData = [
                'role' => 'applicant',
                'is_active' => true,
                'is_approved' => true,
                'password' => \Illuminate\Support\Facades\Hash::make($password), // Reset password
            ];

            // Add university_id if found (only update if not already set or if we found a match)
            if ($universityId) {
                $updateData['university_id'] = $universityId;
            }

            $user->update($updateData);
        }

        // Update application
        $application->update([
            'status' => 'accepted',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'interview_date' => $request->interview_date,
            'user_id' => $user->id,
        ]);

        // Refresh to get the properly formatted datetime
        $application->refresh();

        // Generate acceptance token (for backward compatibility)
        $token = $application->generateAcceptanceToken();

        // Send email with credentials
        $emailNotificationsEnabled = \App\Models\Setting::get('hiring_email_notifications', 'enabled');
        if ($emailNotificationsEnabled === 'enabled') {
            try {
                // Ensure mail configuration is up to date from settings
                MailConfigService::configure();

                Mail::to($application->email)
                    ->send(new \App\Mail\HiringApplicationCredentials(
                        $application,
                        $application->email,
                        $password,
                        $application->interview_date,
                        $application->hiringPosition
                    ));
            } catch (\Exception $e) {
                Log::error('Failed to send credentials email', [
                    'error' => $e->getMessage(),
                    'application_id' => $application->id
                ]);
            }
        }

        return redirect()->route('admin.hiring-applications.show', $application)
            ->with('success', 'Application accepted. User account created and credentials sent via email.');
    }

    public function reject(Request $request, HiringApplication $application)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $application->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Log the action
        UserActivity::logActivity(
            Auth::user(),
            'action',
            'hiring_application_rejected',
            [
                'application_id' => $application->id,
                'applicant_name' => $application->full_name,
                'applicant_email' => $application->email,
                'position' => $application->hiringPosition->title ?? $application->position_applied,
                'admin_notes' => $request->admin_notes,
            ]
        );

        // Send email notification to applicant if enabled
        $emailNotificationsEnabled = \App\Models\Setting::get('hiring_email_notifications', 'enabled');
        if ($emailNotificationsEnabled === 'enabled') {
            try {
                // Ensure mail configuration is up to date from settings
                MailConfigService::configure();

                Mail::to($application->email)
                    ->send(new \App\Mail\HiringApplicationStatusUpdate(
                        $application,
                        'rejected',
                        $request->admin_notes,
                        $application->hiringPosition
                    ));
            } catch (\Exception $e) {
                Log::error('Failed to send rejection email', [
                    'error' => $e->getMessage(),
                    'application_id' => $application->id
                ]);
            }
        }

        return redirect()->route('admin.hiring-applications.show', $application)
            ->with('success', 'Application rejected.');
    }

    public function reconsider(Request $request, HiringApplication $application)
    {
        // Only allow full admins (not employees with limited access) to reconsider applications
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only full administrators can reconsider applications.');
        }

        // Only allow reconsideration if application is rejected
        if ($application->status !== 'rejected') {
            return redirect()->route('admin.hiring-applications.show', $application)
                ->with('error', 'Only rejected applications can be reconsidered.');
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
            'interview_date' => 'required|date|after_or_equal:now',
        ]);

        // Generate a random password for the applicant
        $password = \Illuminate\Support\Str::random(12);

        // Find university by matching school name from application
        $universityId = null;
        if ($application->school) {
            // Try to match by full_name first (includes location), then by name
            // PostgreSQL-compatible concatenation
            $university = \App\Models\University::where(function($query) use ($application) {
                $query->whereRaw(
                    "name || CASE WHEN location IS NOT NULL AND location <> '' THEN ' (' || location || ')' ELSE '' END = ?",
                    [$application->school]
                )->orWhere('name', $application->school);
            })->first();

            if ($university) {
                $universityId = $university->id;
            }
        }

        // Check if user already exists with this email
        $user = \App\Models\User::where('email', $application->email)->first();

        if (!$user) {
            // Create new user account with role 'applicant'
            $userData = [
                'name' => $application->full_name,
                'email' => $application->email,
                'password' => \Illuminate\Support\Facades\Hash::make($password),
                'role' => 'applicant',
                'is_active' => true,
                'is_approved' => true,
            ];

            // Add university_id if found
            if ($universityId) {
                $userData['university_id'] = $universityId;
            }

            $user = \App\Models\User::create($userData);
        } else {
            // Update existing user to applicant role and activate
            $updateData = [
                'role' => 'applicant',
                'is_active' => true,
                'is_approved' => true,
                'password' => \Illuminate\Support\Facades\Hash::make($password), // Reset password
            ];

            // Add university_id if found (only update if not already set or if we found a match)
            if ($universityId) {
                $updateData['university_id'] = $universityId;
            }

            $user->update($updateData);
        }

        // Update application
        $application->update([
            'status' => 'accepted',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'interview_date' => $request->interview_date,
            'user_id' => $user->id,
        ]);

        // Refresh to get the properly formatted datetime
        $application->refresh();

        // Generate acceptance token (for backward compatibility)
        $token = $application->generateAcceptanceToken();

        // Log the action
        UserActivity::logActivity(
            Auth::user(),
            'action',
            'hiring_application_reconsidered',
            [
                'application_id' => $application->id,
                'applicant_name' => $application->full_name,
                'applicant_email' => $application->email,
                'position' => $application->hiringPosition->title ?? $application->position_applied,
                'interview_date' => $application->interview_date?->toDateTimeString(),
                'admin_notes' => $request->admin_notes,
                'user_account_created' => true,
                'user_id' => $user->id,
                'previous_status' => 'rejected',
            ]
        );

        // Send email with credentials (reconsideration message)
        $emailNotificationsEnabled = \App\Models\Setting::get('hiring_email_notifications', 'enabled');
        if ($emailNotificationsEnabled === 'enabled') {
            try {
                // Ensure mail configuration is up to date from settings
                MailConfigService::configure();

                Mail::to($application->email)
                    ->send(new \App\Mail\HiringApplicationReconsideration(
                        $application,
                        $application->email,
                        $password,
                        $application->interview_date,
                        $application->hiringPosition
                    ));
            } catch (\Exception $e) {
                Log::error('Failed to send reconsideration email', [
                    'error' => $e->getMessage(),
                    'application_id' => $application->id
                ]);
            }
        }

        return redirect()->route('admin.hiring-applications.show', $application)
            ->with('success', 'Application reconsidered and accepted. User account created and credentials sent via email.');
    }

    public function scheduleInterview(Request $request, HiringApplication $application)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
            'interview_date' => 'required|date|after_or_equal:now',
        ]);

        // Check if this is a reschedule (interview was already scheduled and date/time is changing)
        $isReschedule = $application->status === 'interview_scheduled' &&
                       $application->interview_date &&
                       $application->interview_date->format('Y-m-d H:i') !== date('Y-m-d H:i', strtotime($request->interview_date));

        $application->update([
            'status' => 'interview_scheduled',
            'admin_notes' => $request->admin_notes,
            'interview_date' => $request->interview_date,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Ensure user account is activated so they can login and take quizzes
        if ($application->user_id) {
            $user = $application->user;
            if ($user) {
                $user->update([
                    'is_approved' => true,
                    'is_active' => true,
                ]);
            }
        }

        // Log the action
        UserActivity::logActivity(
            Auth::user(),
            'action',
            $isReschedule ? 'hiring_application_interview_rescheduled' : 'hiring_application_interview_scheduled',
            [
                'application_id' => $application->id,
                'applicant_name' => $application->full_name,
                'applicant_email' => $application->email,
                'position' => $application->hiringPosition->title ?? $application->position_applied,
                'interview_date' => $request->interview_date,
                'admin_notes' => $request->admin_notes,
                'is_reschedule' => $isReschedule,
            ]
        );

        // Send email notification to applicant if enabled
        $emailNotificationsEnabled = \App\Models\Setting::get('hiring_email_notifications', 'enabled');
        if ($emailNotificationsEnabled === 'enabled') {
            try {
                // Ensure mail configuration is up to date from settings
                MailConfigService::configure();

                Mail::to($application->email)
                    ->send(new \App\Mail\InterviewRescheduled(
                        $application,
                        $request->interview_date,
                        $request->admin_notes,
                        $application->hiringPosition,
                        $isReschedule
                    ));

                Log::info('Interview ' . ($isReschedule ? 'rescheduled' : 'scheduled') . ' email sent successfully', [
                    'application_id' => $application->id,
                    'email' => $application->email,
                    'interview_date' => $request->interview_date,
                    'is_reschedule' => $isReschedule
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send interview ' . ($isReschedule ? 'reschedule' : 'schedule') . ' email', [
                    'error' => $e->getMessage(),
                    'application_id' => $application->id,
                    'email' => $application->email
                ]);
            }
        }

        $successMessage = $isReschedule ? 'Interview rescheduled. Email notification sent to applicant.' : 'Interview scheduled. Email notification sent to applicant.';

        return redirect()->route('admin.hiring-applications.show', $application)
            ->with('success', $successMessage);
    }

    public function downloadResume(HiringApplication $application)
    {
        if (!$application->resume_path) {
            abort(404, 'Resume not found.');
        }

        // Try digitalocean disk first
        try {
            if (Storage::disk('digitalocean')->exists($application->resume_path)) {
                return Storage::disk('digitalocean')->download(
                    $application->resume_path,
                    $application->full_name . '_resume.' . pathinfo($application->resume_path, PATHINFO_EXTENSION)
                );
            }
        } catch (\Throwable $e) {
            // Fallback to public disk
        }

        // Fallback to public disk
        if (Storage::disk('public')->exists($application->resume_path)) {
            return Storage::disk('public')->download(
                $application->resume_path,
                $application->full_name . '_resume.' . pathinfo($application->resume_path, PATHINFO_EXTENSION)
            );
        }

        abort(404, 'Resume not found.');
    }

    public function viewResume(HiringApplication $application)
    {
        if (!$application->resume_path) {
            abort(404, 'Resume not found.');
        }

        // Try digitalocean disk first
        try {
            if (Storage::disk('digitalocean')->exists($application->resume_path)) {
                $url = Storage::disk('digitalocean')->temporaryUrl(
                    $application->resume_path,
                    now()->addMinutes(30),
                    ['ResponseContentDisposition' => 'inline']
                );
                return redirect($url);
            }
        } catch (\Throwable $e) {
            // Fallback to public disk
        }

        // Fallback to public disk
        if (Storage::disk('public')->exists($application->resume_path)) {
            return Storage::disk('public')->response(
                $application->resume_path,
                null,
                ['Content-Disposition' => 'inline']
            );
        }

        abort(404, 'Resume not found.');
    }

    public function markInterviewDone(Request $request, HiringApplication $application)
    {
        // Only allow marking interview as done if interview was scheduled
        if ($application->status !== 'interview_scheduled') {
            return redirect()->route('admin.hiring-applications.show', $application)
                ->withErrors(['error' => 'Can only mark interview as done if interview is scheduled.']);
        }

        // Update application status to done_interview
        $application->update([
            'status' => 'done_interview',
            'admin_notes' => $request->admin_notes ?? $application->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Log the action
        UserActivity::logActivity(
            Auth::user(),
            'action',
            'hiring_application_interview_done',
            [
                'application_id' => $application->id,
                'applicant_name' => $application->full_name,
                'applicant_email' => $application->email,
                'position' => $application->hiringPosition->title ?? $application->position_applied,
                'admin_notes' => $request->admin_notes,
            ]
        );

        return redirect()->route('admin.hiring-applications.show', $application)
            ->with('success', 'Interview marked as done.');
    }

    public function markAsHired(Request $request, HiringApplication $application)
    {
        // Only allow marking as hired if interview is done, interview was scheduled, or application was accepted
        if ($application->status !== 'done_interview' && $application->status !== 'interview_scheduled' && $application->status !== 'accepted') {
            return redirect()->route('admin.hiring-applications.show', $application)
                ->withErrors(['error' => 'Can only mark as hired after interview is done, interview is scheduled, or application is accepted.']);
        }

        // Ensure user account exists
        if (!$application->user_id) {
            return redirect()->route('admin.hiring-applications.show', $application)
                ->withErrors(['error' => 'User account must be created first. Please accept the application first.']);
        }

        $user = $application->user;
        if (!$user) {
            return redirect()->route('admin.hiring-applications.show', $application)
                ->withErrors(['error' => 'User account not found.']);
        }

        // Store the previous status before updating
        $previousStatus = $application->status;

        // Update application status to hired
        $application->update([
            'status' => 'hired',
            'admin_notes' => $request->admin_notes ?? $application->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Prepare user update data
        $userUpdateData = [
            'is_approved' => true,
            'is_active' => true,
        ];

        // If previous status was done_interview and user is applicant, change role to employee
        if ($previousStatus === 'done_interview' && $user->role === 'applicant') {
            $userUpdateData['role'] = 'employee';
        }

        // Activate user account so they can login
        $user->update($userUpdateData);

        // Log the action
        $logMetadata = [
            'application_id' => $application->id,
            'applicant_name' => $application->full_name,
            'applicant_email' => $application->email,
            'position' => $application->hiringPosition->title ?? $application->position_applied,
            'admin_notes' => $request->admin_notes,
        ];

        // If role was changed from applicant to employee, log it
        if ($previousStatus === 'done_interview' && isset($userUpdateData['role']) && $userUpdateData['role'] === 'employee') {
            $logMetadata['role_changed'] = true;
            $logMetadata['previous_role'] = 'applicant';
            $logMetadata['new_role'] = 'employee';
        }

        UserActivity::logActivity(
            Auth::user(),
            'action',
            'hiring_application_hired',
            $logMetadata
        );

        // Send email notification to applicant if enabled
        $emailNotificationsEnabled = \App\Models\Setting::get('hiring_email_notifications', 'enabled');
        if ($emailNotificationsEnabled === 'enabled') {
            try {
                // Ensure mail configuration is up to date from settings
                MailConfigService::configure();

                Mail::to($application->email)
                    ->send(new \App\Mail\HiringApplicationStatusUpdate(
                        $application,
                        'hired',
                        $request->admin_notes ?? 'Congratulations! You have been hired.',
                        $application->hiringPosition
                    ));
            } catch (\Exception $e) {
                Log::error('Failed to send hired email', [
                    'error' => $e->getMessage(),
                    'application_id' => $application->id
                ]);
            }
        }

        return redirect()->route('admin.hiring-applications.show', $application)
            ->with('success', 'Application marked as hired. User account is now active and can login.');
    }

    public function cancelHired(Request $request, HiringApplication $application)
    {
        // Only allow canceling if application is hired
        if ($application->status !== 'hired') {
            return redirect()->route('admin.hiring-applications.show', $application)
                ->withErrors(['error' => 'Can only cancel hired applications.']);
        }

        // Ensure user account exists
        if (!$application->user_id) {
            return redirect()->route('admin.hiring-applications.show', $application)
                ->withErrors(['error' => 'User account not found.']);
        }

        $user = $application->user;
        if (!$user) {
            return redirect()->route('admin.hiring-applications.show', $application)
                ->withErrors(['error' => 'User account not found.']);
        }

        // Determine previous status - if interview was scheduled, go back to that, otherwise go to accepted
        $previousStatus = $application->interview_date ? 'interview_scheduled' : 'accepted';

        // Update application status
        $application->update([
            'status' => $previousStatus,
            'admin_notes' => $request->admin_notes ?? $application->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Deactivate user account so they cannot login
        $user->update([
            'is_approved' => false,
            'is_active' => false,
        ]);

        // Log the action
        UserActivity::logActivity(
            Auth::user(),
            'action',
            'hiring_application_hired_cancelled',
            [
                'application_id' => $application->id,
                'applicant_name' => $application->full_name,
                'applicant_email' => $application->email,
                'position' => $application->hiringPosition->title ?? $application->position_applied,
                'admin_notes' => $request->admin_notes,
                'previous_status' => 'hired',
                'new_status' => $previousStatus,
            ]
        );

        // Send email notification to applicant if enabled
        $emailNotificationsEnabled = \App\Models\Setting::get('hiring_email_notifications', 'enabled');
        if ($emailNotificationsEnabled === 'enabled') {
            try {
                // Ensure mail configuration is up to date from settings
                MailConfigService::configure();

                Mail::to($application->email)
                    ->send(new \App\Mail\HiringApplicationStatusUpdate(
                        $application,
                        'hired_cancelled',
                        $request->admin_notes ?? 'Your hiring status has been cancelled.',
                        $application->hiringPosition
                    ));
            } catch (\Exception $e) {
                Log::error('Failed to send hired cancellation email', [
                    'error' => $e->getMessage(),
                    'application_id' => $application->id
                ]);
            }
        }

        return redirect()->route('admin.hiring-applications.show', $application)
            ->with('success', 'Hired status cancelled. User account has been deactivated.');
    }

    public function updateAdminNotes(Request $request, HiringApplication $application)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $application->update([
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.hiring-applications.show', $application)
            ->with('success', 'Admin notes updated successfully.');
    }

    public function destroy(HiringApplication $application)
    {
        // Only allow full admins (not employees with limited access) to delete applications
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only full administrators can delete applications.');
        }

        // Soft delete the application (don't delete resume file, keep it for audit purposes)
        $application->delete();

        // Log the action
        UserActivity::logActivity(
            Auth::user(),
            'action',
            'hiring_application_deleted',
            [
                'application_id' => $application->id,
                'applicant_name' => $application->full_name,
                'applicant_email' => $application->email,
                'position' => $application->hiringPosition->title ?? $application->position_applied,
                'status' => $application->status,
            ]
        );

        return redirect()->route('admin.hiring-applications.index')
            ->with('success', 'Application deleted successfully.');
    }
}
