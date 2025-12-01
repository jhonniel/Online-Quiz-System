<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of all leave requests.
     */
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['user', 'reviewer']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by employee
        if ($request->has('employee') && $request->employee) {
            $query->where('user_id', $request->employee);
        }

        // Search by employee name or email
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistics
        $baseQuery = LeaveRequest::query();
        if ($request->has('employee') && $request->employee) {
            $baseQuery->where('user_id', $request->employee);
        }
        if ($request->has('type') && $request->type) {
            $baseQuery->where('type', $request->type);
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        $employees = \App\Models\User::where('role', 'employee')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.leave-requests.index', compact('leaveRequests', 'stats', 'employees'));
    }

    /**
     * Display the specified leave request.
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['user', 'reviewer']);
        return view('admin.leave-requests.show', compact('leaveRequest'));
    }

    /**
     * Approve a leave request.
     */
    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $leaveRequest->update([
            'status' => 'approved',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.leave-requests.show', $leaveRequest)
            ->with('success', 'Leave request approved successfully.');
    }

    /**
     * Reject a leave request.
     */
    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.leave-requests.show', $leaveRequest)
            ->with('success', 'Leave request rejected successfully.');
    }

    /**
     * Resubmit a leave request (mark as pending again for correction).
     */
    public function resubmit(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $adminNotes = $request->admin_notes 
            ? ($leaveRequest->admin_notes ? $leaveRequest->admin_notes . "\n\n[Resubmission Request]: " . $request->admin_notes : $request->admin_notes)
            : $leaveRequest->admin_notes;

        $leaveRequest->update([
            'status' => 'pending',
            'admin_notes' => $adminNotes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.leave-requests.show', $leaveRequest)
            ->with('success', 'Leave request marked for resubmission. The employee will need to correct any errors.');
    }
}
