<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TeacherExcusedRequestController extends Controller
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder<LeaveRequest>
     */
    private function teacherExcusedBaseQuery()
    {
        return LeaveRequest::query()
            ->whereIn('type', ['absent', 'excused'])
            ->whereHas('logs', function ($q): void {
                $q->where('action', 'filed_by_teacher');
            });
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function buildGroupedFilings(string $search, string $statusFilter): \Illuminate\Support\Collection
    {
        $query = $this->teacherExcusedBaseQuery()
            ->with([
                'user.university',
                'reviewer',
                'logs' => function ($q): void {
                    $q->where('action', 'filed_by_teacher')
                        ->orderBy('created_at')
                        ->with('performer');
                },
            ]);

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

        $filings = $query
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(fn (LeaveRequest $request) => $request->teacherExcusedGroupKey())
            ->map(fn (\Illuminate\Support\Collection $group) => LeaveRequest::summarizeTeacherExcusedFiling($group))
            ->sortByDesc(fn (array $filing) => $filing['filed_at'])
            ->values();

        if ($statusFilter !== '') {
            $filings = $filings->filter(
                fn (array $filing) => $filing['requests']->contains('status', $statusFilter)
            )->values();
        }

        return $filings;
    }

    /**
     * Student excused (absent) requests filed by teachers — one admin row per teacher filing.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = trim((string) $request->input('status', ''));
        $perPage = (int) $request->input('per_page', 20);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $allFilings = $this->buildGroupedFilings($search, '');
        $stats = [
            'total' => $allFilings->count(),
            'pending' => $allFilings->filter(fn (array $f) => $f['requests']->contains('status', 'pending'))->count(),
            'approved' => $allFilings->filter(fn (array $f) => $f['requests']->every(fn (LeaveRequest $r) => $r->status === 'approved'))->count(),
            'rejected' => $allFilings->filter(fn (array $f) => $f['requests']->every(fn (LeaveRequest $r) => $r->status === 'rejected'))->count(),
        ];

        $filteredFilings = $status !== ''
            ? $this->buildGroupedFilings($search, $status)
            : $allFilings;

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedFilings = new LengthAwarePaginator(
            $filteredFilings->forPage($currentPage, $perPage)->values(),
            $filteredFilings->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.teachers.teacher-excused-requests', [
            'filings' => $paginatedFilings,
            'search' => $search,
            'status' => $status,
            'perPage' => $perPage,
            'stats' => $stats,
        ]);
    }
}
