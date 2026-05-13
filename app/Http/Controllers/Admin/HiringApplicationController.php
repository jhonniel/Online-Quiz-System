<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InternQuizAssignment;
use App\Models\HiringApplication;
use App\Models\Quiz;
use App\Models\QuizAssignment;
use App\Models\QuizAttemptHistory;
use App\Models\UserActivity;
use App\Services\MailConfigService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class HiringApplicationController extends Controller
{
    /**
     * OJT slots used = ongoing interns:
     * active students with required training hours set and still below required based on logged DTR total.
     */
    private function getOngoingInternsCount(): int
    {
        $userTable = (new \App\Models\User)->getTable();
        $dtrTable = (new \App\Models\Dtr)->getTable();

        return \App\Models\User::where('role', 'student')
            ->where('is_active', true)
            ->where('required_training_hours', '>', 0)
            ->whereRaw(
                "COALESCE((SELECT SUM({$dtrTable}.total_hours) FROM {$dtrTable} WHERE {$dtrTable}.user_id = {$userTable}.id), 0) < {$userTable}.required_training_hours"
            )
            ->count();
    }

    /**
     * Apply position-based filtering to the query based on user's allowed positions
     */
    private function applyPositionFilter($query)
    {
        $user = Auth::user();
        $allowedPositionIds = $user->getAllowedPositionIds();

        // If user has position restrictions, filter by allowed positions
        if ($allowedPositionIds !== null) {
            if (! empty($allowedPositionIds)) {
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

    /**
     * @return list<string>
     */
    private function resumeStorageDisks(): array
    {
        return ['digitalocean', 'spaces', 'public', 'local'];
    }

    /**
     * Stored path may omit or duplicate DIGITALOCEAN_SPACES_ROOT_PATH; try variants for object storage.
     *
     * @return list<string>
     */
    private function resumePathVariants(string $storedPath): array
    {
        $storedPath = ltrim($storedPath, '/');
        if ($storedPath === '') {
            return [];
        }

        $variants = [$storedPath];
        $root = trim((string) env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
        if ($root !== '') {
            if (str_starts_with($storedPath, $root.'/')) {
                $variants[] = substr($storedPath, strlen($root) + 1);
            } else {
                $variants[] = $root.'/'.$storedPath;
            }
        }

        return array_values(array_unique(array_filter($variants)));
    }

    /**
     * @return array{disk: string, path: string}|null
     */
    private function findResumeOnStorage(HiringApplication $application): ?array
    {
        if (! $application->resume_path) {
            return null;
        }

        foreach ($this->resumeStorageDisks() as $disk) {
            try {
                $storage = Storage::disk($disk);
                foreach ($this->resumePathVariants($application->resume_path) as $variant) {
                    if ($storage->exists($variant)) {
                        return ['disk' => $disk, 'path' => $variant];
                    }
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * Explicit Content-Type helps browsers render PDFs/images inside an iframe.
     *
     * @return array<string, string>
     */
    private function resumeInlineResponseHeaders(string $path): array
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $contentType = match ($ext) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        $filename = basename($path);

        return [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ];
    }

    /**
     * Internship applications in these statuses have a linked user and may receive intern quizzes.
     *
     * @return list<string>
     */
    private function internStatusesEligibleForInternQuiz(): array
    {
        return ['accepted', 'interview_scheduled', 'done_interview', 'hired'];
    }

    private function shouldShowInternQuizPanel(HiringApplication $application): bool
    {
        if (! $application->hiringPosition || ! $application->user_id) {
            return false;
        }

        if (strcasecmp((string) ($application->hiringPosition->employment_type ?? ''), 'Internship') !== 0) {
            return false;
        }

        return in_array($application->status, $this->internStatusesEligibleForInternQuiz(), true);
    }

    /**
     * Rank among completed assignments for the same quiz (best score desc, then earlier last_attempt_at).
     *
     * @param  Collection<int, QuizAssignment>  $assignments
     * @return array<int, array{rank: int, of: int}>
     */
    private function quizAssignmentRankMeta(Collection $assignments): array
    {
        if ($assignments->isEmpty()) {
            return [];
        }

        $meta = [];

        foreach ($assignments->groupBy('quiz_id') as $quizId => $group) {
            $orderedIds = QuizAssignment::query()
                ->where('quiz_id', $quizId)
                ->where('is_completed', true)
                ->orderByDesc('best_score')
                ->orderBy('last_attempt_at')
                ->pluck('id')
                ->values();

            $of = $orderedIds->count();

            foreach ($group as $asg) {
                if (! $asg->is_completed) {
                    continue;
                }

                $idx = $orderedIds->search(fn ($id) => (int) $id === (int) $asg->id);
                if ($idx !== false) {
                    $meta[$asg->id] = ['rank' => (int) $idx + 1, 'of' => $of];
                }
            }
        }

        return $meta;
    }

    public function index(Request $request)
    {
        $query = HiringApplication::with(['reviewer', 'user', 'hiringPosition']);

        // Apply position-based filtering first
        $query = $this->applyPositionFilter($query);

        $search = trim((string) $request->input('search', ''));
        $searchTokens = $search !== '' ? preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) : [];
        $dbDriver = DB::connection()->getDriverName();
        $idLikeSql = $dbDriver === 'pgsql' ? 'CAST(id AS TEXT) LIKE ?' : 'CAST(id AS CHAR) LIKE ?';

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
            $query->where(function ($q) use ($search, $searchTokens, $idLikeSql) {
                $q->orWhereRaw($idLikeSql, ["%{$search}%"])
                    ->orWhere('first_name', 'like', "%{$search}%")
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

        // Sort by custom status order, then applicant name A-Z
        $query->orderByRaw("
            CASE status
                WHEN 'pending' THEN 1
                WHEN 'accepted' THEN 2
                WHEN 'interview_scheduled' THEN 3
                WHEN 'done_interview' THEN 4
                WHEN 'rejected' THEN 5
                ELSE 99
            END ASC
        ");
        $query->orderByRaw('LOWER(last_name) ASC')
            ->orderByRaw('LOWER(first_name) ASC')
            ->orderBy('created_at', 'desc');

        $applications = $query->get();

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
            if (! empty($allowedPositionIds)) {
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
            $baseQuery->where(function ($q) use ($search, $searchTokens, $idLikeSql) {
                $q->orWhereRaw($idLikeSql, ["%{$search}%"])
                    ->orWhere('first_name', 'like', "%{$search}%")
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

        $userIds = collect($applications->items())->pluck('user_id')->filter()->unique()->values();
        $internQuizAssignmentsPage = collect();
        if ($userIds->isNotEmpty()) {
            $internQuizAssignmentsPage = QuizAssignment::query()
                ->whereIn('user_id', $userIds)
                ->select(['id', 'user_id', 'is_completed'])
                ->orderByDesc('assigned_at')
                ->get();
        }
        $internQuizByUserId = $internQuizAssignmentsPage->groupBy('user_id');

        return view('admin.hiring-applications.index', compact('applications', 'stats', 'positions', 'positionFilter', 'statusFilter', 'perPage', 'search', 'internQuizByUserId'));
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
                $year = (int) $matches[1];
                $month = (int) $matches[2];
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
            ->where(function ($query) use ($startOfCalendar, $endOfCalendar) {
                $query->where(function ($q) use ($startOfCalendar, $endOfCalendar) {
                    // If reviewed_at exists, use it
                    $q->whereNotNull('reviewed_at')
                        ->whereDate('reviewed_at', '>=', $startOfCalendar->toDateString())
                        ->whereDate('reviewed_at', '<=', $endOfCalendar->toDateString());
                })->orWhere(function ($q) use ($startOfCalendar, $endOfCalendar) {
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
                    'interview_format' => $application->interview_format ?? 'on_site',
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
            if (! isset($days[$key])) {
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
        if (! $this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to view applications for this position.');
        }

        $application->load(['reviewer', 'user', 'hiringPosition']);

        $assignableQuizzes = collect();
        $internQuizAssignments = collect();
        $internQuizRankMeta = [];
        $internQuizRecentAttempts = [];
        $showInternQuizPanel = $this->shouldShowInternQuizPanel($application);

        if ($showInternQuizPanel) {
            $assignableQuizzes = Quiz::query()
                ->where('is_active', true)
                ->orderBy('title')
                ->get(['id', 'title', 'quiz_code']);
        }

        if ($application->user_id && $showInternQuizPanel) {
            $internQuizAssignments = QuizAssignment::query()
                ->where('user_id', $application->user_id)
                ->with(['quiz:id,title,quiz_code,total_questions'])
                ->orderByDesc('assigned_at')
                ->get();
            $internQuizRankMeta = $this->quizAssignmentRankMeta(collect($internQuizAssignments));

            foreach ($internQuizAssignments as $asg) {
                $internQuizRecentAttempts[$asg->id] = QuizAttemptHistory::query()
                    ->where('quiz_id', $asg->quiz_id)
                    ->where('user_id', $asg->user_id)
                    ->orderByDesc('attempt_number')
                    ->limit(5)
                    ->get();
            }
        }

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

        return view('admin.hiring-applications.show', compact(
            'application',
            'activityLogs',
            'assignableQuizzes',
            'internQuizAssignments',
            'internQuizRankMeta',
            'internQuizRecentAttempts',
            'showInternQuizPanel',
        ));
    }

    /**
     * Full attempt list for an applicant's quiz assignment (hiring permission only; no Content management required).
     */
    public function showInternQuizAssignmentAttempts(HiringApplication $application, QuizAssignment $assignment)
    {
        if (! $this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied.');
        }

        $application->loadMissing('hiringPosition');

        if (! $this->shouldShowInternQuizPanel($application)) {
            abort(404);
        }

        if (! $application->user_id || (int) $assignment->user_id !== (int) $application->user_id) {
            abort(404);
        }

        $assignment->load(['quiz', 'user']);

        $attemptHistory = QuizAttemptHistory::query()
            ->where('quiz_id', $assignment->quiz_id)
            ->where('user_id', $assignment->user_id)
            ->orderByDesc('attempt_number')
            ->get();

        return view('admin.hiring-applications.quiz-assignment-attempts', compact(
            'application',
            'assignment',
            'attemptHistory',
        ));
    }

    /**
     * Assign one or more active quizzes to an internship applicant (from accepted onward); sends one email listing all selected quizzes when enabled.
     */
    public function assignInternQuiz(Request $request, HiringApplication $application)
    {
        if (! $this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'quiz_ids' => ['required', 'array', 'min:1'],
            'quiz_ids.*' => ['integer', 'distinct', 'exists:quizzes,id'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $quizIds = array_values(array_unique(array_map('intval', $validated['quiz_ids'])));

        $isInternship = $application->hiringPosition
            && strcasecmp((string) ($application->hiringPosition->employment_type ?? ''), 'Internship') === 0;

        $allowedStatuses = $this->internStatusesEligibleForInternQuiz();

        if (! $isInternship || ! in_array($application->status, $allowedStatuses, true) || ! $application->user_id) {
            return redirect('/admin/hiring-applications/'.$application->id)
                ->withErrors(['error' => 'Quiz assignment is only available for internship applications that are accepted (or later in the pipeline) and have an applicant user account.']);
        }

        $user = $application->user;
        if (! $user || ! in_array($user->role, ['student', 'applicant'], true)) {
            return redirect('/admin/hiring-applications/'.$application->id)
                ->withErrors(['error' => 'Applicant must have a user account with student access to assign a quiz.']);
        }

        $dueDate = ! empty($validated['due_date'])
            ? Carbon::parse($validated['due_date'])->endOfDay()
            : null;

        $assignedTitles = [];
        $assignedQuizzes = collect();
        $emailNotificationsEnabled = \App\Models\Setting::get('hiring_email_notifications', 'enabled');
        $emailFailure = false;

        foreach ($quizIds as $quizId) {
            $quiz = Quiz::query()
                ->where('id', $quizId)
                ->where('is_active', true)
                ->first();

            if (! $quiz) {
                continue;
            }

            $assignment = QuizAssignment::firstOrNew([
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
            ]);

            if (! $assignment->exists) {
                $assignment->assigned_at = now();
                $assignment->status = 'assigned';
                $assignment->is_completed = false;
            }

            if ($dueDate) {
                $assignment->due_date = $dueDate;
            }

            $assignment->save();
            $assignedTitles[] = $quiz->title;
            $assignedQuizzes->push($quiz);

            UserActivity::logActivity(
                Auth::user(),
                'action',
                'hiring_application_intern_quiz_assigned',
                [
                    'application_id' => $application->id,
                    'applicant_name' => $application->full_name,
                    'applicant_email' => $application->email,
                    'quiz_id' => $quiz->id,
                    'quiz_title' => $quiz->title,
                    'due_date' => $dueDate?->toIso8601String(),
                ]
            );
        }

        if ($emailNotificationsEnabled === 'enabled' && $assignedQuizzes->isNotEmpty()) {
            try {
                MailConfigService::configure();
                Mail::to($application->email)->send(new InternQuizAssignment(
                    $application,
                    $user,
                    $assignedQuizzes,
                    $dueDate
                ));
            } catch (\Throwable $e) {
                Log::error('Failed to send intern quiz assignment email', [
                    'error' => $e->getMessage(),
                    'application_id' => $application->id,
                    'quiz_ids' => $assignedQuizzes->pluck('id')->all(),
                ]);
                report($e);
                $emailFailure = true;
            }
        }

        if ($assignedTitles === []) {
            return redirect('/admin/hiring-applications/'.$application->id)
                ->withErrors(['error' => 'No valid active quizzes were selected.']);
        }

        $count = count($assignedTitles);
        $titlesList = implode(', ', $assignedTitles);
        $successCore = $count === 1
            ? 'Quiz "'.$assignedTitles[0].'" saved for '.$application->full_name.'.'
            : $count.' quizzes saved for '.$application->full_name.': '.$titlesList.'.';

        if ($emailNotificationsEnabled === 'enabled') {
            if (! $emailFailure) {
                $success = $successCore.' An email was sent listing all assigned quizzes with titles and quiz codes.';
            } else {
                return redirect('/admin/hiring-applications/'.$application->id)
                    ->with('success', $successCore.' The notification email could not be sent; check mail settings.');
            }
        } else {
            $success = $successCore.' Email notifications are disabled in settings; the applicant was not emailed.';
        }

        return redirect('/admin/hiring-applications/'.$application->id)->with('success', $success);
    }

    public function accept(Request $request, HiringApplication $application)
    {
        // Check if user can access this application's position
        if (! $this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to accept applications for this position.');
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
            'interview_date' => 'nullable|date|after_or_equal:now',
        ]);

        // Generate a random password for the applicant
        $password = \Illuminate\Support\Str::random(12);

        // Find university by matching school name from application
        $universityId = null;
        if ($application->school) {
            // Try to match by full_name first (includes location), then by name
            // PostgreSQL-compatible concatenation
            $university = \App\Models\University::where(function ($query) use ($application) {
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

        if (! $user) {
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

        // Update application (interview date/time is set via Schedule Interview, not required on accept)
        HiringApplication::withoutEvents(function () use ($application, $request, $user) {
            $application->update([
                'status' => 'accepted',
                'admin_notes' => $request->admin_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'interview_date' => $request->filled('interview_date') ? $request->interview_date : null,
                'user_id' => $user->id,
            ]);
        });

        // Refresh to get the properly formatted datetime
        $application->refresh();

        $application->loadMissing('hiringPosition');
        UserActivity::logActivity(
            Auth::user(),
            'action',
            'hiring_application_accepted',
            [
                'application_id' => $application->id,
                'applicant_name' => $application->full_name,
                'applicant_email' => $application->email,
                'position' => $application->hiringPosition->title ?? $application->position_applied,
                'admin_notes' => $request->admin_notes,
                'interview_date' => $application->interview_date?->toDateTimeString(),
                'user_id' => $user->id,
            ]
        );

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
                        null,
                        $application->hiringPosition
                    ));
            } catch (\Exception $e) {
                Log::error('Failed to send credentials email', [
                    'error' => $e->getMessage(),
                    'application_id' => $application->id,
                ]);
            }
        }

        return redirect('/admin/hiring-applications/'.$application->id)
            ->with('success', 'Application accepted. User account created and credentials sent via email.');
    }

    public function reject(Request $request, HiringApplication $application)
    {
        // Check if user can access this application's position
        if (! $this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to reject applications for this position.');
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        HiringApplication::withoutEvents(function () use ($application, $request) {
            $application->update([
                'status' => 'rejected',
                'admin_notes' => $request->admin_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
        });

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
                    'application_id' => $application->id,
                ]);
            }
        }

        return redirect('/admin/hiring-applications/'.$application->id)
            ->with('success', 'Application rejected.');
    }

    public function reconsider(Request $request, HiringApplication $application)
    {
        // Check if user can access this application's position
        if (! $this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to reconsider applications for this position.');
        }

        // Only allow full admins (not employees with limited access) to reconsider applications
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Only full administrators can reconsider applications.');
        }

        // Only allow reconsideration if application is rejected
        if ($application->status !== 'rejected') {
            return redirect('/admin/hiring-applications/'.$application->id)
                ->with('error', 'Only rejected applications can be reconsidered.');
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
            'interview_date' => 'nullable|date|after_or_equal:now',
        ]);

        // Generate a random password for the applicant
        $password = \Illuminate\Support\Str::random(12);

        // Find university by matching school name from application
        $universityId = null;
        if ($application->school) {
            // Try to match by full_name first (includes location), then by name
            // PostgreSQL-compatible concatenation
            $university = \App\Models\University::where(function ($query) use ($application) {
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

        if (! $user) {
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

        // Update application (interview date/time is set via Schedule Interview, not required on reconsider)
        HiringApplication::withoutEvents(function () use ($application, $request, $user) {
            $application->update([
                'status' => 'accepted',
                'admin_notes' => $request->admin_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'interview_date' => $request->filled('interview_date') ? $request->interview_date : null,
                'user_id' => $user->id,
            ]);
        });

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
                        null,
                        $application->hiringPosition
                    ));
            } catch (\Exception $e) {
                Log::error('Failed to send reconsideration email', [
                    'error' => $e->getMessage(),
                    'application_id' => $application->id,
                ]);
            }
        }

        return redirect('/admin/hiring-applications/'.$application->id)
            ->with('success', 'Application reconsidered and accepted. User account created and credentials sent via email.');
    }

    public function scheduleInterview(Request $request, HiringApplication $application)
    {
        // Check if user can access this application's position
        if (! $this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to schedule interviews for this position.');
        }

        $interviewFormat = $request->input('interview_format');
        $rules = [
            'admin_notes' => 'nullable|string|max:1000',
            'interview_date' => 'required|date|after_or_equal:now',
            'interview_format' => 'required|in:on_site,online',
        ];
        if ($interviewFormat === 'online') {
            $rules['interview_meeting_link'] = 'required|url|max:2048';
        } else {
            $rules['interview_meeting_link'] = 'nullable|string|max:2048';
        }
        $request->validate($rules);

        $meetingLink = $interviewFormat === 'online' ? $request->input('interview_meeting_link') : null;

        // Check if this is a reschedule (interview was already scheduled and date/time is changing)
        $isReschedule = $application->status === 'interview_scheduled' &&
                       $application->interview_date &&
                       $application->interview_date->format('Y-m-d H:i') !== date('Y-m-d H:i', strtotime($request->interview_date));

        HiringApplication::withoutEvents(function () use ($application, $request, $interviewFormat, $meetingLink) {
            $application->update([
                'status' => 'interview_scheduled',
                'admin_notes' => $request->admin_notes,
                'interview_date' => $request->interview_date,
                'interview_format' => $interviewFormat,
                'interview_meeting_link' => $meetingLink,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
        });

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
                'interview_format' => $interviewFormat,
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

                // Physical address only relevant for on-site interviews
                $address = $interviewFormat === 'on_site' ? \App\Models\Setting::get('contact_address') : null;

                Mail::to($application->email)
                    ->send(new \App\Mail\InterviewRescheduled(
                        $application,
                        $request->interview_date,
                        $request->admin_notes,
                        $application->hiringPosition,
                        $isReschedule,
                        $address,
                        $interviewFormat,
                        $meetingLink
                    ));

                Log::info('Interview '.($isReschedule ? 'rescheduled' : 'scheduled').' email sent successfully', [
                    'application_id' => $application->id,
                    'email' => $application->email,
                    'interview_date' => $request->interview_date,
                    'is_reschedule' => $isReschedule,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send interview '.($isReschedule ? 'reschedule' : 'schedule').' email', [
                    'error' => $e->getMessage(),
                    'application_id' => $application->id,
                    'email' => $application->email,
                ]);
            }
        }

        $successMessage = $isReschedule ? 'Interview rescheduled. Email notification sent to applicant.' : 'Interview scheduled. Email notification sent to applicant.';

        return redirect('/admin/hiring-applications/'.$application->id)
            ->with('success', $successMessage);
    }

    public function downloadResume(HiringApplication $application)
    {
        // Check if user can access this application's position
        if (! $this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to download resumes for this position.');
        }

        if (! $application->resume_path) {
            abort(404, 'Resume not found.');
        }

        $downloadName = $application->full_name.'_resume.'.pathinfo($application->resume_path, PATHINFO_EXTENSION);

        $hit = $this->findResumeOnStorage($application);
        if ($hit) {
            return Storage::disk($hit['disk'])->download($hit['path'], $downloadName);
        }

        abort(404, 'Resume not found.');
    }

    public function viewResume(HiringApplication $application)
    {
        // Check if user can access this application's position
        if (! $this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to view resumes for this position.');
        }

        if (! $application->resume_path) {
            abort(404, 'Resume not found.');
        }

        $hit = $this->findResumeOnStorage($application);
        if (! $hit) {
            abort(404, 'Resume not found.');
        }

        // Same-origin stream with explicit Content-Type so PDFs/images render inside an admin iframe
        return Storage::disk($hit['disk'])->response(
            $hit['path'],
            basename($hit['path']),
            $this->resumeInlineResponseHeaders($hit['path'])
        );
    }

    public function sendFollowUpEmail(Request $request, HiringApplication $application)
    {
        // Check if user can access this application's position
        if (! $this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to send follow-up emails for this position.');
        }

        // Only allow sending follow-up for scheduled interviews
        if ($application->status !== 'interview_scheduled') {
            return redirect('/admin/hiring-applications/'.$application->id)
                ->withErrors(['error' => 'Follow-up email can only be sent for scheduled interviews.']);
        }

        if (! $application->interview_date) {
            return redirect('/admin/hiring-applications/'.$application->id)
                ->withErrors(['error' => 'Interview date must be set before sending follow-up email.']);
        }

        // Only allow sending follow-up for past interviews (beyond today's date)
        if ($application->interview_date->gte(now())) {
            return redirect('/admin/hiring-applications/'.$application->id)
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

            return redirect('/admin/hiring-applications/'.$application->id)
                ->with('success', 'Follow-up email sent successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to send follow-up email: '.$e->getMessage());

            return redirect('/admin/hiring-applications/'.$application->id)
                ->withErrors(['error' => 'Failed to send follow-up email. Please try again.']);
        }
    }

    public function markInterviewDone(Request $request, HiringApplication $application)
    {
        // Check if user can access this application's position
        if (! $this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to mark interviews as done for this position.');
        }

        // Only allow marking interview as done if interview was scheduled
        if ($application->status !== 'interview_scheduled') {
            return redirect('/admin/hiring-applications/'.$application->id)
                ->withErrors(['error' => 'Can only mark interview as done if interview is scheduled.']);
        }

        // Update application status to done_interview
        HiringApplication::withoutEvents(function () use ($application, $request) {
            $application->update([
                'status' => 'done_interview',
                'admin_notes' => $request->admin_notes ?? $application->admin_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
        });

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

        return redirect('/admin/hiring-applications/'.$application->id)
            ->with('success', 'Interview marked as done.');
    }

    public function markAsHired(Request $request, HiringApplication $application)
    {
        // Check if user can access this application's position
        if (! $this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to mark applicants as hired for this position.');
        }

        // Only super admins can mark applicants as hired
        if (! Auth::user()->isSuperAdmin()) {
            abort(403, 'Access denied. Only super administrators can mark applicants as hired.');
        }

        // Check if this is an internship position - if so, redirect to accept intern
        $isInternship = $application->hiringPosition &&
                        strcasecmp($application->hiringPosition->employment_type ?? '', 'Internship') === 0;

        if ($isInternship) {
            return redirect('/admin/hiring-applications/'.$application->id)
                ->withErrors(['error' => 'Please use "Accept Intern" button for internship positions.']);
        }

        // Only allow marking as hired if interview is done, interview was scheduled, or application was accepted
        if ($application->status !== 'done_interview' && $application->status !== 'interview_scheduled' && $application->status !== 'accepted') {
            return redirect('/admin/hiring-applications/'.$application->id)
                ->withErrors(['error' => 'Can only mark as hired after interview is done, interview is scheduled, or application is accepted.']);
        }

        // Ensure user account exists
        if (! $application->user_id) {
            return redirect('/admin/hiring-applications/'.$application->id)
                ->withErrors(['error' => 'User account must be created first. Please accept the application first.']);
        }

        $user = $application->user;
        if (! $user) {
            return redirect('/admin/hiring-applications/'.$application->id)
                ->withErrors(['error' => 'User account not found.']);
        }

        // Store the previous status before updating
        $previousStatus = $application->status;

        // Update application status to hired
        HiringApplication::withoutEvents(function () use ($application, $request) {
            $application->update([
                'status' => 'hired',
                'admin_notes' => $request->admin_notes ?? $application->admin_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
        });

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
                    'application_id' => $application->id,
                ]);
            }
        }

        return redirect('/admin/hiring-applications/'.$application->id)
            ->with('success', 'Application marked as hired. User account is now active and can login.');
    }

    public function acceptIntern(Request $request, HiringApplication $application)
    {
        // Check if user can access this application's position
        if (! $this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to accept interns for this position.');
        }

        // Only super admins can accept interns
        if (! Auth::user()->isSuperAdmin()) {
            abort(403, 'Access denied. Only super administrators can accept interns.');
        }

        // Check if this is an internship position
        $isInternship = $application->hiringPosition &&
                        strcasecmp($application->hiringPosition->employment_type ?? '', 'Internship') === 0;

        if (! $isInternship) {
            return redirect('/admin/hiring-applications/'.$application->id)
                ->withErrors(['error' => 'This action is only available for internship positions.']);
        }

        // Only allow accepting intern if interview is done, interview was scheduled, or application was accepted
        if ($application->status !== 'done_interview' && $application->status !== 'interview_scheduled' && $application->status !== 'accepted') {
            return redirect('/admin/hiring-applications/'.$application->id)
                ->withErrors(['error' => 'Can only accept intern after interview is done, interview is scheduled, or application is accepted.']);
        }

        // Ensure user account exists
        if (! $application->user_id) {
            return redirect('/admin/hiring-applications/'.$application->id)
                ->withErrors(['error' => 'User account must be created first. Please accept the application first.']);
        }

        $user = $application->user;
        if (! $user) {
            return redirect('/admin/hiring-applications/'.$application->id)
                ->withErrors(['error' => 'User account not found.']);
        }

        // OJT slot capacity guard (configured in Admin Settings).
        $ojtTotalSlots = (int) \App\Models\Setting::get('ojt_total_slots', 0);
        if ($ojtTotalSlots > 0) {
            $currentUsedSlots = $this->getOngoingInternsCount();

            // Count this acceptance only when the user is not already an ongoing intern.
            $willConsumeNewSlot = ! ($user->role === 'student'
                && (bool) $user->is_active
                && (float) ($user->required_training_hours ?? 0) > 0);
            $projectedUsedSlots = $currentUsedSlots + ($willConsumeNewSlot ? 1 : 0);

            if ($projectedUsedSlots > $ojtTotalSlots) {
                return redirect('/admin/hiring-applications/'.$application->id)
                    ->withErrors([
                        'error' => "Cannot accept intern: OJT capacity would be exceeded ({$projectedUsedSlots}/{$ojtTotalSlots}). Increase total OJT slots in Admin Settings first.",
                    ]);
            }
        }

        // Store the previous status before updating
        $previousStatus = $application->status;

        // Update application status to hired
        HiringApplication::withoutEvents(function () use ($application, $request) {
            $application->update([
                'status' => 'hired',
                'admin_notes' => $request->admin_notes ?? $application->admin_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
        });

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
                    'application_id' => $application->id,
                ]);
            }
        }

        $successMessage = 'Intern accepted. User account is now active with student role and can login.';
        if (($ojtTotalSlots ?? 0) > 0) {
            $updatedUsedSlots = $this->getOngoingInternsCount();
            $remainingSlots = max($ojtTotalSlots - $updatedUsedSlots, 0);
            if ($remainingSlots <= 3) {
                $successMessage .= " Warning: OJT capacity is low ({$updatedUsedSlots}/{$ojtTotalSlots} used, {$remainingSlots} slots left).";
            }
        }

        return redirect('/admin/hiring-applications/'.$application->id)
            ->with('success', $successMessage);
    }

    public function cancelHired(Request $request, HiringApplication $application)
    {
        // Check if user can access this application's position
        if (! $this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to cancel hired status for this position.');
        }

        // Only allow canceling if application is hired
        if ($application->status !== 'hired') {
            return redirect('/admin/hiring-applications/'.$application->id)
                ->withErrors(['error' => 'Can only cancel hired applications.']);
        }

        // Ensure user account exists
        if (! $application->user_id) {
            return redirect('/admin/hiring-applications/'.$application->id)
                ->withErrors(['error' => 'User account not found.']);
        }

        $user = $application->user;
        if (! $user) {
            return redirect('/admin/hiring-applications/'.$application->id)
                ->withErrors(['error' => 'User account not found.']);
        }

        // Determine previous status - if interview was scheduled, go back to that, otherwise go to accepted
        $previousStatus = $application->interview_date ? 'interview_scheduled' : 'accepted';

        // Update application status
        HiringApplication::withoutEvents(function () use ($application, $request, $previousStatus) {
            $application->update([
                'status' => $previousStatus,
                'admin_notes' => $request->admin_notes ?? $application->admin_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
        });

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
                    'application_id' => $application->id,
                ]);
            }
        }

        return redirect('/admin/hiring-applications/'.$application->id)
            ->with('success', 'Hired status cancelled. User account has been deactivated.');
    }

    public function updateAdminNotes(Request $request, HiringApplication $application)
    {
        // Check if user can access this application's position
        if (! $this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to update admin notes for this position.');
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $application->update([
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect('/admin/hiring-applications/'.$application->id)
            ->with('success', 'Admin notes updated successfully.');
    }

    public function destroy(HiringApplication $application)
    {
        // Check if user can access this application's position
        if (! $this->canAccessPosition($application->hiring_position_id)) {
            abort(403, 'Access denied. You do not have permission to delete applications for this position.');
        }

        // Only allow full admins (not employees with limited access) to delete applications
        if (! Auth::user()->isAdmin()) {
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

        return redirect('/admin/hiring-applications')
            ->with('success', 'Application deleted successfully.');
    }
}
