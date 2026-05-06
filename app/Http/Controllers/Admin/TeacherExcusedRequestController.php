<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class TeacherExcusedRequestController extends Controller
{
    /**
     * Student excused (absent) requests filed by teachers on behalf of students.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = trim((string) $request->input('status', ''));
        $perPage = (int) $request->input('per_page', 20);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $query = LeaveRequest::query()
            ->where('type', 'absent')
            ->whereHas('logs', function ($q): void {
                $q->where('action', 'filed_by_teacher');
            })
            ->with([
                'user.university',
                'reviewer',
                'logs' => function ($q): void {
                    $q->where('action', 'filed_by_teacher')
                        ->orderBy('created_at')
                        ->with('performer');
                },
            ]);

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like): void {
                $q->whereHas('user', function ($uq) use ($like): void {
                    $uq->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                })->orWhereHas('logs', function ($lq) use ($like): void {
                    $lq->where('action', 'filed_by_teacher')
                        ->whereHas('performer', function ($pq) use ($like): void {
                            $pq->where('name', 'like', $like)
                                ->orWhere('email', 'like', $like);
                        });
                });
            });
        }

        $requests = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        return view('admin.teachers.teacher-excused-requests', [
            'requests' => $requests,
            'search' => $search,
            'status' => $status,
            'perPage' => $perPage,
        ]);
    }
}
