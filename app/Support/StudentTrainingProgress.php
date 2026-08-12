<?php

namespace App\Support;

use App\Models\Dtr;
use App\Models\User;

class StudentTrainingProgress
{
    public static function loggedTrainingHours(User $user): float
    {
        return (float) Dtr::query()
            ->where('user_id', $user->id)
            ->sum('total_hours');
    }

    /**
     * Student has a required hours target and logged DTR hours meet or exceed it.
     */
    public static function hasMetRequiredTrainingHours(User $user): bool
    {
        $required = (float) ($user->required_training_hours ?? 0);
        if ($required <= 0) {
            return false;
        }

        $logged = $user->internship_total_hours !== null
            ? (float) $user->internship_total_hours
            : self::loggedTrainingHours($user);

        return $logged >= $required;
    }

    /**
     * @return array{required: float, logged: float, remaining: float, eligible: bool}
     */
    public static function summary(User $user): array
    {
        $required = (float) ($user->required_training_hours ?? 0);
        $logged = $user->internship_total_hours !== null
            ? (float) $user->internship_total_hours
            : self::loggedTrainingHours($user);
        $remaining = max($required - $logged, 0);

        return [
            'required' => $required,
            'logged' => $logged,
            'remaining' => $remaining,
            'eligible' => $required > 0 && $logged >= $required,
        ];
    }
}
