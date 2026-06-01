<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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

        $requests = $query->orderByDesc('created_at')->get();
        $grouped = $this->groupTeacherExcusedRequests($requests);

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $total = $grouped->count();
        $pageItems = $grouped->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $groupedRequests = new LengthAwarePaginator(
            $pageItems,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.teachers.teacher-excused-requests', [
            'groupedRequests' => $groupedRequests,
            'search' => $search,
            'status' => $status,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Group leave requests filed together by the same teacher (one form submission).
     *
     * @param  Collection<int, LeaveRequest>  $requests
     * @return Collection<int, array{
     *     key: string,
     *     teacher: \App\Models\User|null,
     *     filed_at: \Illuminate\Support\Carbon|null,
     *     start_date: \Illuminate\Support\Carbon|null,
     *     end_date: \Illuminate\Support\Carbon|null,
     *     reason: string,
     *     students: Collection<int, array{leave_request: LeaveRequest, user: \App\Models\User|null}>,
     *     status_summary: string,
     *     status_badge_class: string,
     * }>
     */
    private function groupTeacherExcusedRequests(Collection $requests): Collection
    {
        return $requests
            ->groupBy(fn (LeaveRequest $req) => $this->teacherExcusedBatchKey($req))
            ->map(function (Collection $items, string $key) {
                $sorted = $items->sortByDesc('created_at')->values();
                $first = $sorted->first();
                $log = $first?->logs->firstWhere('action', 'filed_by_teacher');
                $statuses = $sorted->pluck('status')->unique()->values();

                return [
                    'key' => $key,
                    'teacher' => $log?->performer,
                    'filed_at' => $sorted->max('created_at'),
                    'start_date' => $first?->start_date,
                    'end_date' => $first?->end_date,
                    'reason' => $this->cleanTeacherExcusedReason($first?->reason),
                    'students' => $sorted->map(fn (LeaveRequest $req) => [
                        'leave_request' => $req,
                        'user' => $req->user,
                    ])->values(),
                    'status_summary' => $statuses->count() === 1
                        ? ($sorted->first()?->display_status ?? 'Unknown')
                        : 'Mixed',
                    'status_badge_class' => $statuses->count() === 1
                        ? ($sorted->first()?->status_badge_class ?? 'bg-gray-100 text-gray-800')
                        : 'bg-amber-100 text-amber-800',
                ];
            })
            ->sortByDesc(fn (array $group) => $group['filed_at'])
            ->values();
    }

    private function teacherExcusedBatchKey(LeaveRequest $request): string
    {
        $log = $request->logs->firstWhere('action', 'filed_by_teacher');
        $teacherId = $log?->performed_by ?? 0;
        $cleanReason = $this->cleanTeacherExcusedReason($request->reason);
        $filedMinute = optional($request->created_at)->format('Y-m-d H:i') ?? '';

        return implode('|', [
            $teacherId,
            $request->start_date?->format('Y-m-d') ?? '',
            $request->end_date?->format('Y-m-d') ?? '',
            md5($cleanReason),
            $filedMinute,
        ]);
    }

    private function cleanTeacherExcusedReason(?string $reason): string
    {
        $text = preg_replace('/^Teacher excused request by .+\n\n/s', '', (string) $reason, 1);

        return $text !== '' ? $text : (string) $reason;
    }
}
