<?php

namespace App\Observers;

use App\Models\HiringApplication;
use App\Models\UserActivity;
use Illuminate\Support\Facades\Auth;

class HiringApplicationObserver
{
    /**
     * Fields excluded from generic "record updated" activity (handled elsewhere or sensitive).
     *
     * @var list<string>
     */
    private const IGNORE_FIELDS = [
        'updated_at',
        'created_at',
        'acceptance_token',
        'token_expires_at',
        'deleted_at',
    ];

    /**
     * Per-model pending diff between updating and updated (key = spl_object_id).
     *
     * @var array<int, array<string, array{old: mixed, new: mixed}>>
     */
    private static array $pendingDiffs = [];

    public function updating(HiringApplication $application): void
    {
        $diff = [];
        foreach ($application->getDirty() as $key => $newValue) {
            if (in_array($key, self::IGNORE_FIELDS, true)) {
                continue;
            }
            $diff[$key] = [
                'old' => $this->normalizeForLog($application->getOriginal($key)),
                'new' => $this->normalizeForLog($newValue),
            ];
        }
        self::$pendingDiffs[spl_object_id($application)] = $diff;
    }

    public function updated(HiringApplication $application): void
    {
        $oid = spl_object_id($application);
        $diff = self::$pendingDiffs[$oid] ?? [];
        unset(self::$pendingDiffs[$oid]);

        if ($diff === []) {
            return;
        }

        UserActivity::logActivity(
            Auth::user(),
            'action',
            'hiring_application_updated',
            [
                'application_id' => $application->id,
                'applicant_name' => $application->full_name,
                'applicant_email' => $application->email,
                'position' => $application->hiringPosition?->title ?? $application->position_applied,
                'changes' => $diff,
            ]
        );
    }

    private function normalizeForLog(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value;
    }
}
