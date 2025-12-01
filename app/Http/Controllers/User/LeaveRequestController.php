<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Only allow employees to access
        if (Auth::user()->role !== 'employee') {
            abort(403, 'Only employees can access leave requests.');
        }

        $leaveRequests = LeaveRequest::where('user_id', Auth::id())
            ->with('reviewer')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total' => LeaveRequest::where('user_id', Auth::id())->count(),
            'pending' => LeaveRequest::where('user_id', Auth::id())->where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('user_id', Auth::id())->where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('user_id', Auth::id())->where('status', 'rejected')->count(),
        ];

        return view('user.leave-requests.index', compact('leaveRequests', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Only allow employees to access
        if (Auth::user()->role !== 'employee') {
            abort(403, 'Only employees can create leave requests.');
        }

        return view('user.leave-requests.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Only allow employees to access
        if (Auth::user()->role !== 'employee') {
            abort(403, 'Only employees can create leave requests.');
        }

        $validated = $request->validate([
            'type' => 'required|in:vacation_leave,sick_leave,work_from_home,absent,overtime',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:1000',
        ]);

        LeaveRequest::create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? $validated['start_date'],
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('user.leave-requests.index')
            ->with('success', 'Leave request submitted successfully. It will be reviewed by an administrator.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveRequest $leaveRequest)
    {
        // Only allow employees to access
        if (Auth::user()->role !== 'employee') {
            abort(403, 'Only employees can view leave requests.');
        }

        // Ensure the user can only view their own requests
        if ($leaveRequest->user_id !== Auth::id()) {
            abort(403, 'You can only view your own leave requests.');
        }

        $leaveRequest->load('reviewer');

        return view('user.leave-requests.show', compact('leaveRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        // Only allow employees to access
        if (Auth::user()->role !== 'employee') {
            abort(403, 'Only employees can delete leave requests.');
        }

        // Ensure the user can only delete their own requests
        if ($leaveRequest->user_id !== Auth::id()) {
            abort(403, 'You can only delete your own leave requests.');
        }

        // Only allow deletion of pending requests
        if (!$leaveRequest->isPending()) {
            return redirect()->back()
                ->withErrors(['error' => 'You can only delete pending leave requests.']);
        }

        $leaveRequest->delete();

        return redirect()->route('user.leave-requests.index')
            ->with('success', 'Leave request deleted successfully.');
    }
}
