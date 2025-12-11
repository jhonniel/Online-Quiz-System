<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Dtr;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DtrController extends Controller
{
    /**
     * Display the authenticated employee's DTR records.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Only allow employees to access
        if ($user->role !== 'employee') {
            abort(403, 'Only employees can view DTR records.');
        }

        $query = Dtr::where('user_id', $user->id);

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $dtrs = $query->orderBy('date', 'desc')->get();

        $totalRecords = $dtrs->count();

        // Calculate Total Hours:
        // - Default: current week only
        // - With filters: filtered date range
        $hasDateFilters = $request->filled('date_from') || $request->filled('date_to');

        if ($hasDateFilters) {
            // Use filtered records for total hours
            $totalHours = 0;
            foreach ($dtrs as $dtr) {
                $totalHours += ($dtr->total_hours ?? 0);
            }
            $totalHoursLabel = 'Filtered Range';
        } else {
            // Default: current week only
            $weekStart = now()->copy()->startOfWeek();
            $weekEnd = now()->copy()->endOfWeek();

            $currentWeekDtrs = Dtr::where('user_id', $user->id)
                ->whereDate('date', '>=', $weekStart->toDateString())
                ->whereDate('date', '<=', $weekEnd->toDateString())
                ->get();

            $totalHours = 0;
            foreach ($currentWeekDtrs as $dtr) {
                $totalHours += ($dtr->total_hours ?? 0);
            }
            $totalHoursLabel = 'Current Week';
        }

        // Format totals
        $totalMinutes = (int) round($totalHours * 60);
        $totalH = intdiv($totalMinutes, 60);
        $totalM = $totalMinutes % 60;
        $totalHoursFormatted = sprintf('%02d:%02d', $totalH, $totalM);

        // Calculate net overtime balance
        // Overtime Credited Window is ONLY used for expiration logic, NOT for counting
        $currentYear = now()->year;
        $months = $user->overtime_months_credited ?? 12;
        $today = Carbon::today();

        // Get DTR overtime entries for completed weeks only (count all, window only for expiration)
        $dtrOvertimeEntries = Dtr::where('user_id', $user->id)
            ->where('overtime_hours', '>', 0)
            ->whereDate('date', '<=', $today) // completed days/weeks only
            ->get(['id', 'date', 'overtime_hours']);

        // Calculate valid (non-expired) DTR overtime and track expiring
        $netOvertimeHours = 0;
        $expiringOvertimeEntries = [];
        $expiringOvertimeTotal = 0;

        foreach ($dtrOvertimeEntries as $dtr) {
            $expirationDate = $dtr->date->copy()->addMonths($months);
            $daysUntilExpiration = $today->diffInDays($expirationDate, false);

            if ($daysUntilExpiration > 0) {
                // Not expired - include in balance
                $netOvertimeHours += $dtr->overtime_hours;

                // Check if expiring within 30 days
                if ($daysUntilExpiration <= 30) {
                    $expiringOvertimeTotal += $dtr->overtime_hours;
                    $expiringOvertimeEntries[] = [
                        'type' => 'dtr',
                        'date' => $dtr->date,
                        'hours' => $dtr->overtime_hours,
                        'expiration_date' => $expirationDate,
                        'days_remaining' => $daysUntilExpiration,
                    ];
                }
            }
            // If expired (daysUntilExpiration <= 0), exclude from balance
        }

        // Get approved overtime leave requests for completed weeks only (count all, window only for expiration)
        $approvedOvertimeRequests = LeaveRequest::where('user_id', $user->id)
            ->where('type', 'overtime')
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today) // completed weeks only
            ->get();

        $overtimeFromLeavesMinutes = 0;
        foreach ($approvedOvertimeRequests as $otRequest) {
            $raw = $otRequest->reason ?? '';
            if (preg_match('/Total Overtime Hours:\s*([0-9]{2}:[0-9]{2})/', $raw, $m)) {
                [$h, $mPart] = array_map('intval', explode(':', $m[1]));
                $overtimeHours = ($h * 60 + $mPart) / 60;

                // Check expiration
                $expirationDate = $otRequest->start_date->copy()->addMonths($months);
                $daysUntilExpiration = $today->diffInDays($expirationDate, false);

                if ($daysUntilExpiration > 0) {
                    // Not expired - include in balance
                    $overtimeFromLeavesMinutes += $h * 60 + $mPart;

                    // Check if expiring within 30 days
                    if ($daysUntilExpiration <= 30) {
                        $expiringOvertimeTotal += $overtimeHours;
                        $expiringOvertimeEntries[] = [
                            'type' => 'leave_request',
                            'date' => $otRequest->start_date,
                            'hours' => $overtimeHours,
                            'expiration_date' => $expirationDate,
                            'days_remaining' => $daysUntilExpiration,
                        ];
                    }
                }
                // If expired, exclude from balance
            }
        }

        $netOvertimeHours += $overtimeFromLeavesMinutes / 60;

        // Build set of weeks where overtime was earned (DTR or overtime leave)
        $overtimeWeekKeys = [];
        foreach ($dtrOvertimeEntries as $dtr) {
            $weekStart = $dtr->date->copy()->startOfWeek()->toDateString();
            $overtimeWeekKeys[$weekStart] = true;
        }
        foreach ($approvedOvertimeRequests as $otRequest) {
            $weekStart = $otRequest->start_date->copy()->startOfWeek()->toDateString();
            $overtimeWeekKeys[$weekStart] = true;
        }

        // Get deficit hours for completed weeks starting from the user's first DTR week
        $today = Carbon::today();
        $firstDtr = Dtr::where('user_id', $user->id)->orderBy('date', 'asc')->first();
        if ($firstDtr) {
            $firstWeekStart = $firstDtr->date->copy()->startOfWeek()->toDateString();
            $totalDeficitHours = \App\Models\DtrDeficit::where('user_id', $user->id)
                ->where('is_applied', true)
                ->where('week_end_date', '<', $today->toDateString()) // Only completed weeks
                ->where('week_start_date', '>=', $firstWeekStart)
                ->sum('deficit_hours');
        } else {
            $totalDeficitHours = 0;
        }
        $netOvertimeHours = $netOvertimeHours - $totalDeficitHours;

        // Get ALL approved offset requests (no date filtering - count all)
        $approvedOffsetRequests = LeaveRequest::where('user_id', $user->id)
            ->where('type', 'offset')
            ->where('status', 'approved')
            ->get();

        // Parse offset hours from reason field (each offset may have different hours)
        $offsetHoursUsed = 0;
        foreach ($approvedOffsetRequests as $offsetRequest) {
            $raw = $offsetRequest->reason ?? '';
            if (preg_match('/Hours to Deduct:\s*([0-9]{2}:[0-9]{2})/', $raw, $m)) {
                [$h, $mPart] = array_map('intval', explode(':', $m[1]));
                $offsetHoursUsed += $h + ($mPart / 60);
            } else {
                // Fallback: if format not found, use 8 hours (for old records)
                $offsetHoursUsed += 8;
            }
        }

        $netOvertimeHours = $netOvertimeHours - $offsetHoursUsed;

        // Format expiring overtime total
        $expiringOvertimeMinutes = (int) round($expiringOvertimeTotal * 60);
        $expiringOvertimeH = intdiv($expiringOvertimeMinutes, 60);
        $expiringOvertimeM = $expiringOvertimeMinutes % 60;
        $expiringOvertimeFormatted = sprintf('%02d:%02d', $expiringOvertimeH, $expiringOvertimeM);

        // Get minimum days remaining (for countdown display)
        $minDaysRemaining = null;
        if (!empty($expiringOvertimeEntries)) {
            $minDaysRemaining = min(array_column($expiringOvertimeEntries, 'days_remaining'));
        }

        // Calculate current week deficit (not included in balance yet)
        $currentWeekStart = $today->copy()->startOfWeek();
        $currentWeekEnd = $today->copy()->endOfWeek();

        $currentWeekDtrs = Dtr::where('user_id', $user->id)
            ->whereDate('date', '>=', $currentWeekStart->toDateString())
            ->whereDate('date', '<=', $currentWeekEnd->toDateString())
            ->whereDate('date', '<=', $today->toDateString())
            ->get();

        $currentWeekTotalHours = $currentWeekDtrs->sum('total_hours');
        $currentWeekDeficitHours = max(0, 40.0 - $currentWeekTotalHours);

        // Format current week deficit
        $currentWeekDeficitMinutes = (int) round($currentWeekDeficitHours * 60);
        $currentWeekDeficitH = intdiv($currentWeekDeficitMinutes, 60);
        $currentWeekDeficitM = $currentWeekDeficitMinutes % 60;
        $currentWeekDeficitFormatted = sprintf('%02d:%02d', $currentWeekDeficitH, $currentWeekDeficitM);

        // Format net overtime (handle negative values)
        $isNegative = $netOvertimeHours < 0;
        $absOvertimeMinutes = (int) round(abs($netOvertimeHours) * 60);
        $overtimeHoursPart = intdiv($absOvertimeMinutes, 60);
        $overtimeMinutesPart = $absOvertimeMinutes % 60;
        $totalOvertimeFormatted = ($isNegative ? '-' : '') . sprintf('%02d:%02d', $overtimeHoursPart, $overtimeMinutesPart);

        // Build label for the overtime window
        if ($months === 12) {
            $overtimeWindowLabel = 'This Year';
        } else {
            $overtimeWindowLabel = "Last {$months} month(s)";
        }

        // Calculate absent count for current year
        $absentCount = Dtr::where('user_id', $user->id)
            ->where('status', 'absent')
            ->whereYear('date', $currentYear)
            ->count();

        // Group DTRs by Month -> ISO Week
        $groupedDtrs = [];

        foreach ($dtrs as $dtr) {
            $monthKey = $dtr->date->format('Y-m');
            $monthLabel = $dtr->date->format('F Y');

            $isoYear = $dtr->date->format('o');
            $weekNumber = $dtr->date->isoWeek;
            $weekKey = $isoYear . '-W' . $weekNumber;

            $weekStart = $dtr->date->copy()->startOfWeek();
            $weekEnd = $dtr->date->copy()->endOfWeek();
            $weekLabel = 'Week ' . $weekNumber . ' (' . $weekStart->format('M d') . ' - ' . $weekEnd->format('M d') . ')';

            if (!isset($groupedDtrs[$monthKey])) {
                $groupedDtrs[$monthKey] = [
                    'label' => $monthLabel,
                    'weeks' => [],
                ];
            }

            if (!isset($groupedDtrs[$monthKey]['weeks'][$weekKey])) {
                $groupedDtrs[$monthKey]['weeks'][$weekKey] = [
                    'label' => $weekLabel,
                    'records' => [],
                ];
            }

            $groupedDtrs[$monthKey]['weeks'][$weekKey]['records'][] = $dtr;
        }

        return view('user.dtr.index', compact(
            'groupedDtrs',
            'totalRecords',
            'totalHoursFormatted',
            'totalOvertimeFormatted',
            'absentCount',
            'currentYear',
            'overtimeWindowLabel',
            'totalHoursLabel',
            'currentWeekDeficitFormatted',
            'currentWeekDeficitHours',
            'expiringOvertimeFormatted',
            'expiringOvertimeTotal',
            'minDaysRemaining',
            'expiringOvertimeEntries'
        ));
    }
}
