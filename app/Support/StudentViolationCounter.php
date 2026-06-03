<?php

namespace App\Support;

use App\Models\DtrTimeRequest;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\DtrTimeRequestHours;

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

    /**
     * Full merit breakdown and source records for admin detail views.
     *
     * @return array<string, mixed>
     */
    public static function detailsForUser(User $student): array
    {
        $userId = (int) $student->id;
        $breakdown = self::breakdownForUser($userId);
        $allowable = User::normalizedStudentAbsenceAllowance($student->student_absence_allowance);
        $approvedAbsentDays = (int) (self::approvedAbsentDaysByUserId([$userId])[$userId] ?? 0);
        $remainingBalance = max($allowable - $approvedAbsentDays, 0);

        $undertimeFilings = DtrTimeRequest::query()
            ->where('user_id', $userId)
            ->where('status', '!=', 'rejected')
            ->where(function ($q): void {
                $q->where('request_type', 'regular')
                    ->orWhereNull('request_type');
            })
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get(['id', 'date', 'hours', 'requested_total_hours', 'status'])
            ->filter(fn (DtrTimeRequest $request) => self::isUndertimeFiling($request))
            ->values()
            ->map(function (DtrTimeRequest $request) {
                $hours = StudentUndertimeRulesViolation::effectiveFiledHours($request);

                return [
                    'id' => (int) $request->id,
                    'date' => $request->date?->format('M j, Y') ?? '—',
                    'date_sort' => $request->date?->format('Y-m-d') ?? '',
                    'hours' => round($hours, 2),
                    'hours_label' => DtrTimeRequestHours::decimalToTimeString($hours),
                    'status' => (string) $request->status,
                ];
            })
            ->all();

        $absentRequests = LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('type', 'absent')
            ->where('status', 'approved')
            ->orderByDesc('start_date')
            ->get(['id', 'start_date', 'end_date', 'status'])
            ->map(function (LeaveRequest $request) {
                $days = $request->days;
                $start = $request->start_date;
                $end = $request->end_date ?? $start;
                $range = $start && $end && ! $start->equalTo($end)
                    ? $start->format('M j, Y').' – '.$end->format('M j, Y')
                    : ($start?->format('M j, Y') ?? '—');

                return [
                    'id' => (int) $request->id,
                    'range' => $range,
                    'days' => $days,
                    'status' => (string) $request->status,
                ];
            })
            ->values()
            ->all();

        $thresholds = StudentMeritNoticeSettings::thresholds();

        return [
            'student' => [
                'id' => $userId,
                'name' => (string) $student->name,
                'email' => (string) $student->email,
            ],
            'breakdown' => $breakdown,
            'absence' => [
                'allowable' => round($allowable, 2),
                'approved_days' => $approvedAbsentDays,
                'remaining_balance' => round($remainingBalance, 2),
                'excess_merits' => (int) ($breakdown['excess_absence'] ?? 0),
            ],
            'undertime_filings' => $undertimeFilings,
            'absent_requests' => $absentRequests,
            'notices' => [
                'rules_warning' => (bool) ($student->student_rules_warning ?? false),
                'final_notice' => (bool) ($student->student_rules_marquee_enabled ?? false),
                'merit_automation_disabled' => (bool) ($student->student_rules_merit_automation_disabled ?? false),
                'warning_manual' => (bool) ($student->student_rules_warning_manual ?? false),
                'final_manual' => (bool) ($student->student_rules_marquee_manual ?? false),
            ],
            'thresholds' => $thresholds,
            'rules' => [
                'undertime' => '1 merit per regular time request filed below 08:00 (pending or approved; rejected excluded).',
                'excess_absence' => '1 merit per approved absent day over the allowable absence balance.',
                'manual' => 'Added by an administrator on the student profile.',
            ],
        ];
    }
}
