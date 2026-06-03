<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DtrTimeRequest;
use App\Support\DtrTimeRequestHours;
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
            
            $query = DtrTimeRequest::with(['user', 'reviewer'])
                ->whereHas('user', function ($q) use ($allowedDepartmentIds) {
                    $q->where('role', 'student');
                    if ($allowedDepartmentIds !== null) {
                        $q->whereIn('department_id', $allowedDepartmentIds);
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

            // Get all students for filter dropdown
            $students = \App\Models\User::where('role', 'student')
                ->where('is_active', true)
                ->when($allowedDepartmentIds !== null, function ($q) use ($allowedDepartmentIds) {
                    $q->whereIn('department_id', $allowedDepartmentIds);
                })
                ->orderBy('name')
                ->get();

            return view('admin.student-management.time-requests', compact(
                'timeRequests',
                'students',
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

        DtrTimeRequestHours::applyApprovedRequestToDtr(
            $dtrTimeRequest,
            $validated['admin_notes'] ?? null
        );

        $dtrTimeRequest->update([
            'status' => 'approved',
            'admin_notes' => $validated['admin_notes'] ?? null,
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
                $validated['admin_notes'] ?? null
            );
        } else {
            $attendanceAdditionalTimeLeave = TimeRequestOvertimeLeaveImport::ensurePendingAdditionalTimeFromRegularTimeRequest(
                $freshRequest
            );
        }

        $typeLabel = strtolower($freshRequest->request_type_label);
        $message = "Approved {$typeLabel} time request and applied hours to the student's DTR (counts toward required training time).";
        if ($leaveImported) {
            $message .= ' An Additional Time leave request was recorded in Leave Requests ('.TimeRequestOvertimeLeaveImport::IMPORT_REMARK.').';
        } elseif ($attendanceAdditionalTimeLeave) {
            $message .= ' A pending Additional Time request was created in Leave Requests — the student must complete the details before it can be approved.';
        }

        $automation = StudentUndertimeRulesViolation::evaluateAfterApprovedTimeRequest($freshRequest);
        if ($automation['disabled']) {
            $streak = StudentUndertimeRulesViolation::countConsecutiveApprovedFullDaysEndingOn(
                (int) $freshRequest->user_id,
                $freshRequest->date->copy()->startOfDay()
            );
            $message .= " Student rules violation warning was disabled automatically ({$streak} consecutive day(s) at 08:00 or above).";
        } elseif ($automation['enabled']) {
            $streak = StudentUndertimeRulesViolation::countConsecutiveApprovedUndertimeEndingOn(
                (int) $freshRequest->user_id,
                $freshRequest->date->copy()->startOfDay()
            );
            $message .= " Student rules violation warning was enabled automatically ({$streak} consecutive under-time day(s) below 08:00).";
        }

        return back()->with('success', $message);
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

}
