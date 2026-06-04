<?php

namespace App\Services;

use App\Mail\StudentOjtAccountDisabledAfterGraceMail;
use App\Mail\StudentOjtCompletedCongratulationsMail;
use App\Models\Dtr;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
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

    public function syncForStudentId(int $userId, bool $allowThrottle = true): void
    {
        if ($allowThrottle) {
            $cacheKey = 'ojt_post_completion_sync:'.$userId;
            if (! Cache::add($cacheKey, 1, now()->addMinutes(2))) {
                return;
            }
        }

        $user = User::query()->find($userId);
        if (! $user || $user->role !== 'student') {
            return;
        }

        $this->processStudent($user);
    }

    /**
     * @return array{processed: int, completed_hours: int, backfilled_met_at: int, terminated: int, congratulations_sent: int, disabled_notice_sent: int}
     */
    public function processAllStudents(): array
    {
        $stats = [
            'processed' => 0,
            'completed_hours' => 0,
            'backfilled_met_at' => 0,
            'terminated' => 0,
            'congratulations_sent' => 0,
            'disabled_notice_sent' => 0,
        ];

        User::query()
            ->where('role', 'student')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($users) use (&$stats) {
                foreach ($users as $u) {
                    $student = User::query()->find($u->id);
                    if (! $student) {
                        continue;
                    }
                    $rowStats = $this->processStudent($student);
                    foreach ($rowStats as $key => $value) {
                        if (isset($stats[$key])) {
                            $stats[$key] += (int) $value;
                        }
                    }
                }
            });

        return $stats;
    }

    /**
     * Apply post-completion rules to one student (including existing accounts that already met required DTR hours).
     *
     * @return array{processed: int, completed_hours: int, backfilled_met_at: int, terminated: int, congratulations_sent: int, disabled_notice_sent: int}
     */
    public function processStudent(User $user): array
    {
        $stats = [
            'processed' => 1,
            'completed_hours' => 0,
            'backfilled_met_at' => 0,
            'terminated' => 0,
            'congratulations_sent' => 0,
            'disabled_notice_sent' => 0,
        ];

        if ($user->role !== 'student') {
            return $stats;
        }

        $required = (float) ($user->required_training_hours ?? 0);
        if ($required <= 0) {
            return $stats;
        }

        $total = $this->totalDtrHoursForStudent((int) $user->id);
        if ($total < $required) {
            return $stats;
        }

        $stats['completed_hours'] = 1;
        $user->refresh();

        $wasMissingMetAt = $user->ojt_requirement_met_at === null;
        if ($wasMissingMetAt) {
            $metAt = $this->resolveRequirementMetAt($user, $required);

            $this->runWriteTransaction(function () use ($user, $metAt) {
                $locked = $this->lockUserRow((int) $user->id);
                if (! $locked || $locked->role !== 'student' || $locked->ojt_requirement_met_at !== null) {
                    return;
                }
                $locked->ojt_requirement_met_at = $metAt;
                $locked->save();
            });

            $user->refresh();
            if ($user->ojt_requirement_met_at !== null) {
                $stats['backfilled_met_at'] = 1;
            }
        }

        $metAt = $user->ojt_requirement_met_at ? Carbon::parse($user->ojt_requirement_met_at)->startOfDay() : null;
        if (! $metAt) {
            $stats['disabled_notice_sent'] += $this->sendAccountDisabledNoticeIfPending($user) ? 1 : 0;

            return $stats;
        }

        $graceEnd = $metAt->copy()->addDays(self::GRACE_DAYS)->startOfDay();
        $graceAlreadyEnded = now()->startOfDay()->gte($graceEnd);

        if ($graceAlreadyEnded && $user->ojt_completion_congratulations_sent_at === null) {
            $user->forceFill([
                'ojt_completion_congratulations_sent_at' => $graceEnd,
            ])->save();
            $user->refresh();
        }

        $wasTerminated = (bool) $user->student_terminated;
        if ($graceAlreadyEnded
            && ! $wasTerminated
            && $user->ojt_post_completion_grace_closed_at === null) {
            $this->terminateStudentAfterOjtGrace($user);
            $user->refresh();
            if ((bool) $user->student_terminated && ! $wasTerminated) {
                $stats['terminated'] = 1;
            }
        }

        if ($user->ojt_completion_congratulations_sent_at === null && ! $graceAlreadyEnded) {
            if (filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::to($user->email)->send(new StudentOjtCompletedCongratulationsMail(
                        $user->fresh(),
                        $metAt->copy(),
                        self::GRACE_DAYS
                    ));
                    $user->forceFill(['ojt_completion_congratulations_sent_at' => now()])->save();
                    $stats['congratulations_sent'] = 1;
                } catch (\Throwable $e) {
                    Log::warning('OJT completion congratulations email failed: '.$e->getMessage(), [
                        'user_id' => $user->id,
                    ]);
                }
            } else {
                $user->forceFill(['ojt_completion_congratulations_sent_at' => now()])->save();
            }
            $user->refresh();
        }

        $stats['disabled_notice_sent'] += $this->sendAccountDisabledNoticeIfPending($user) ? 1 : 0;

        return $stats;
    }

    /**
     * Historical completion date from DTR (for students who finished before tracking existed).
     */
    private function resolveRequirementMetAt(User $user, float $requiredHours): Carbon
    {
        if ($user->ojt_requirement_met_at) {
            return Carbon::parse($user->ojt_requirement_met_at)->startOfDay();
        }

        $estimated = $this->estimateRequirementMetDate((int) $user->id, $requiredHours);
        if ($estimated) {
            return $estimated;
        }

        $lastDtrDate = Dtr::query()
            ->where('user_id', $user->id)
            ->where('total_hours', '>', 0)
            ->orderByDesc('date')
            ->value('date');

        if ($lastDtrDate) {
            return Carbon::parse($lastDtrDate)->startOfDay();
        }

        return now()->startOfDay();
    }

    private function terminateStudentAfterOjtGrace(User $user): void
    {
        $this->runWriteTransaction(function () use ($user) {
            $locked = $this->lockUserRow((int) $user->id);
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

    private function lockUserRow(int $userId): ?User
    {
        $query = User::query()->whereKey($userId);

        if (DB::connection()->getDriverName() !== 'sqlite') {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function runWriteTransaction(callable $callback): void
    {
        $maxAttempts = DB::connection()->getDriverName() === 'sqlite' ? 8 : 1;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                DB::transaction($callback);

                return;
            } catch (\Throwable $e) {
                if ($attempt >= $maxAttempts || ! $this->isDatabaseLockedException($e)) {
                    throw $e;
                }

                usleep(50_000 * $attempt);
            }
        }
    }

    private function isDatabaseLockedException(\Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'database is locked')
            || str_contains($message, 'database table is locked')
            || str_contains($message, 'general error: 5');
    }

    private function sendAccountDisabledNoticeIfPending(User $user): bool
    {
        $user->refresh();

        if ($user->ojt_account_disabled_notice_sent_at !== null) {
            return false;
        }
        if (! (bool) $user->student_terminated) {
            return false;
        }
        if ($user->ojt_post_completion_grace_closed_at === null) {
            return false;
        }

        if (! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            $user->forceFill(['ojt_account_disabled_notice_sent_at' => now()])->save();

            return false;
        }

        try {
            Mail::to($user->email)->send(new StudentOjtAccountDisabledAfterGraceMail($user->fresh()));
        } catch (\Throwable $e) {
            Log::warning('OJT account disabled notice email failed: '.$e->getMessage(), [
                'user_id' => $user->id,
            ]);

            return false;
        }

        $user->forceFill(['ojt_account_disabled_notice_sent_at' => now()])->save();

        return true;
    }
}
