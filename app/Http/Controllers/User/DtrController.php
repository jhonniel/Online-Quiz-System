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

        // Calculate net overtime balance (same as leave requests page)
        $currentYear = now()->year;
        $months = $user->overtime_months_credited ?? 12;
        if ($months === 12) {
            $fromDate = now()->copy()->startOfYear();
        } else {
            $fromDate = now()->copy()->subMonths($months)->startOfDay();
        }

        // Overtime summary from DTR + approved overtime leave requests for the configured window,
        // minus any approved Offset requests and deficit hours
        $dtrOvertimeQuery = Dtr::where('user_id', $user->id);
        if ($months === 12) {
            $dtrOvertimeQuery->whereYear('date', $currentYear);
        } else {
            $dtrOvertimeQuery->whereDate('date', '>=', $fromDate->toDateString());
        }
        $netOvertimeHours = $dtrOvertimeQuery->sum('overtime_hours');

        // Add overtime coming from approved overtime leave requests (HH:MM in reason)
        $approvedOvertimeRequestsQuery = LeaveRequest::where('user_id', $user->id)
            ->where('type', 'overtime')
            ->where('status', 'approved');

        if ($months === 12) {
            $approvedOvertimeRequestsQuery->whereYear('start_date', $currentYear);
        } else {
            $approvedOvertimeRequestsQuery->whereDate('start_date', '>=', $fromDate->toDateString());
        }

        $approvedOvertimeRequests = $approvedOvertimeRequestsQuery->get();

        $overtimeFromLeavesMinutes = 0;
        foreach ($approvedOvertimeRequests as $otRequest) {
            $raw = $otRequest->reason ?? '';
            if (preg_match('/Total Overtime Hours:\s*([0-9]{2}:[0-9]{2})/', $raw, $m)) {
                [$h, $mPart] = array_map('intval', explode(':', $m[1]));
                $overtimeFromLeavesMinutes += $h * 60 + $mPart;
            }
        }

        $netOvertimeHours += $overtimeFromLeavesMinutes / 60;

        // Subtract deficit hours from overtime balance (allow negative values)
        $deficitQuery = \App\Models\DtrDeficit::where('user_id', $user->id)
            ->where('is_applied', true);

        if ($months === 12) {
            $deficitQuery->whereYear('week_start_date', $currentYear);
        } else {
            $deficitQuery->whereDate('week_start_date', '>=', $fromDate->toDateString());
        }

        $totalDeficitHours = $deficitQuery->sum('deficit_hours');
        $netOvertimeHours = $netOvertimeHours - $totalDeficitHours;

        $approvedOffsetQuery = LeaveRequest::where('user_id', $user->id)
            ->where('type', 'offset')
            ->where('status', 'approved');

        if ($months === 12) {
            $approvedOffsetQuery->whereYear('start_date', $currentYear);
        } else {
            $approvedOffsetQuery->whereDate('start_date', '>=', $fromDate->toDateString());
        }

        $approvedOffsetRequests = $approvedOffsetQuery->get();

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

        return view('user.dtr.index', compact('groupedDtrs', 'totalRecords', 'totalHoursFormatted', 'totalOvertimeFormatted', 'absentCount', 'currentYear', 'overtimeWindowLabel', 'totalHoursLabel'));
    }
}
