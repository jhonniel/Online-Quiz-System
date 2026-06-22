<?php

namespace App\Support;

use App\Models\LeaveRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final class WorkFromHomeQuota
{
    public const MONTHLY_EMPLOYEE_ALLOWANCE_DAYS = 2;

    public static function monthlyAllowanceDays(): int
    {
        return self::MONTHLY_EMPLOYEE_ALLOWANCE_DAYS;
    }

    /**
     * @return array{
     *     allowance: int,
     *     used: float,
     *     remaining: float,
     *     carryover_debt: float,
     *     month_label: string,
     *     year: int,
     *     month: int,
     *     approved_deductions: list<array{id: int, start_date: string, end_date: string, days_in_month: float}>
     * }
     */
    public static function balanceForMonth(int $userId, ?Carbon $reference = null, ?int $excludeLeaveRequestId = null): array
    {
        $reference = ($reference ?? Carbon::now('Asia/Manila'))->copy();
        $year = (int) $reference->year;
        $month = (int) $reference->month;
        $allowance = self::monthlyAllowanceDays();
        $approvedDeductions = self::approvedDeductionsForMonth($userId, $year, $month, $excludeLeaveRequestId);
        $used = array_sum(array_column($approvedDeductions, 'days_in_month'));
        $carryoverDebt = self::carryoverDebtForMonth($userId, $year, $month, $excludeLeaveRequestId);

        return [
            'allowance' => $allowance,
            'used' => $used,
            'carryover_debt' => $carryoverDebt,
            'remaining' => max($allowance - $used - $carryoverDebt, 0),
            'month_label' => $reference->format('F Y'),
            'year' => $year,
            'month' => $month,
            'approved_deductions' => $approvedDeductions,
        ];
    }

    /**
     * Approved WFH requests that deduct from the given calendar month.
     *
     * @return list<array{id: int, start_date: string, end_date: string, days_in_month: float}>
     */
    public static function approvedDeductionsForMonth(
        int $userId,
        int $year,
        int $month,
        ?int $excludeLeaveRequestId = null
    ): array {
        $deductions = [];

        foreach (self::approvedRequestsOverlappingMonth($userId, $year, $month, $excludeLeaveRequestId) as $request) {
            $daysInMonth = self::daysOfRequestInMonth($request, $year, $month);
            if ($daysInMonth <= 0) {
                continue;
            }

            $end = $request->end_date ?? $request->start_date;
            $deductions[] = [
                'id' => (int) $request->id,
                'start_date' => $request->start_date->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'days_in_month' => $daysInMonth,
            ];
        }

        return $deductions;
    }

    /**
     * WFH days already approved in a calendar month (pending requests do not reduce balance).
     */
    public static function usedDaysInMonth(int $userId, int $year, int $month, ?int $excludeLeaveRequestId = null): float
    {
        $total = 0.0;

        foreach (self::approvedRequestsOverlappingMonth($userId, $year, $month, $excludeLeaveRequestId) as $request) {
            $total += self::daysOfRequestInMonth($request, $year, $month);
        }

        return $total;
    }

    /**
     * @return EloquentCollection<int, LeaveRequest>
     */
    public static function approvedRequestsOverlappingMonth(
        int $userId,
        int $year,
        int $month,
        ?int $excludeLeaveRequestId = null
    ): EloquentCollection {
        $query = LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('type', 'work_from_home')
            ->where('status', 'approved');

        if ($excludeLeaveRequestId !== null) {
            $query->where('id', '!=', $excludeLeaveRequestId);
        }

        $matching = $query->orderBy('start_date')->get()->filter(
            fn (LeaveRequest $request) => self::daysOfRequestInMonth($request, $year, $month) > 0
        );

        return new EloquentCollection($matching->values()->all());
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private static function monthBounds(int $year, int $month): array
    {
        $monthStart = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila')->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();

        return [$monthStart, $monthEnd];
    }

    /**
     * Validate an employee-filed WFH request (admins are not subject to this limit).
     */
    public static function validateEmployeeRequest(
        int $userId,
        string $startDate,
        ?string $endDate = null,
        ?int $excludeLeaveRequestId = null
    ): ?string {
        $start = Carbon::parse($startDate, 'Asia/Manila')->startOfDay();
        $end = Carbon::parse($endDate ?? $startDate, 'Asia/Manila')->startOfDay();

        if ($end->lt($start)) {
            return 'End date must be on or after the start date.';
        }

        $neededPerMonth = self::daysPerMonthForRange($start, $end);
        $allowance = self::monthlyAllowanceDays();

        foreach ($neededPerMonth as $monthKey => $daysNeeded) {
            [$year, $month] = array_map('intval', explode('-', $monthKey));
            $used = self::usedDaysInMonth($userId, $year, $month, $excludeLeaveRequestId);
            $carryoverDebt = self::carryoverDebtForMonth($userId, $year, $month, $excludeLeaveRequestId);
            $remaining = max($allowance - $used - $carryoverDebt, 0);

            if ($daysNeeded > $remaining) {
                $monthLabel = Carbon::create($year, $month, 1)->format('F Y');

                if ($remaining <= 0) {
                    $carryoverNote = $carryoverDebt > 0
                        ? ' This includes '.$carryoverDebt.' day(s) carried over from admin-filed Work From Home in the previous month.'
                        : '';

                    return 'No balance: You do not have any Work From Home balance remaining for '.$monthLabel.'. '
                        ."You have already used your {$allowance} approved day(s) for that month."
                        .$carryoverNote
                        .' Contact an administrator if you need additional WFH days.';
                }

                $remainingLabel = $remaining == 1 ? 'day' : 'days';

                return "Work From Home is limited to {$allowance} days per month. "
                    ."For {$monthLabel} you have {$remaining} {$remainingLabel} remaining, but this request needs {$daysNeeded} "
                    .($daysNeeded == 1 ? 'day' : 'days').'.';
            }
        }

        return null;
    }

    /**
     * Whether approving this WFH request would exceed the employee monthly cap.
     */
    public static function validateApproval(LeaveRequest $leaveRequest): ?string
    {
        if ($leaveRequest->type !== 'work_from_home' || $leaveRequest->status === 'approved') {
            return null;
        }

        return self::validateEmployeeRequest(
            (int) $leaveRequest->user_id,
            $leaveRequest->start_date->format('Y-m-d'),
            ($leaveRequest->end_date ?? $leaveRequest->start_date)->format('Y-m-d'),
            null
        );
    }

    /**
     * Admin-filed WFH days that exceeded the monthly allowance and reduce next month's balance.
     */
    public static function adminExcessForMonth(
        int $userId,
        int $year,
        int $month,
        ?int $excludeLeaveRequestId = null
    ): float {
        $allowance = self::monthlyAllowanceDays();
        $totalUsed = self::usedDaysInMonth($userId, $year, $month, $excludeLeaveRequestId);
        $adminUsed = self::adminFiledUsedDaysInMonth($userId, $year, $month, $excludeLeaveRequestId);
        $overflow = max($totalUsed - $allowance, 0);

        if ($overflow <= 0 || $adminUsed <= 0) {
            return 0.0;
        }

        return (float) min($adminUsed, $overflow);
    }

    /**
     * WFH days deducted from this month because of admin-filed excess in the previous month.
     */
    public static function carryoverDebtForMonth(
        int $userId,
        int $year,
        int $month,
        ?int $excludeLeaveRequestId = null
    ): float {
        $previousMonth = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila')->subMonth();

        return self::adminExcessForMonth(
            $userId,
            (int) $previousMonth->year,
            (int) $previousMonth->month,
            $excludeLeaveRequestId
        );
    }

    public static function adminFiledUsedDaysInMonth(
        int $userId,
        int $year,
        int $month,
        ?int $excludeLeaveRequestId = null
    ): float {
        $total = 0.0;

        foreach (self::approvedAdminFiledRequestsOverlappingMonth($userId, $year, $month, $excludeLeaveRequestId) as $request) {
            $total += self::daysOfRequestInMonth($request, $year, $month);
        }

        return $total;
    }

    /**
     * @return EloquentCollection<int, LeaveRequest>
     */
    public static function approvedAdminFiledRequestsOverlappingMonth(
        int $userId,
        int $year,
        int $month,
        ?int $excludeLeaveRequestId = null
    ): EloquentCollection {
        $query = LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('type', 'work_from_home')
            ->where('status', 'approved')
            ->whereHas('logs', fn ($q) => $q->where('action', 'filed_by_admin'));

        if ($excludeLeaveRequestId !== null) {
            $query->where('id', '!=', $excludeLeaveRequestId);
        }

        $matching = $query->orderBy('start_date')->get()->filter(
            fn (LeaveRequest $request) => self::daysOfRequestInMonth($request, $year, $month) > 0
        );

        return new EloquentCollection($matching->values()->all());
    }

    public static function isAdminFiledRequest(LeaveRequest $leaveRequest): bool
    {
        if ($leaveRequest->relationLoaded('logs')) {
            return $leaveRequest->logs->contains(
                fn ($log) => (string) ($log->action ?? '') === 'filed_by_admin'
            );
        }

        return $leaveRequest->logs()->where('action', 'filed_by_admin')->exists();
    }

    /**
     * @return array<string, float> Keys are YYYY-MM, values are inclusive day counts in that month.
     */
    public static function daysPerMonthForRange(Carbon $start, Carbon $end): array
    {
        $counts = [];
        $period = CarbonPeriod::create($start->copy()->startOfDay(), $end->copy()->startOfDay());

        foreach ($period as $date) {
            $key = $date->format('Y-m');
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        return $counts;
    }

    private static function daysOfRequestInMonth(LeaveRequest $request, int $year, int $month): float
    {
        $monthStart = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila')->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $start = $request->start_date->copy()->startOfDay();
        $end = ($request->end_date ?? $request->start_date)->copy()->startOfDay();

        $overlapStart = $start->greaterThan($monthStart) ? $start : $monthStart;
        $overlapEnd = $end->lessThan($monthEnd) ? $end : $monthEnd;

        if ($overlapStart->greaterThan($overlapEnd)) {
            return 0;
        }

        return (float) ($overlapStart->diffInDays($overlapEnd) + 1);
    }
}
