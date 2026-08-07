<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DtrTimeRequest;
use App\Support\DtrTimeRequestHours;
use App\Support\StudentMeritNoticeSettings;
use App\Support\StudentMeritRulesNotice;
use App\Support\StudentUndertimeRulesViolation;
use App\Support\TimeRequestOvertimeLeaveImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DtrTimeRequestController extends Controller
{
    /**
     * Display all student time requests
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                abort(403, 'Authentication required.');
            }
            
            // Check if user has student_management permission or is admin
            // Note: Route middleware already checks this, but keeping as backup
            if (!$user->isAdmin() && !$user->canAccessStudentManagement()) {
                abort(403, 'Access denied. You do not have permission to access Student Management.');
            }

            $allowedDepartmentIds = $user->canAccessStudentManagement()
                ? $user->getAllowedStudentDepartmentIds()
                : null;

            // Backfill safeguard:
            // Ensure any existing pending regular request whose day total exceeds 08:00
            // has a pending Additional Time leave request linked in Leave Requests.
            $this->reconcilePendingAdditionalTimeLeaves($allowedDepartmentIds);
            $undertimeRulesResult = StudentUndertimeRulesViolation::reconcileForStudents($allowedDepartmentIds);

            $search = trim((string) $request->input('search', ''));

            $query = DtrTimeRequest::with(['user.university', 'reviewer'])
                ->whereHas('user', function ($q) use ($allowedDepartmentIds, $request, $search) {
                    $q->where('role', 'student');
                    if ($allowedDepartmentIds !== null) {
                        $q->whereIn('department_id', $allowedDepartmentIds);
                    }
                    if ($request->filled('university_id')) {
                        $q->where('university_id', $request->university_id);
                    }
                    if ($search !== '') {
                        $this->applyStudentUserSearch($q, $search);
                    }
                });

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by student
            if ($request->filled('student_id')) {
                $query->where('user_id', $request->student_id);
            }

            // Filter by date range (all dates by default if not specified)
            if ($request->filled('date_from')) {
                $query->whereDate('date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('date', '<=', $request->date_to);
            }

            // Sort: pending first, then approved/rejected
            // Within each status group, sort by date desc (newest dates first), then by created_at desc
            $query->orderByRaw("CASE 
                WHEN status = 'pending' THEN 1 
                WHEN status = 'approved' THEN 2 
                WHEN status = 'rejected' THEN 3 
                ELSE 4 
            END")
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

            $timeRequests = $query->paginate(20)->appends($request->query());

            // Global counts (all students) by status
            $baseStatusQuery = DtrTimeRequest::whereHas('user', function ($q) {
                $q->where('role', 'student');
            });
            if ($allowedDepartmentIds !== null) {
                $baseStatusQuery->whereHas('user', function ($q) use ($allowedDepartmentIds) {
                    $q->whereIn('department_id', $allowedDepartmentIds);
                });
            }

            $pendingCount = (clone $baseStatusQuery)->where('status', 'pending')->count();
            $rejectedCount = (clone $baseStatusQuery)->where('status', 'rejected')->count();

            $universities = \App\Models\University::where('is_active', true)
                ->orderBy('name')
                ->get();

            // Get all students for filter dropdown (optionally narrowed by school/search)
            $studentsQuery = \App\Models\User::where('role', 'student')
                ->where('is_active', true)
                ->when($allowedDepartmentIds !== null, function ($q) use ($allowedDepartmentIds) {
                    $q->whereIn('department_id', $allowedDepartmentIds);
                });

            if ($request->filled('university_id')) {
                $studentsQuery->where('university_id', $request->university_id);
            }
            if ($search !== '') {
                $this->applyStudentUserSearch($studentsQuery, $search);
            }

            $students = $studentsQuery->orderBy('name')->get();

            return view('admin.student-management.time-requests', compact(
                'timeRequests',
                'students',
                'universities',
                'pendingCount',
                'rejectedCount',
                'undertimeRulesResult'
            ));
        } catch (\Exception $e) {
            \Log::error('Error in DtrTimeRequestController@index: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);
            
            // Re-throw to see the actual error
            throw $e;
        }
    }

    /**
     * Update a pending or rejected time request (date, time, remarks).
     */
    public function update(Request $request, DtrTimeRequest $dtrTimeRequest)
    {
        $this->assertCanManageTimeRequest($dtrTimeRequest);

        if (! in_array($dtrTimeRequest->status, ['pending', 'rejected'], true)) {
            return back()->withErrors(['error' => 'Only pending or rejected time requests can be edited.']);
        }

        $validated = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $hours = DtrTimeRequestHours::timeStringToDecimal($validated['time']);
        if ($hours < 0 || $hours > 24) {
            return back()->withErrors(['time' => 'Time must be between 00:00 and 24:00.'])->withInput();
        }

        if ($dtrTimeRequest->isRegular() && $hours > DtrTimeRequestHours::STANDARD_DAY_HOURS) {
            return back()->withErrors(['time' => 'Regular time requests cannot exceed 08:00. Hours above 08:00 are filed as Additional Time in Leave Requests when the day total exceeds 08:00.'])->withInput();
        }

        $duplicate = DtrTimeRequest::query()
            ->where('user_id', $dtrTimeRequest->user_id)
            ->whereDate('date', $validated['date'])
            ->where('request_type', $dtrTimeRequest->request_type ?? 'regular')
            ->where('id', '!=', $dtrTimeRequest->id)
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['date' => 'This student already has a time request for the selected date.'])->withInput();
        }

        $wasRejected = $dtrTimeRequest->status === 'rejected';

        $dtrTimeRequest->update([
            'date' => $validated['date'],
            'hours' => $hours,
            'remarks' => $validated['remarks'] ?? null,
            'status' => 'pending',
            'admin_notes' => $wasRejected ? null : $dtrTimeRequest->admin_notes,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        $message = $wasRejected
            ? 'Time request updated and set back to pending for review.'
            : 'Time request updated successfully.';

        return back()->with('success', $message);
    }

    /**
     * Approve a time request
     */
    public function approve(Request $request, DtrTimeRequest $dtrTimeRequest)
    {
        $this->assertCanManageTimeRequest($dtrTimeRequest);

        if ($dtrTimeRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'This request has already been processed.']);
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $result = $this->processApproval($dtrTimeRequest, $validated['admin_notes'] ?? null);

        return back()->with('success', $this->buildApprovalSuccessMessage($result));
    }

    /**
     * Approve multiple pending time requests at once.
     */
    public function bulkApprove(Request $request)
    {
        $user = Auth::user();

        if (! $user->isAdmin()) {
            abort(403, 'Access denied. Only admins can bulk approve time requests.');
        }

        $validated = $request->validate([
            'request_ids' => ['required', 'array', 'min:1'],
            'request_ids.*' => ['integer', 'exists:dtr_time_requests,id'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $adminNotes = $validated['admin_notes'] ?? null;
        $approvedCount = 0;
        $skippedCount = 0;
        $additionalTimePendingCount = 0;
        $leaveImportedCount = 0;
        $warningEnabledCount = 0;
        $finalEnabledCount = 0;
        $warningDisabledCount = 0;

        $requests = DtrTimeRequest::with('user')
            ->whereIn('id', $validated['request_ids'])
            ->get();

        foreach ($requests as $dtrTimeRequest) {
            if ($dtrTimeRequest->status !== 'pending') {
                $skippedCount++;
                continue;
            }

            if ($dtrTimeRequest->user?->role !== 'student') {
                $skippedCount++;
                continue;
            }

            $allowedStudentDepartmentIds = $user->getAllowedStudentDepartmentIds();
            if ($allowedStudentDepartmentIds !== null) {
                $normalizedAllowedDepartmentIds = array_map('intval', $allowedStudentDepartmentIds);
                if (! in_array((int) $dtrTimeRequest->user->department_id, $normalizedAllowedDepartmentIds, true)) {
                    $skippedCount++;
                    continue;
                }
            }

            $result = $this->processApproval($dtrTimeRequest, $adminNotes);
            $approvedCount++;

            if (! empty($result['leave_imported'])) {
                $leaveImportedCount++;
            }
            if (! empty($result['attendance_additional_time_leave'])) {
                $additionalTimePendingCount++;
            }

            $automation = $result['automation'] ?? [];
            if (! empty($automation['final_enabled'])) {
                $finalEnabledCount++;
            } elseif (! empty($automation['enabled'])) {
                $warningEnabledCount++;
            } elseif (! empty($automation['disabled'])) {
                $warningDisabledCount++;
            }
        }

        if ($approvedCount === 0) {
            return back()->withErrors(['error' => 'No pending time requests were approved. Selected items may already be processed or outside your access.']);
        }

        $message = "Approved {$approvedCount} time request".($approvedCount === 1 ? '' : 's').' and applied hours to student DTR.';
        if ($skippedCount > 0) {
            $message .= " Skipped {$skippedCount}.";
        }
        if ($leaveImportedCount > 0) {
            $message .= " {$leaveImportedCount} Additional Time leave request(s) recorded in Leave Requests.";
        }
        if ($additionalTimePendingCount > 0) {
            $message .= " {$additionalTimePendingCount} pending Additional Time leave request(s) created for students to complete.";
        }
        if ($finalEnabledCount > 0) {
            $message .= " Final notice enabled for {$finalEnabledCount} student(s).";
        }
        if ($warningEnabledCount > 0) {
            $message .= " Rules violation warning enabled for {$warningEnabledCount} student(s).";
        }
        if ($warningDisabledCount > 0) {
            $message .= " Rules violation warning disabled for {$warningDisabledCount} student(s).";
        }

        return back()->with('success', $message);
    }

    /**
     * @return array{
     *     request: DtrTimeRequest,
     *     leave_imported: mixed,
     *     attendance_additional_time_leave: mixed,
     *     automation: array<string, mixed>
     * }
     */
    private function processApproval(DtrTimeRequest $dtrTimeRequest, ?string $adminNotes): array
    {
        DtrTimeRequestHours::applyApprovedRequestToDtr($dtrTimeRequest, $adminNotes);

        $dtrTimeRequest->update([
            'status' => 'approved',
            'admin_notes' => $adminNotes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $freshRequest = $dtrTimeRequest->fresh();

        $leaveImported = null;
        $attendanceAdditionalTimeLeave = null;
        if ($freshRequest->isOvertime()) {
            $leaveImported = TimeRequestOvertimeLeaveImport::syncFromApprovedTimeRequest(
                $freshRequest,
                Auth::id(),
                $adminNotes
            );
        } else {
            $attendanceAdditionalTimeLeave = TimeRequestOvertimeLeaveImport::ensurePendingAdditionalTimeFromRegularTimeRequest(
                $freshRequest
            );
        }

        $automation = StudentUndertimeRulesViolation::evaluateAfterApprovedTimeRequest($freshRequest);

        return [
            'request' => $freshRequest,
            'leave_imported' => $leaveImported,
            'attendance_additional_time_leave' => $attendanceAdditionalTimeLeave,
            'automation' => $automation,
        ];
    }

    /**
     * @param  array{
     *     request: DtrTimeRequest,
     *     leave_imported: mixed,
     *     attendance_additional_time_leave: mixed,
     *     automation: array<string, mixed>
     * }  $result
     */
    private function buildApprovalSuccessMessage(array $result): string
    {
        $freshRequest = $result['request'];
        $typeLabel = strtolower($freshRequest->request_type_label);
        $requestedTotal = (float) ($freshRequest->requested_total_hours ?? $freshRequest->hours);
        $overtimeHours = max($requestedTotal - DtrTimeRequestHours::STANDARD_DAY_HOURS, 0);

        $message = "Approved {$typeLabel} time request and applied hours to the student's DTR (counts toward required training time).";
        if ($overtimeHours > 0 && $freshRequest->isRegular()) {
            $message .= ' Additional Time of '.DtrTimeRequestHours::decimalToTimeString($overtimeHours)
                .' was included in DTR and remaining training hours.';
        }

        if ($result['leave_imported']) {
            $message .= ' An Additional Time leave request was recorded in Leave Requests ('.TimeRequestOvertimeLeaveImport::IMPORT_REMARK.').';
        } elseif ($result['attendance_additional_time_leave']) {
            $message .= ' A pending Additional Time leave request remains in Leave Requests for student details/documentation (hours are already on DTR).';
        }

        $automation = $result['automation'];
        if (! empty($automation['final_enabled'])) {
            $meritTotal = \App\Support\StudentViolationCounter::countForUser((int) $freshRequest->user_id);
            $message .= " Final notice (scrolling banner) was enabled automatically ({$meritTotal} merit(s) on record, maximum reached).";
        } elseif (! empty($automation['enabled'])) {
            $meritTotal = \App\Support\StudentViolationCounter::countForUser((int) $freshRequest->user_id);
            $meritThresholds = StudentMeritNoticeSettings::thresholds();
            if ($meritTotal >= $meritThresholds['warning'] && $meritTotal < $meritThresholds['final']) {
                $message .= " Student rules violation warning was enabled automatically ({$meritTotal} merit(s) on record).";
            } elseif ($meritTotal >= $meritThresholds['final']) {
                $message .= " Final notice (scrolling banner) was enabled automatically ({$meritTotal} merit(s) on record, maximum reached).";
            } else {
                $streak = StudentUndertimeRulesViolation::countConsecutiveApprovedUndertimeEndingOn(
                    (int) $freshRequest->user_id,
                    $freshRequest->date->copy()->startOfDay()
                );
                $message .= " Student rules violation warning was enabled automatically ({$streak} consecutive under-time day(s) below 08:00).";
            }
        } elseif (! empty($automation['disabled'])) {
            $streak = StudentUndertimeRulesViolation::countConsecutiveApprovedFullDaysEndingOn(
                (int) $freshRequest->user_id,
                $freshRequest->date->copy()->startOfDay()
            );
            $message .= " Student rules violation warning was disabled automatically ({$streak} consecutive day(s) at 08:00 or above).";
        }

        return $message;
    }

    /**
     * Reject a time request
     */
    public function reject(Request $request, DtrTimeRequest $dtrTimeRequest)
    {
        $this->assertCanManageTimeRequest($dtrTimeRequest);

        if ($dtrTimeRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'This request has already been processed.']);
        }

        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $dtrTimeRequest->update([
            'status' => 'rejected',
            'admin_notes' => $validated['admin_notes'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $dtrTimeRequest->loadMissing('user');
        if ($dtrTimeRequest->user?->role === 'student') {
            StudentMeritRulesNotice::syncForStudent($dtrTimeRequest->user);
        }

        return back()->with('success', 'Time request rejected.');
    }

    /**
     * Delete a rejected time request (only for super admins with full access)
     */
    public function destroy(DtrTimeRequest $dtrTimeRequest)
    {
        $user = Auth::user();
        
        // Only super admins (admins with full access) can delete
        if (!$user->isSuperAdmin()) {
            abort(403, 'Access denied. Only admins with full access can delete time requests.');
        }
        
        // Only allow deletion of rejected requests
        if ($dtrTimeRequest->status !== 'rejected') {
            return back()->withErrors(['error' => 'Only rejected time requests can be deleted.']);
        }

        $studentName = $dtrTimeRequest->user->name;
        $date = $dtrTimeRequest->date->format('M d, Y');
        
        $dtrTimeRequest->delete();

        return back()->with('success', "Rejected time request for {$studentName} on {$date} has been deleted.");
    }

    private function assertCanManageTimeRequest(DtrTimeRequest $dtrTimeRequest): void
    {
        $user = Auth::user();

        if (! $user->isAdmin() && ! $user->canAccessStudentManagement()) {
            abort(403, 'Access denied. You do not have permission to perform this action.');
        }

        $dtrTimeRequest->loadMissing('user');

        $allowedStudentDepartmentIds = $user->getAllowedStudentDepartmentIds();
        if ($allowedStudentDepartmentIds !== null) {
            $normalizedAllowedDepartmentIds = array_map('intval', $allowedStudentDepartmentIds);
            if (! in_array((int) $dtrTimeRequest->user->department_id, $normalizedAllowedDepartmentIds, true)) {
                abort(403, 'Access denied. You cannot manage this student department.');
            }
        }
    }

    /**
     * @param  array<int, int>|null  $allowedDepartmentIds
     */
    private function reconcilePendingAdditionalTimeLeaves(?array $allowedDepartmentIds): void
    {
        $query = DtrTimeRequest::query()
            ->where('status', 'pending')
            ->where(function ($q): void {
                $q->where('request_type', 'regular')
                    ->orWhereNull('request_type');
            })
            ->whereHas('user', function ($q) use ($allowedDepartmentIds): void {
                $q->where('role', 'student');
                if ($allowedDepartmentIds !== null) {
                    $q->whereIn('department_id', $allowedDepartmentIds);
                }
            })
            ->orderBy('id');

        $query->chunkById(150, function ($requests): void {
            foreach ($requests as $timeRequest) {
                $this->normalizePendingRegularRequest($timeRequest);
                TimeRequestOvertimeLeaveImport::ensurePendingAdditionalTimeFromRegularTimeRequest($timeRequest);
            }
        });
    }

    private function normalizePendingRegularRequest(DtrTimeRequest $timeRequest): void
    {
        $storedTotal = (float) ($timeRequest->requested_total_hours ?? 0);
        $baseHours = (float) $timeRequest->hours;

        // Legacy rows may have regular hours > 08:00 directly on the request.
        // Preserve the full total in requested_total_hours, cap regular hours to 08:00.
        if ($storedTotal <= 0) {
            $storedTotal = $baseHours;
        }

        $normalizedRegular = min($storedTotal, DtrTimeRequestHours::STANDARD_DAY_HOURS);

        $dirty = false;
        $update = [];

        if ($baseHours !== $normalizedRegular) {
            $update['hours'] = $normalizedRegular;
            $dirty = true;
        }

        if ((float) ($timeRequest->requested_total_hours ?? 0) !== $storedTotal) {
            $update['requested_total_hours'] = $storedTotal;
            $dirty = true;
        }

        if ($dirty) {
            $timeRequest->update($update);
            $timeRequest->refresh();
        }
    }

    /**
     * Partial match on student name, email, ID, or school (university).
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    private function applyStudentUserSearch($query, string $search): void
    {
        $search = trim($search);
        if ($search === '') {
            return;
        }

        $term = '%'.addcslashes($search, '%_\\').'%';

        $query->where(function ($q) use ($term, $search) {
            if (ctype_digit($search)) {
                $q->orWhere('id', (int) $search);
            }

            $q->orWhere('name', 'like', $term)
                ->orWhere('email', 'like', $term)
                ->orWhereHas('university', function ($uq) use ($term) {
                    $uq->where(function ($inner) use ($term) {
                        $inner->where('name', 'like', $term)
                            ->orWhere('code', 'like', $term);
                    });
                });
        });
    }

}
