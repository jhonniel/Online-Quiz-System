<?php

namespace App\Support;

use App\Models\Dtr;
use App\Models\User;
use Carbon\Carbon;

class StudentTrainingEstimate
{
    /**
     * Project when a student may finish required training hours (weekdays only).
     * Uses average hours on active DTR days in the last 30 days, or 8 h/day if none.
     */
    public static function estimatedEndDate(User $user, ?float $loggedHours = null): ?Carbon
    {
        $requiredHours = (float) ($user->required_training_hours ?? 0);
        if ($requiredHours <= 0) {
            return null;
        }

        $logged = $loggedHours ?? (float) Dtr::query()->where('user_id', $user->id)->sum('total_hours');
        $remainingHours = $requiredHours - $logged;
        if ($remainingHours <= 0) {
            return null;
        }

        $recentDtrRows = Dtr::query()
            ->where('user_id', $user->id)
            ->whereDate('date', '>=', now()->subDays(30)->toDateString())
            ->where('total_hours', '>', 0)
            ->get(['date', 'total_hours']);

        $activeDays = $recentDtrRows->pluck('date')->unique()->count();
        $avgHoursPerActiveDay = $activeDays > 0
            ? ((float) $recentDtrRows->sum('total_hours')) / $activeDays
            : 0.0;

        $dailyHoursForEstimate = $avgHoursPerActiveDay > 0 ? $avgHoursPerActiveDay : 8.0;
        $neededDays = (int) ceil($remainingHours / $dailyHoursForEstimate);

        return now()->timezone(config('app.timezone'))->startOfDay()->addWeekdays(max($neededDays, 1));
    }

    /**
     * @param  array<string, mixed>  $studentTime
     * @return array<string, mixed>
     */
    public static function withEstimatedEndDate(User $user, array $studentTime, ?float $loggedHours = null): array
    {
        $estimated = self::estimatedEndDate($user, $loggedHours);
        $studentTime['estimated_end_date'] = $estimated?->toDateString();
        $studentTime['estimated_end_date_formatted'] = $estimated?->format('M d, Y');

        return $studentTime;
    }
}
