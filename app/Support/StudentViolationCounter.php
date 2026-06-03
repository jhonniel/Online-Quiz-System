<?php

namespace App\Support;

use App\Models\DtrTimeRequest;
use App\Models\LeaveRequest;
use App\Models\User;
final class StudentViolationCounter
{
    /**
     * @param  list<int>  $userIds
     * @return array<int, int> Total merits per user id
     */
    public static function countsForUserIds(array $userIds): array
    {
        $breakdowns = self::breakdownsForUserIds($userIds);
        $counts = [];
        foreach ($userIds as $userId) {
            $counts[$userId] = (int) ($breakdowns[$userId]['total'] ?? 0);
        }

        return $counts;
    }

    public static function countForUser(int $userId): int
    {
        return (int) (self::breakdownForUser($userId)['total'] ?? 0);
    }

    /**
     * @return array{undertime: int, excess_absence: int, manual: int, total: int}
     */
    public static function breakdownForUser(int $userId): array
    {
        return self::breakdownsForUserIds([$userId])[$userId] ?? self::emptyBreakdown();
    }

    /**
     * @param  list<int>  $userIds
     * @return array<int, array{undertime: int, excess_absence: int, manual: int, total: int}>
     */
    public static function breakdownsForUserIds(array $userIds): array
    {
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        if ($userIds === []) {
            return [];
        }

        $undertime = self::undertimeMeritCountsByUserId($userIds);
        $absence = self::excessAbsenceMeritCountsByUserId($userIds);
        $manual = User::query()
            ->whereIn('id', $userIds)
            ->pluck('student_manual_merits', 'id')
            ->map(fn ($value) => max(0, (int) $value))
            ->all();

        $breakdowns = [];
        foreach ($userIds as $userId) {
            $breakdowns[$userId] = self::composeBreakdown(
                (int) ($undertime[$userId] ?? 0),
                (int) ($absence[$userId] ?? 0),
                (int) ($manual[$userId] ?? 0),
            );
        }

        return $breakdowns;
    }

    /**
     * Each filed regular time request below 08:00 counts as 1 merit (rejected excluded).
     */
    public static function isUndertimeFiling(DtrTimeRequest $request): bool
    {
        if ($request->isOvertime()) {
            return false;
        }

        if ($request->status === 'rejected') {
            return false;
        }

        return StudentUndertimeRulesViolation::effectiveFiledHours($request) < DtrTimeRequestHours::STANDARD_DAY_HOURS;
    }

    /**
     * @return array{undertime: int, excess_absence: int, manual: int, total: int}
     */
    public static function composeBreakdown(int $undertime, int $excessAbsence, int $manual): array
    {
        $manual = max(0, $manual);

        return [
            'undertime' => max(0, $undertime),
            'excess_absence' => max(0, $excessAbsence),
            'manual' => $manual,
            'total' => max(0, $undertime) + max(0, $excessAbsence) + $manual,
        ];
    }

    /**
     * @return array{undertime: int, excess_absence: int, manual: int, total: int}
     */
    private static function emptyBreakdown(): array
    {
        return self::composeBreakdown(0, 0, 0);
    }

    /**
     * @param  list<int>  $userIds
     * @return array<int, int>
     */
    private static function undertimeMeritCountsByUserId(array $userIds): array
    {
        $counts = array_fill_keys($userIds, 0);

        $requests = DtrTimeRequest::query()
            ->whereIn('user_id', $userIds)
            ->where('status', '!=', 'rejected')
            ->where(function ($q): void {
                $q->where('request_type', 'regular')
                    ->orWhereNull('request_type');
            })
            ->orderBy('date')
            ->get(['id', 'user_id', 'date', 'hours', 'requested_total_hours', 'request_type', 'status']);

        foreach ($requests->groupBy('user_id') as $userId => $userRequests) {
            $counts[(int) $userId] = $userRequests
                ->filter(fn (DtrTimeRequest $request) => self::isUndertimeFiling($request))
                ->count();
        }

        return $counts;
    }

    /**
     * @param  list<int>  $userIds
     * @return array<int, int>
     */
    private static function excessAbsenceMeritCountsByUserId(array $userIds): array
    {
        $allowances = User::query()
            ->whereIn('id', $userIds)
            ->get(['id', 'student_absence_allowance'])
            ->keyBy('id');

        $counts = array_fill_keys($userIds, 0);

        $approvedDaysByUser = self::approvedAbsentDaysByUserId($userIds);

        foreach ($userIds as $userId) {
            $allowable = User::normalizedStudentAbsenceAllowance(
                $allowances->get($userId)?->student_absence_allowance
            );
            $approvedDays = (int) ($approvedDaysByUser[$userId] ?? 0);
            $excess = self::excessAbsenceMerits($approvedDays, $allowable);
            if ($excess > 0) {
                $counts[$userId] = $excess;
            }
        }

        return $counts;
    }

    /**
     * Approved absent calendar days per student (matches leave balance / dashboard logic).
     *
     * @param  list<int>  $userIds
     * @return array<int, int>
     */
    public static function approvedAbsentDaysByUserId(array $userIds): array
    {
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        $counts = array_fill_keys($userIds, 0);

        if ($userIds === []) {
            return [];
        }

        $absentRequests = LeaveRequest::query()
            ->whereIn('user_id', $userIds)
            ->where('type', 'absent')
            ->where('status', 'approved')
            ->get(['user_id', 'start_date', 'end_date']);

        foreach ($absentRequests->groupBy('user_id') as $userId => $requests) {
            $counts[(int) $userId] = (int) $requests->sum(
                fn (LeaveRequest $request) => $request->days
            );
        }

        return $counts;
    }

    /**
     * Merits from approved absent days over the student's allowable absence balance.
     */
    public static function excessAbsenceMerits(int $approvedAbsentDays, float $allowableAbsences): int
    {
        if ($approvedAbsentDays <= 0) {
            return 0;
        }

        $excess = $approvedAbsentDays - $allowableAbsences;

        return $excess > 0 ? (int) ceil($excess) : 0;
    }
}
