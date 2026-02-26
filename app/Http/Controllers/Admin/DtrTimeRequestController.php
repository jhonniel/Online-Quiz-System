<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DtrTimeRequest;
use App\Models\Dtr;
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
            
            $query = DtrTimeRequest::with(['user', 'reviewer'])
                ->whereHas('user', function ($q) {
                    $q->where('role', 'student');
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

            $pendingCount = (clone $baseStatusQuery)->where('status', 'pending')->count();
            $rejectedCount = (clone $baseStatusQuery)->where('status', 'rejected')->count();

            // Get all students for filter dropdown
            $students = \App\Models\User::where('role', 'student')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            return view('admin.student-management.time-requests', compact(
                'timeRequests',
                'students',
                'pendingCount',
                'rejectedCount'
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
     * Approve a time request
     */
    public function approve(Request $request, DtrTimeRequest $dtrTimeRequest)
    {
        $user = Auth::user();
        
        // Check if user has student_management permission or is admin
        if (!$user->isAdmin() && !$user->canAccessStudentManagement()) {
            abort(403, 'Access denied. You do not have permission to perform this action.');
        }
        
        if ($dtrTimeRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'This request has already been processed.']);
        }

        // Check if DTR already exists for this date
        $existingDtr = Dtr::where('user_id', $dtrTimeRequest->user_id)
            ->whereDate('date', $dtrTimeRequest->date)
            ->first();

        if ($existingDtr) {
            return back()->withErrors(['error' => 'DTR record already exists for this date.']);
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        // Create DTR record
        Dtr::create([
            'user_id' => $dtrTimeRequest->user_id,
            'date' => $dtrTimeRequest->date,
            'total_hours' => $dtrTimeRequest->hours,
            'overtime_hours' => 0,
            'status' => 'present',
            'remarks' => 'Approved time request' . ($dtrTimeRequest->remarks ? ': ' . $dtrTimeRequest->remarks : ''),
            'added_time_from_note' => $dtrTimeRequest->hours, // Mark as added time
        ]);

        // Update time request
        $dtrTimeRequest->update([
            'status' => 'approved',
            'admin_notes' => $validated['admin_notes'] ?? null,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Time request approved and DTR record created successfully.');
    }

    /**
     * Reject a time request
     */
    public function reject(Request $request, DtrTimeRequest $dtrTimeRequest)
    {
        $user = Auth::user();
        
        // Check if user has student_management permission or is admin
        if (!$user->isAdmin() && !$user->canAccessStudentManagement()) {
            abort(403, 'Access denied. You do not have permission to perform this action.');
        }
        
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
}
