<?php

namespace App\Services;

use App\Mail\LeaveRequestStatusUpdate;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LeaveRequestIncompleteAttendanceOvertimeService
{
    /** Days after Record Attendance filing to complete overtime details before auto-rejection. */
    public const RESPONSE_WINDOW_DAYS = 3;

    public function completionDeadlineAt(LeaveRequest $leaveRequest): Carbon
    {
        return Carbon::parse($leaveRequest->created_at)
            ->timezone(config('app.timezone'))
            ->addDays(self::RESPONSE_WINDOW_DAYS);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function reminderModalPayloadForUser(User $user): array
    {
        if ($user->role !== 'student') {
            return [];
        }

        return LeaveRequest::query()
            ->where('user_id', $user->id)
            ->awaitingAttendanceOvertimeCompletion()
            ->orderByDesc('start_date')
            ->get()
            ->map(function (LeaveRequest $req) {
                $deadlineAt = $this->completionDeadlineAt($req);
                $now = now();
                $pastDue = $now->greaterThanOrEqualTo($deadlineAt);
                $secondsLeft = max(0, $deadlineAt->getTimestamp() - $now->getTimestamp());

                $hours = '';
                if (preg_match('/Total Overtime Hours:\s*([0-9]{2}:[0-9]{2})/', (string) ($req->reason ?? ''), $m)) {
                    $hours = trim($m[1]);
                }

                return [
                    'id' => $req->id,
                    'date_label' => $req->start_date?->format('M d, Y') ?? '—',
                    'date_label_short' => $req->start_date?->format('M j, Y') ?? '—',
                    'hours' => $hours,
                    'complete_url' => route('user.leave-requests.complete-attendance-overtime', $req),
                    'deadline_formatted' => $deadlineAt->format('M j, Y g:i A'),
                    'deadline_short' => $deadlineAt->format('M j, g:i A'),
                    'past_due' => $pastDue,
                    'time_remaining_label' => $pastDue ? null : $this->formatCountdownLabel((int) $secondsLeft),
                    'time_remaining_short' => $pastDue ? 'overdue' : $this->formatShortCountdownLabel((int) $secondsLeft),
                    'window_days' => self::RESPONSE_WINDOW_DAYS,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Auto-reject incomplete attendance overtime requests past the completion window.
     *
     * @return int Number of requests rejected
     */
    public function processDueAutoRejects(): int
    {
        $actorId = $this->systemActorUserId();
        if ($actorId === null) {
            Log::warning('LeaveRequestIncompleteAttendanceOvertimeService: no admin user for automated rejection');

            return 0;
        }

        $rejected = 0;

        LeaveRequest::query()
            ->awaitingAttendanceOvertimeCompletion()
            ->with('user')
            ->orderBy('id')
            ->chunkById(100, function ($requests) use ($actorId, &$rejected): void {
                foreach ($requests as $leaveRequest) {
                    if (now()->lessThan($this->completionDeadlineAt($leaveRequest))) {
                        continue;
                    }

                    $this->autoReject($leaveRequest, $actorId);
                    $rejected++;
                }
            });

        return $rejected;
    }

    private function autoReject(LeaveRequest $leaveRequest, int $actorId): void
    {
        $leaveRequest->load('user');

        $statusBefore = $leaveRequest->status;
        $notes = 'Automatically rejected: overtime details from Record Attendance were not completed within '
            .self::RESPONSE_WINDOW_DAYS
            .' days.';

        $leaveRequest->update([
            'status' => 'rejected',
            'admin_notes' => $notes,
            'reviewed_by' => $actorId,
            'reviewed_at' => now(),
        ]);

        LeaveRequestLog::create([
            'leave_request_id' => $leaveRequest->id,
            'action' => 'rejected',
            'status_before' => $statusBefore,
            'status_after' => 'rejected',
            'notes' => $notes,
            'performed_by' => $actorId,
        ]);

        try {
            MailConfigService::configure();
            $fresh = $leaveRequest->fresh(['user']);

            if ($fresh->user?->email) {
                Mail::to($fresh->user->email)->send(
                    new LeaveRequestStatusUpdate($fresh, 'rejected', $notes)
                );
            }
        } catch (\Throwable $e) {
            Log::error('LeaveRequestIncompleteAttendanceOvertimeService: failed to send auto-rejection email', [
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
            ]);
        }

        Log::info('Leave request auto-rejected (incomplete attendance overtime)', [
            'leave_request_id' => $leaveRequest->id,
            'user_id' => $leaveRequest->user_id,
        ]);
    }

    private function systemActorUserId(): ?int
    {
        $id = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->orderBy('id')
            ->value('id');

        return $id !== null ? (int) $id : null;
    }

    private function formatShortCountdownLabel(int $secondsRemaining): string
    {
        if ($secondsRemaining <= 0) {
            return '';
        }

        $days = intdiv($secondsRemaining, 86400);
        $hours = intdiv($secondsRemaining % 86400, 3600);
        $minutes = intdiv($secondsRemaining % 3600, 60);

        $parts = [];
        if ($days > 0) {
            $parts[] = $days.'d';
        }
        if ($hours > 0) {
            $parts[] = $hours.'h';
        }
        if ($minutes > 0 && $days === 0) {
            $parts[] = $minutes.'m';
        }

        return ($parts === [] ? '<1m' : implode(' ', $parts)).' left';
    }

    private function formatCountdownLabel(int $secondsRemaining): string
    {
        if ($secondsRemaining <= 0) {
            return '';
        }

        $days = intdiv($secondsRemaining, 86400);
        $r = $secondsRemaining % 86400;
        $hours = intdiv($r, 3600);
        $r %= 3600;
        $minutes = intdiv($r, 60);
        $seconds = $r % 60;

        $parts = [];
        if ($days > 0) {
            $parts[] = $days.' '.($days === 1 ? 'day' : 'days');
        }
        if ($hours > 0) {
            $parts[] = $hours.' '.($hours === 1 ? 'hour' : 'hours');
        }
        if ($minutes > 0) {
            $parts[] = $minutes.' '.($minutes === 1 ? 'minute' : 'minutes');
        }
        if ($seconds > 0 && $days === 0) {
            $parts[] = $seconds.' '.($seconds === 1 ? 'second' : 'seconds');
        }

        return $parts === [] ? 'less than a minute' : implode(', ', $parts);
    }
}
