<?php

namespace App\Services;

use App\Mail\LeaveRequestStatusUpdate;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LeaveRequestStaleResubmissionService
{
    /** Days after the leave request was last updated before auto-rejection (resubmission flow). */
    public const RESPONSE_WINDOW_DAYS = 3;

    /**
     * When the requestor must act by, based on {@see LeaveRequest::$updated_at} (last change to the request row).
     */
    public function resubmissionResponseDeadlineAt(LeaveRequest $leaveRequest): Carbon
    {
        return Carbon::parse($leaveRequest->updated_at)
            ->timezone(config('app.timezone'))
            ->addDays(self::RESPONSE_WINDOW_DAYS);
    }

    public function pendingResubmissionModalPayloadForUser(User $user): array
    {
        if (! in_array($user->role, ['employee', 'student'], true)) {
            return [];
        }

        return LeaveRequest::query()
            ->where('user_id', $user->id)
            ->awaitingUserResubmission()
            ->with('resubmissionRequestedBy')
            ->orderByDesc('updated_at')
            ->get()
            ->map(function (LeaveRequest $req) {
                $log = $req->resubmissionRequestedBy;
                if (! $log) {
                    return null;
                }

                $lastUpdated = Carbon::parse($req->updated_at)->timezone(config('app.timezone'));
                $deadlineAt = $this->resubmissionResponseDeadlineAt($req);
                $now = now();
                $pastDue = $now->greaterThanOrEqualTo($deadlineAt);
                $secondsLeft = max(0, $deadlineAt->getTimestamp() - $now->getTimestamp());

                return [
                    'id' => $req->id,
                    'type_label' => $req->type_label,
                    'edit_url' => route('user.leave-requests.edit', $req),
                    'index_url' => route('user.leave-requests.index'),
                    'last_updated_formatted' => $lastUpdated->format('M j, Y g:i A'),
                    'deadline_at' => $deadlineAt,
                    'deadline_formatted' => $deadlineAt->timezone(config('app.timezone'))->format('M j, Y g:i A'),
                    'past_due' => $pastDue,
                    'hours_remaining' => $pastDue ? 0 : (int) ceil($secondsLeft / 3600),
                    'time_remaining_label' => $pastDue ? null : $this->formatResubmissionCountdownLabel((int) $secondsLeft),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Notify the leave request owner by email after an administrator marks the request for resubmission.
     */
    public function notifyUserResubmissionRequested(LeaveRequest $leaveRequest, ?string $normalizedNotesForEmail): void
    {
        $leaveRequest->loadMissing('user');
        $user = $leaveRequest->user;

        $email = is_string($user?->email) ? trim($user->email) : '';
        if ($email === '') {
            Log::warning('Resubmission requested email skipped: missing user email', [
                'leave_request_id' => $leaveRequest->id,
                'user_id' => $leaveRequest->user_id,
            ]);

            return;
        }

        try {
            MailConfigService::configure();

            Log::info('Sending leave request resubmission email to user', [
                'user_email' => $email,
                'leave_request_id' => $leaveRequest->id,
                'user_name' => $user?->name,
            ]);

            Mail::to($email)->send(
                new LeaveRequestStatusUpdate($leaveRequest, 'resubmission_requested', $normalizedNotesForEmail)
            );

            Log::info('Leave request resubmission email sent successfully', [
                'user_email' => $email,
                'leave_request_id' => $leaveRequest->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send leave request resubmission email', [
                'user_email' => $email,
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Auto-reject pending resubmissions that exceeded the response window.
     *
     * @return int Number of requests rejected
     */
    public function processDueAutoRejects(): int
    {
        $actorId = $this->systemActorUserId();
        if ($actorId === null) {
            Log::warning('LeaveRequestStaleResubmissionService: no user available for automated rejection actor');

            return 0;
        }

        $rejected = 0;

        LeaveRequest::query()
            ->awaitingUserResubmission()
            ->with(['user', 'resubmissionRequestedBy'])
            ->orderBy('id')
            ->chunkById(100, function ($requests) use ($actorId, &$rejected): void {
                foreach ($requests as $leaveRequest) {
                    if (! $leaveRequest->resubmissionRequestedBy) {
                        continue;
                    }

                    $deadlineAt = $this->resubmissionResponseDeadlineAt($leaveRequest);
                    if (now()->lessThan($deadlineAt)) {
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
        $notes = 'Automatically rejected: this request was not updated within '
            .self::RESPONSE_WINDOW_DAYS
            .' days after the last update to the request while resubmission was required.';

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

            Mail::to($fresh->user->email)->send(
                new LeaveRequestStatusUpdate($fresh, 'rejected', $notes)
            );
        } catch (\Throwable $e) {
            Log::error('LeaveRequestStaleResubmissionService: failed to send auto-rejection email', [
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
            ]);
        }

        Log::info('Leave request auto-rejected (stale resubmission)', [
            'leave_request_id' => $leaveRequest->id,
            'user_id' => $leaveRequest->user_id,
            'acted_as_user_id' => $actorId,
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

    /**
     * Human-readable countdown (days, hours, minutes, seconds) until the auto-reject deadline.
     */
    private function formatResubmissionCountdownLabel(int $secondsRemaining): string
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
        if ($seconds > 0) {
            $parts[] = $seconds.' '.($seconds === 1 ? 'second' : 'seconds');
        }

        return $parts === [] ? 'less than a minute' : implode(', ', $parts);
    }
}
