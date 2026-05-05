<?php

namespace App\Services;

use App\Mail\StudentOjtAccountDisabledAfterGraceMail;
use App\Mail\StudentOjtCompletedCongratulationsMail;
use App\Models\Dtr;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StudentOjtPostCompletionService
{
    public const GRACE_DAYS = 30;

    public function totalDtrHoursForStudent(int $userId): float
    {
        return (float) Dtr::query()->where('user_id', $userId)->sum('total_hours');
    }

    /**
     * First calendar day (start of day) when cumulative DTR hours reached the requirement, or null.
     */
    public function estimateRequirementMetDate(int $userId, float $requiredHours): ?Carbon
    {
        if ($requiredHours <= 0) {
            return null;
        }

        $rows = Dtr::query()
            ->where('user_id', $userId)
            ->orderBy('date')
            ->orderBy('id')
            ->get(['date', 'total_hours']);

        $sum = 0.0;
        foreach ($rows as $row) {
            $sum += (float) ($row->total_hours ?? 0);
            if ($sum >= $requiredHours) {
                return Carbon::parse($row->date)->startOfDay();
            }
        }

        return null;
    }

    /**
     * For the student dashboard: days until post–OJT access ends (same 30-day rule as emails / auto-disable).
     * Returns null when the countdown does not apply.
     *
     * @return array{days_remaining: int, access_end_date: Carbon, requirement_met_at: Carbon}|null
     */
    public function studentAccountDisableCountdownForDashboard(User $user): ?array
    {
        if ($user->role !== 'student') {
            return null;
        }
        if ((bool) $user->student_terminated) {
            return null;
        }
        if ($user->ojt_post_completion_grace_closed_at) {
            return null;
        }

        $required = (float) ($user->required_training_hours ?? 0);
        if ($required <= 0) {
            return null;
        }

        $total = $this->totalDtrHoursForStudent((int) $user->id);
        if ($total < $required) {
            return null;
        }

        $metAt = $user->ojt_requirement_met_at
            ? Carbon::parse($user->ojt_requirement_met_at)->startOfDay()
            : ($this->estimateRequirementMetDate((int) $user->id, $required) ?? now()->startOfDay());

        $graceEnd = $metAt->copy()->addDays(self::GRACE_DAYS)->startOfDay();
        $today = now()->startOfDay();

        if ($today->gte($graceEnd)) {
            return [
                'days_remaining' => 0,
                'access_end_date' => $graceEnd,
                'requirement_met_at' => $metAt,
            ];
        }

        return [
            'days_remaining' => (int) $today->diffInDays($graceEnd),
            'access_end_date' => $graceEnd,
            'requirement_met_at' => $metAt,
        ];
    }

    public function syncForStudentId(int $userId): void
    {
        $user = User::query()->find($userId);
        if (! $user || $user->role !== 'student') {
            return;
        }

        $this->processStudent($user);
    }

    public function processAllStudents(): int
    {
        $count = 0;
        User::query()
            ->where('role', 'student')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($users) use (&$count) {
                foreach ($users as $u) {
                    $this->syncForStudentId((int) $u->id);
                    $count++;
                }
            });

        return $count;
    }

    public function processStudent(User $user): void
    {
        if ($user->role !== 'student') {
            return;
        }

        $required = (float) ($user->required_training_hours ?? 0);
        if ($required <= 0) {
            return;
        }

        $total = $this->totalDtrHoursForStudent((int) $user->id);
        if ($total < $required) {
            return;
        }

        $user->refresh();

        if ($user->ojt_requirement_met_at === null) {
            $metAt = $this->estimateRequirementMetDate((int) $user->id, $required) ?? now()->startOfDay();

            DB::transaction(function () use ($user, $metAt) {
                $locked = User::query()->whereKey($user->id)->lockForUpdate()->first();
                if (! $locked || $locked->role !== 'student' || $locked->ojt_requirement_met_at !== null) {
                    return;
                }
                $locked->ojt_requirement_met_at = $metAt;
                $locked->save();
            });

            $user->refresh();
        }

        $metAt = $user->ojt_requirement_met_at ? Carbon::parse($user->ojt_requirement_met_at) : null;
        if (! $metAt) {
            $this->sendAccountDisabledNoticeIfPending($user);

            return;
        }

        $graceEnd = $metAt->copy()->addDays(self::GRACE_DAYS)->startOfDay();

        if (now()->gte($graceEnd)
            && ! (bool) $user->student_terminated
            && $user->ojt_post_completion_grace_closed_at === null) {
            $this->terminateStudentAfterOjtGrace($user);
            $user->refresh();
        }

        if ($user->ojt_completion_congratulations_sent_at === null && now()->lt($graceEnd)) {
            if (filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::to($user->email)->send(new StudentOjtCompletedCongratulationsMail(
                        $user->fresh(),
                        $metAt->copy(),
                        self::GRACE_DAYS
                    ));
                    $user->forceFill(['ojt_completion_congratulations_sent_at' => now()])->save();
                } catch (\Throwable $e) {
                    Log::warning('OJT completion congratulations email failed: '.$e->getMessage(), [
                        'user_id' => $user->id,
                    ]);
                }
            }
        }

        $this->sendAccountDisabledNoticeIfPending($user);
    }

    private function terminateStudentAfterOjtGrace(User $user): void
    {
        DB::transaction(function () use ($user) {
            $locked = User::query()->whereKey($user->id)->lockForUpdate()->first();
            if (! $locked || $locked->role !== 'student') {
                return;
            }
            if ((bool) $locked->student_terminated || $locked->ojt_post_completion_grace_closed_at !== null) {
                return;
            }
            $required = (float) ($locked->required_training_hours ?? 0);
            if ($required <= 0) {
                return;
            }
            $total = $this->totalDtrHoursForStudent((int) $locked->id);
            if ($total < $required) {
                return;
            }
            $metAt = $locked->ojt_requirement_met_at ? Carbon::parse($locked->ojt_requirement_met_at) : null;
            if (! $metAt || now()->lt($metAt->copy()->addDays(self::GRACE_DAYS)->startOfDay())) {
                return;
            }

            $locked->student_terminated = true;
            $locked->ojt_post_completion_grace_closed_at = now();
            $locked->save();
        });
    }

    private function sendAccountDisabledNoticeIfPending(User $user): void
    {
        $user->refresh();

        if ($user->ojt_account_disabled_notice_sent_at !== null) {
            return;
        }
        if (! (bool) $user->student_terminated) {
            return;
        }
        if ($user->ojt_post_completion_grace_closed_at === null) {
            return;
        }

        if (! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            $user->forceFill(['ojt_account_disabled_notice_sent_at' => now()])->save();

            return;
        }

        try {
            Mail::to($user->email)->send(new StudentOjtAccountDisabledAfterGraceMail($user->fresh()));
        } catch (\Throwable $e) {
            Log::warning('OJT account disabled notice email failed: '.$e->getMessage(), [
                'user_id' => $user->id,
            ]);

            return;
        }

        $user->forceFill(['ojt_account_disabled_notice_sent_at' => now()])->save();
    }
}
