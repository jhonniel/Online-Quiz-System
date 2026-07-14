<?php

namespace App\Support;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestLog;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class EmployeeRecordsLedger
{
    /**
     * @return array{
     *     year: int,
     *     allowance: float,
     *     used: float,
     *     remaining: float,
     *     vacation_allowance: float,
     *     sick_allowance: float
     * }
     */
    public static function leaveCreditSummary(int $userId, int $year): array
    {
        $defaultVacation = (float) Setting::get('default_vacation_balance', 15);
        $defaultSick = (float) Setting::get('default_sick_leave_balance', 10);

        $leaveBalance = LeaveBalance::firstOrCreateWithCarryover(
            $userId,
            $year,
            $defaultVacation,
            $defaultSick
        );

        $allowance = (float) $leaveBalance->vacation_allowance + (float) $leaveBalance->sick_allowance;
        $used = (float) self::sumApprovedLeaveCreditDays($userId, $year);

        return [
            'year' => $year,
            'allowance' => round($allowance, 2),
            'used' => round($used, 2),
            'remaining' => round(max($allowance - $used, 0), 2),
            'vacation_allowance' => (float) $leaveBalance->vacation_allowance,
            'sick_allowance' => (float) $leaveBalance->sick_allowance,
        ];
    }

    /**
     * @return array{minutes: int, formatted: string, earned_minutes: int, offset_minutes: int}
     */
    public static function overtimeSummary(int $userId): array
    {
        $earned = self::sumApprovedOvertimeMinutes($userId);
        $offset = self::sumApprovedOffsetMinutes($userId);
        $net = $earned - $offset;

        return [
            'minutes' => $net,
            'formatted' => self::formatMinutes($net),
            'earned_minutes' => $earned,
            'offset_minutes' => $offset,
        ];
    }

    /**
     * @return list<array{
     *     date: \Carbon\Carbon,
     *     direction: string,
     *     amount_days: float,
     *     amount_label: string,
     *     description: string,
     *     leave_request_id: int|null,
     *     reference: string|null
     * }>
     */
    public static function leaveCreditLedger(int $userId, ?int $year = null): array
    {
        $years = $year !== null
            ? [$year]
            : self::leaveCreditYears($userId);

        $entries = [];
        foreach ($years as $ledgerYear) {
            foreach (self::leaveCreditYearEntries($userId, $ledgerYear) as $entry) {
                $entries[] = $entry;
            }
        }

        usort($entries, fn (array $a, array $b) => $b['date']->timestamp <=> $a['date']->timestamp);

        return $entries;
    }

    /**
     * Ledger entries for a single year, with running balance computed chronologically.
     *
     * @return list<array<string, mixed>>
     */
    private static function leaveCreditYearEntries(int $userId, int $year): array
    {
        $summary = self::leaveCreditSummary($userId, $year);

        $balanceRow = LeaveBalance::where('user_id', $userId)->where('year', $year)->first();
        $allocatedAt = $balanceRow?->created_at
            ? Carbon::parse($balanceRow->created_at)->startOfDay()
            : Carbon::create($year, 1, 1);

        $running = $summary['allowance'];

        $entries = [[
            'date' => $allocatedAt,
            'direction' => 'credit',
            'amount_days' => $summary['allowance'],
            'amount_label' => number_format($summary['allowance'], 2).' day(s)',
            'description' => "Leave credits allocated for {$year}",
            'leave_request_id' => null,
            'reference' => null,
            'balance_after' => round($running, 2),
            'year' => $year,
        ]];

        $requests = LeaveRequest::query()
            ->where('user_id', $userId)
            ->whereIn('type', ['vacation_leave', 'sick_leave', 'leave'])
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();

        foreach ($requests as $request) {
            $days = (float) $request->days;
            $running = max($running - $days, 0);
            $entries[] = [
                'date' => $request->start_date->copy()->startOfDay(),
                'direction' => 'debit',
                'amount_days' => $days,
                'amount_label' => number_format($days, 2).' day(s)',
                'description' => $request->type_label.' approved',
                'leave_request_id' => $request->id,
                'reference' => self::dateRangeLabel($request),
                'balance_after' => round($running, 2),
                'year' => $year,
            ];
        }

        return $entries;
    }

    /**
     * Years that have leave balance rows or approved leave-credit requests.
     *
     * @return list<int>
     */
    private static function leaveCreditYears(int $userId): array
    {
        $balanceYears = LeaveBalance::where('user_id', $userId)->pluck('year')->all();

        $requestYears = LeaveRequest::query()
            ->where('user_id', $userId)
            ->whereIn('type', ['vacation_leave', 'sick_leave', 'leave'])
            ->where('status', 'approved')
            ->get(['start_date'])
            ->map(fn (LeaveRequest $request) => (int) $request->start_date?->year)
            ->all();

        $years = array_values(array_unique(array_map('intval', array_merge($balanceYears, $requestYears))));
        $years = array_filter($years, fn (int $y) => $y > 0);

        if ($years === []) {
            $years = [(int) now()->year];
        }

        rsort($years);

        return $years;
    }

    /**
     * @return list<array{
     *     date: \Carbon\Carbon,
     *     direction: string,
     *     amount_minutes: int,
     *     amount_label: string,
     *     description: string,
     *     leave_request_id: int|null,
     *     reference: string|null
     * }>
     */
    public static function overtimeLedger(int $userId, ?int $year = null): array
    {
        $items = array_values(array_filter(
            self::overtimeBalanceTimeline($userId),
            fn (array $entry) => ($entry['kind'] ?? '') === 'overtime'
                && ($year === null || (int) $entry['date']->year === $year)
        ));

        usort($items, fn (array $a, array $b) => $b['date']->timestamp <=> $a['date']->timestamp);

        return $items;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function offsetLedger(int $userId, ?int $year = null): array
    {
        $items = array_values(array_filter(
            self::overtimeBalanceTimeline($userId),
            fn (array $entry) => ($entry['kind'] ?? '') === 'offset'
                && ($year === null || (int) $entry['date']->year === $year)
        ));

        usort($items, fn (array $a, array $b) => $b['date']->timestamp <=> $a['date']->timestamp);

        return $items;
    }

    /**
     * Combined overtime credits and offset debits with running net balance.
     *
     * @return list<array<string, mixed>>
     */
    private static function overtimeBalanceTimeline(int $userId): array
    {
        $today = Carbon::today();

        $overtimeRequests = LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('type', 'overtime')
            ->where('status', 'approved')
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();

        $items = [];

        foreach ($overtimeRequests as $request) {
            $minutes = self::overtimeMinutesFromRequest($request);
            if ($minutes <= 0) {
                continue;
            }

            $countsTowardBalance = $request->start_date && $request->start_date->lte($today);

            $items[] = [
                'kind' => 'overtime',
                'date' => $request->start_date->copy()->startOfDay(),
                'direction' => 'credit',
                'amount_minutes' => $minutes,
                'amount_label' => self::formatMinutes($minutes),
                'description' => $countsTowardBalance
                    ? 'Overtime approved'
                    : 'Overtime approved (credits on '.$request->start_date->format('M j, Y').')',
                'leave_request_id' => $request->id,
                'reference' => self::dateRangeLabel($request),
                'counts_toward_balance' => $countsTowardBalance,
            ];
        }

        $offsetRequests = LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('type', 'offset')
            ->where('status', 'approved')
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();

        foreach ($offsetRequests as $request) {
            $minutes = self::offsetMinutesFromRequest($request);
            if ($minutes <= 0) {
                continue;
            }

            $items[] = [
                'kind' => 'offset',
                'date' => $request->start_date->copy()->startOfDay(),
                'direction' => 'debit',
                'amount_minutes' => $minutes,
                'amount_label' => self::formatMinutes($minutes),
                'description' => 'Offset approved',
                'leave_request_id' => $request->id,
                'reference' => self::dateRangeLabel($request),
                'counts_toward_balance' => true,
            ];
        }

        usort($items, fn (array $a, array $b) => $a['date']->timestamp <=> $b['date']->timestamp);

        $running = 0;
        foreach ($items as &$item) {
            if (($item['counts_toward_balance'] ?? true) === false) {
                $item['balance_after'] = self::formatMinutes($running);

                continue;
            }

            if ($item['direction'] === 'credit') {
                $running += $item['amount_minutes'];
            } else {
                $running -= $item['amount_minutes'];
            }
            $item['balance_after'] = self::formatMinutes($running);
        }
        unset($item);

        return $items;
    }

    /**
     * @return \Illuminate\Support\Collection<int, LeaveRequestLog>
     */
    public static function offsetActivityLogs(int $userId, ?int $year = null): Collection
    {
        return LeaveRequestLog::query()
            ->with(['leaveRequest', 'performer'])
            ->whereHas('leaveRequest', function ($query) use ($userId, $year) {
                $query->where('user_id', $userId)->where('type', 'offset');
                if ($year !== null) {
                    $query->whereYear('start_date', $year);
                }
            })
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();
    }

    /**
     * @return \Illuminate\Support\Collection<int, LeaveRequestLog>
     */
    public static function activityLogs(int $userId, ?int $year = null): Collection
    {
        return LeaveRequestLog::query()
            ->with(['leaveRequest', 'performer'])
            ->whereHas('leaveRequest', function ($query) use ($userId, $year) {
                $query->where('user_id', $userId);
                if ($year !== null) {
                    $query->whereYear('start_date', $year);
                }
            })
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();
    }

    public static function sumApprovedLeaveCreditDays(int $userId, int $year): float
    {
        return (float) LeaveRequest::query()
            ->where('user_id', $userId)
            ->whereIn('type', ['vacation_leave', 'sick_leave', 'leave'])
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->get()
            ->sum(fn (LeaveRequest $request) => (float) $request->days);
    }

    public static function sumApprovedOvertimeMinutes(int $userId): int
    {
        $total = 0;
        $today = Carbon::today();

        $requests = LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('type', 'overtime')
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->get(['reason']);

        foreach ($requests as $request) {
            $total += self::overtimeMinutesFromRequest($request);
        }

        return $total;
    }

    public static function sumApprovedOffsetMinutes(int $userId): int
    {
        $total = 0;

        $requests = LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('type', 'offset')
            ->where('status', 'approved')
            ->get();

        foreach ($requests as $request) {
            $total += self::offsetMinutesFromRequest($request);
        }

        return $total;
    }

    public static function formatMinutes(int $minutes): string
    {
        $sign = $minutes < 0 ? '-' : '';
        $abs = abs($minutes);

        return $sign.sprintf('%02d:%02d', intdiv($abs, 60), $abs % 60);
    }

    public static function isTrackableEmployee(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return in_array($user->role, ['employee', 'hr'], true) || $user->isStaffMember();
    }

    private static function overtimeMinutesFromRequest(LeaveRequest $request): int
    {
        return $request->parseOvertimeTotalMinutesFromReason() ?? 0;
    }

    private static function offsetMinutesFromRequest(LeaveRequest $request): int
    {
        return $request->parseOffsetHoursToDeductMinutes()
            ?? (int) round(((float) $request->days * 8) * 60);
    }

    private static function dateRangeLabel(LeaveRequest $request): string
    {
        $start = $request->start_date?->format('M j, Y') ?? '—';
        if ($request->end_date && $request->end_date->ne($request->start_date)) {
            return $start.' – '.$request->end_date->format('M j, Y');
        }

        return $start;
    }
}
