<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeedbackController extends Controller
{
    /**
     * Display a listing of feedbacks
     */
    public function index(Request $request)
    {
        $query = Feedback::with(['user', 'assignedAdmin']);
        $search = trim((string) $request->input('search', ''));

        $perPage = (int) $request->input('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100], true)) {
            $perPage = 15;
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($search !== '') {
            $query->where(function($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }

                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('priority', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('assignedAdmin', function($adminQuery) use ($search) {
                      $adminQuery->where('name', 'like', "%{$search}%")
                                 ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $feedbacks = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->query());

        // Get statistics
        $stats = [
            'total' => Feedback::count(),
            'pending' => Feedback::where('status', 'pending')->count(),
            'in_review' => Feedback::where('status', 'in_review')->count(),
            'in_progress' => Feedback::where('status', 'in_progress')->count(),
            'completed' => Feedback::where('status', 'completed')->count(),
            'rejected' => Feedback::where('status', 'rejected')->count(),
            'critical' => Feedback::where('priority', 'critical')->count(),
            'high' => Feedback::where('priority', 'high')->count(),
        ];

        return view('admin.feedback.index', compact('feedbacks', 'stats', 'search', 'perPage'));
    }

    /**
     * Display the specified feedback
     */
    public function show(Feedback $feedback)
    {
        $feedback->load(['user', 'assignedAdmin']);
        return view('admin.feedback.show', compact('feedback'));
    }

    /**
     * Update feedback status and response
     */
    public function update(Request $request, Feedback $feedback)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'in_review', 'in_progress', 'completed', 'rejected'])],
            'admin_response' => 'nullable|string|max:2000',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $validated['admin_responded_at'] = now();

        $feedback->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Feedback updated successfully!',
                'type' => 'success'
            ]);
        }

        return redirect()->route('admin.feedback.show', $feedback)
            ->with('success', 'Feedback updated successfully!');
    }

    /**
     * Assign feedback to an admin
     */
    public function assign(Request $request, Feedback $feedback)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $feedback->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Feedback assigned successfully!',
                'type' => 'success'
            ]);
        }

        return redirect()->back()
            ->with('success', 'Feedback assigned successfully!');
    }

    /**
     * Delete feedback
     */
    public function destroy(Feedback $feedback)
    {
        $feedback->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Feedback deleted successfully!',
                'type' => 'success'
            ]);
        }

        return redirect()->route('admin.feedback.index')
            ->with('success', 'Feedback deleted successfully!');
    }

    /**
     * Get feedback statistics for dashboard
     */
    public function getStats()
    {
        $stats = [
            'total' => Feedback::count(),
            'pending' => Feedback::where('status', 'pending')->count(),
            'in_review' => Feedback::where('status', 'in_review')->count(),
            'in_progress' => Feedback::where('status', 'in_progress')->count(),
            'completed' => Feedback::where('status', 'completed')->count(),
            'rejected' => Feedback::where('status', 'rejected')->count(),
            'critical' => Feedback::where('priority', 'critical')->count(),
            'high' => Feedback::where('priority', 'high')->count(),
            'recent' => Feedback::with(['user'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
            'by_type' => Feedback::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get(),
            'by_priority' => Feedback::selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get admins for assignment dropdown
     */
    public function getAdmins()
    {
        $admins = User::where('role', 'admin')
            ->select('id', 'name', 'email')
            ->get();

        return response()->json($admins);
    }
}
