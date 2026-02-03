<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HiringApplication;
use App\Models\UserActivity;
use App\Services\MailConfigService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class HiringApplicationController extends Controller
{
    /**
     * Apply position-based filtering to the query based on user's allowed positions
     */
    private function applyPositionFilter($query)
    {
        $user = Auth::user();
        $allowedPositionIds = $user->getAllowedPositionIds();

        // If user has position restrictions, filter by allowed positions
        if ($allowedPositionIds !== null) {
            if (!empty($allowedPositionIds)) {
                $query->whereIn('hiring_position_id', $allowedPositionIds);
            } else {
                // Empty array means no access
                $query->whereRaw('1 = 0'); // Return no results
            }
        }
        // If $allowedPositionIds is null, user can see all positions (super admin or no restrictions)

        return $query;
    }

    /**
     * Check if user can access a specific position
     */
    private function canAccessPosition($positionId)
    {
        $user = Auth::user();
        $allowedPositionIds = $user->getAllowedPositionIds();

        // Super admins or users with no restrictions can access all positions
        if ($allowedPositionIds === null) {
            return true;
        }

        // If empty array, no access
        if (empty($allowedPositionIds)) {
            return false;
        }

        // Check if position is in allowed list
        return in_array($positionId, $allowedPositionIds);
    }

    public function index(Request $request)
    {
        $query = HiringApplication::with(['reviewer', 'user', 'hiringPosition']);

        // Apply position-based filtering first
        $query = $this->applyPositionFilter($query);

        $search = trim((string) $request->input('search', ''));
        $searchTokens = $search !== '' ? preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) : [];

        // Filter by position if provided (but only if user has access to it)
        if ($request->has('position') && $request->position) {
            $positionId = $request->position;
            // Only apply filter if user can access this position
            if ($this->canAccessPosition($positionId)) {
                $query->where('hiring_position_id', $positionId);
            }
        }

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
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

        // Custom sorting: pending, accepted, interview_scheduled, done_interview, hired, rejected
        $statusOrder = [
            'pending' => 1,
            'accepted' => 2,
            'interview_scheduled' => 3,
            'done_interview' => 4,
            'hired' => 5,
            'rejected' => 6,
        ];
        
        $applications = $query->get()->sortBy(function ($application) use ($statusOrder) {
            return $statusOrder[$application->status] ?? 999;
        })->values();
        
        // Paginate manually
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = $perPage;
        $items = $applications->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $applications = new \Illuminate\Pagination\LengthAwarePaginator($items, $applications->count(), $perPage, $currentPage, [
            'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
        $applications->appends(request()->query());

        $positionFilter = $request->position;
        $statusFilter = $request->status;
        
        // Filter positions dropdown to only show allowed positions
        $user = Auth::user();
        $allowedPositionIds = $user->getAllowedPositionIds();
        if ($allowedPositionIds !== null) {
            if (!empty($allowedPositionIds)) {
                $positions = \App\Models\HiringPosition::whereIn('id', $allowedPositionIds)->orderBy('title')->get();
            } else {
                $positions = collect(); // No positions available
            }
        } else {
            $positions = \App\Models\HiringPosition::orderBy('title')->get();
        }

        $baseQuery = HiringApplication::query();
        // Apply position-based filtering to base query for stats
        $baseQuery = $this->applyPositionFilter($baseQuery);
        if ($positionFilter) {
            $baseQuery->where('hiring_position_id', $positionFilter);
        }
        if ($statusFilter) {
            $baseQuery->where('status', $statusFilter);
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

        return view('admin.hiring-applications.index', compact('applications', 'stats', 'positions', 'positionFilter', 'statusFilter', 'perPage', 'search'));
    }

    public function calendar(Request $request)
    {
        // Use Manila timezone for current date/month context
        $nowManila = Carbon::now('Asia/Manila');
        $monthParam = $request->input('month', $nowManila->format('Y-m'));

        try {
            // Parse the month parameter - ensure it's in Y-m format
            // Add '-01' to make it a complete date for parsing
            if (preg_match('/^(\d{4})-(\d{2})$/', $monthParam, $matches)) {
                $year = (int)$matches[1];
                $month = (int)$matches[2];
                // Validate month is between 1-12
                if ($month >= 1 && $month <= 12) {
                    $currentMonth = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila');
                } else {
                    throw new \Exception('Invalid month');
                }
            } else {
                throw new \Exception('Invalid format');
            }
        } catch (\Exception $e) {
            $currentMonth = $nowManila->copy()->startOfMonth();
        }

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        // Extend to full weeks for calendar grid
        $startOfCalendar = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endOfCalendar = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        // Get all applications with scheduled interviews in the calendar range
        $scheduledQuery = HiringApplication::with(['hiringPosition', 'user'])
            ->where('status', 'interview_scheduled')
            ->whereNotNull('interview_date')
            ->whereDate('interview_date', '>=', $startOfCalendar->toDateString())
            ->whereDate('interview_date', '<=', $endOfCalendar->toDateString());
        
        // Apply position-based filtering
        $scheduledQuery = $this->applyPositionFilter($scheduledQuery);
        $scheduledApplications = $scheduledQuery->get();

        // Get all accepted applications in the calendar range (use reviewed_at or created_at as the date)
        $acceptedQuery = HiringApplication::with(['hiringPosition', 'user'])
            ->where('status', 'accepted')
            ->where(function($query) use ($startOfCalendar, $endOfCalendar) {
                $query->where(function($q) use ($startOfCalendar, $endOfCalendar) {
                    // If reviewed_at exists, use it
                    $q->whereNotNull('reviewed_at')
                      ->whereDate('reviewed_at', '>=', $startOfCalendar->toDateString())
                      ->whereDate('reviewed_at', '<=', $endOfCalendar->toDateString());
                })->orWhere(function($q) use ($startOfCalendar, $endOfCalendar) {
                    // Otherwise use created_at
                    $q->whereNull('reviewed_at')
                      ->whereDate('created_at', '>=', $startOfCalendar->toDateString())
                      ->whereDate('created_at', '<=', $endOfCalendar->toDateString());
                });
            });
        
        // Apply position-based filtering
        $acceptedQuery = $this->applyPositionFilter($acceptedQuery);
        $acceptedApplications = $acceptedQuery->get();

        // Combine all applications
        $applications = $scheduledApplications->concat($acceptedApplications);

        // Prepare map of day => interview entries
        // Use ordered array to ensure all days are included
        $days = [];
        
        // Manually create all days from start to end (inclusive) to ensure nothing is missed
        $currentDate = $startOfCalendar->copy();
        while ($currentDate <= $endOfCalendar) {
            $key = $currentDate->toDateString();
            $days[$key] = [
                'date' => $currentDate->copy(),
                'interviews' => [],
            ];
            $currentDate->addDay();
        }

        // Add scheduled interviews to the corresponding days
        foreach ($scheduledApplications as $application) {
            $interviewDate = $application->interview_date->toDateString();
            if (isset($days[$interviewDate])) {
                $days[$interviewDate]['interviews'][] = [
                    'id' => $application->id,
                    'applicant_name' => $application->full_name,
                    'position' => $application->hiringPosition ? $application->hiringPosition->title : ($application->position_applied ?: 'N/A'),
                    'interview_time' => $application->interview_date->format('g:i A'),
                    'email' => $application->email,
                    'type' => 'interview',
                    'status' => 'scheduled',
                ];
            }
        }

        // Add accepted applications to the corresponding days
        foreach ($acceptedApplications as $application) {
            // Use reviewed_at if available, otherwise use created_at
            $acceptanceDate = $application->reviewed_at ? $application->reviewed_at : $application->created_at;
            $dateKey = $acceptanceDate->toDateString();
            
            if (isset($days[$dateKey])) {
                $days[$dateKey]['interviews'][] = [
                    'id' => $application->id,
                    'applicant_name' => $application->full_name,
                    'position' => $application->hiringPosition ? $application->hiringPosition->title : ($application->position_applied ?: 'N/A'),
                    'interview_time' => $acceptanceDate->format('g:i A'),
                    'email' => $application->email,
                    'type' => 'accepted',
                    'status' => 'accepted',
                ];
            }
        }

        // Group days into weeks - iterate through period again to maintain order
        $weeks = [];
        $week = [];
        $currentDate = $startOfCalendar->copy();
        
        // Iterate through all dates from start to end (inclusive)
        while ($currentDate <= $endOfCalendar) {
            $key = $currentDate->toDateString();
            
            // Ensure day exists in days array (create if missing)
            if (!isset($days[$key])) {
                $days[$key] = [
                    'date' => $currentDate->copy(),
                    'interviews' => [],
                ];
            }
            
            $week[] = $days[$key];
            
            // When we have 7 days, start a new week
            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }
            
            $currentDate->addDay();
        }
        
        // Add remaining days if any (final partial week - should be exactly 0 or 7, but handle edge cases)
        if (count($week) > 0) {
            $weeks[] = $week;
        }

        $prevMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();

        return view('admin.hiring-applications.calendar', compact(
            'weeks',
            'currentMonth',
            'prevMonth',
            'nextMonth',
            'applications',
            'scheduledApplications',
            'acceptedApplications'
        ));
    }

    public function show(HiringApplication $application)
    {
        // Check if user can access this application's position
        if (!$this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to view applications for this position.');
        }

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
        // Check if user can access this application's position
        if (!$this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to accept applications for this position.');
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
        // Check if user can access this application's position
        if (!$this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to reject applications for this position.');
        }

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
        // Check if user can access this application's position
        if (!$this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to reconsider applications for this position.');
        }

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
        // Check if user can access this application's position
        if (!$this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to schedule interviews for this position.');
        }

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

                // Get address from settings
                $address = \App\Models\Setting::get('contact_address');

                Mail::to($application->email)
                    ->send(new \App\Mail\InterviewRescheduled(
                        $application,
                        $request->interview_date,
                        $request->admin_notes,
                        $application->hiringPosition,
                        $isReschedule,
                        $address
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
        // Check if user can access this application's position
        if (!$this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to download resumes for this position.');
        }

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
        // Check if user can access this application's position
        if (!$this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to view resumes for this position.');
        }

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

    public function sendFollowUpEmail(Request $request, HiringApplication $application)
    {
        // Check if user can access this application's position
        if (!$this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to send follow-up emails for this position.');
        }

        // Only allow sending follow-up for scheduled interviews
        if ($application->status !== 'interview_scheduled') {
            return redirect()->route('admin.hiring-applications.show', $application)
                ->withErrors(['error' => 'Follow-up email can only be sent for scheduled interviews.']);
        }

        if (!$application->interview_date) {
            return redirect()->route('admin.hiring-applications.show', $application)
                ->withErrors(['error' => 'Interview date must be set before sending follow-up email.']);
        }

        // Only allow sending follow-up for past interviews (beyond today's date)
        if ($application->interview_date->gte(now())) {
            return redirect()->route('admin.hiring-applications.show', $application)
                ->withErrors(['error' => 'Follow-up email can only be sent for past interviews.']);
        }

        // Get social media link from settings
        $socialMediaLink = \App\Models\Setting::get('interview_reschedule_social_media_link');

        try {
            // Send follow-up email
            Mail::to($application->email)->send(
                new \App\Mail\InterviewFollowUp(
                    $application,
                    $application->interview_date,
                    $application->hiringPosition,
                    $socialMediaLink
                )
            );

            // Log the action
            UserActivity::logActivity(
                Auth::user(),
                'action',
                'hiring_application_follow_up_sent',
                [
                    'application_id' => $application->id,
                    'applicant_name' => $application->full_name,
                    'applicant_email' => $application->email,
                    'position' => $application->hiringPosition->title ?? $application->position_applied,
                    'interview_date' => $application->interview_date->format('Y-m-d H:i:s'),
                ]
            );

            return redirect()->route('admin.hiring-applications.show', $application)
                ->with('success', 'Follow-up email sent successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to send follow-up email: ' . $e->getMessage());
            return redirect()->route('admin.hiring-applications.show', $application)
                ->withErrors(['error' => 'Failed to send follow-up email. Please try again.']);
        }
    }

    public function markInterviewDone(Request $request, HiringApplication $application)
    {
        // Check if user can access this application's position
        if (!$this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to mark interviews as done for this position.');
        }

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
        // Check if user can access this application's position
        if (!$this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to mark applicants as hired for this position.');
        }

        // Only super admins can mark applicants as hired
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Access denied. Only super administrators can mark applicants as hired.');
        }

        // Check if this is an internship position - if so, redirect to accept intern
        $isInternship = $application->hiringPosition && 
                        strcasecmp($application->hiringPosition->employment_type ?? '', 'Internship') === 0;
        
        if ($isInternship) {
            return redirect()->route('admin.hiring-applications.show', $application)
                ->withErrors(['error' => 'Please use "Accept Intern" button for internship positions.']);
        }

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

    public function acceptIntern(Request $request, HiringApplication $application)
    {
        // Check if user can access this application's position
        if (!$this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to accept interns for this position.');
        }

        // Only super admins can accept interns
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Access denied. Only super administrators can accept interns.');
        }

        // Check if this is an internship position
        $isInternship = $application->hiringPosition && 
                        strcasecmp($application->hiringPosition->employment_type ?? '', 'Internship') === 0;
        
        if (!$isInternship) {
            return redirect()->route('admin.hiring-applications.show', $application)
                ->withErrors(['error' => 'This action is only available for internship positions.']);
        }

        // Only allow accepting intern if interview is done, interview was scheduled, or application was accepted
        if ($application->status !== 'done_interview' && $application->status !== 'interview_scheduled' && $application->status !== 'accepted') {
            return redirect()->route('admin.hiring-applications.show', $application)
                ->withErrors(['error' => 'Can only accept intern after interview is done, interview is scheduled, or application is accepted.']);
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

        // Prepare user update data - change role to student for interns
        $userUpdateData = [
            'is_approved' => true,
            'is_active' => true,
        ];

        // If previous status was done_interview and user is applicant, change role to student (not employee)
        if ($previousStatus === 'done_interview' && $user->role === 'applicant') {
            $userUpdateData['role'] = 'student';
        } elseif ($user->role === 'applicant') {
            // Also change role to student if status is interview_scheduled or accepted
            $userUpdateData['role'] = 'student';
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
            'employment_type' => 'Internship',
        ];

        // If role was changed from applicant to student, log it
        if (isset($userUpdateData['role']) && $userUpdateData['role'] === 'student') {
            $logMetadata['role_changed'] = true;
            $logMetadata['previous_role'] = 'applicant';
            $logMetadata['new_role'] = 'student';
        }

        UserActivity::logActivity(
            Auth::user(),
            'action',
            'hiring_application_intern_accepted',
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
                        $request->admin_notes ?? 'Congratulations! Your internship application has been accepted.',
                        $application->hiringPosition
                    ));
            } catch (\Exception $e) {
                Log::error('Failed to send intern acceptance email', [
                    'error' => $e->getMessage(),
                    'application_id' => $application->id
                ]);
            }
        }

        return redirect()->route('admin.hiring-applications.show', $application)
            ->with('success', 'Intern accepted. User account is now active with student role and can login.');
    }

    public function cancelHired(Request $request, HiringApplication $application)
    {
        // Check if user can access this application's position
        if (!$this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to cancel hired status for this position.');
        }

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
        // Check if user can access this application's position
        if (!$this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to update admin notes for this position.');
        }

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
        // Check if user can access this application's position
        if (!$this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to delete applications for this position.');
        }

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
