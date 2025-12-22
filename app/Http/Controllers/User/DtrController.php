<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Dtr;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class DtrController extends Controller
{
    /**
     * Display the authenticated employee/student's DTR records.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Allow employees and students to access
        if (!in_array($user->role, ['employee', 'student'])) {
            abort(403, 'Only employees and students can view DTR records.');
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

        // Include approved leave requests as on-leave entries in the list
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from) : ($dtrs->min('date') ? $dtrs->min('date')->copy() : null);
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to) : ($dtrs->max('date') ? $dtrs->max('date')->copy() : null);
        if ($dateFrom && $dateTo && $dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        if ($dateFrom && $dateTo) {
            $approvedLeaves = LeaveRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $dateTo->toDateString())
                ->where(function ($q) use ($dateFrom) {
                    $q->whereNull('end_date')
                      ->orWhereDate('end_date', '>=', $dateFrom->toDateString());
                })
                ->get();

            $leaveEntries = collect();
            foreach ($approvedLeaves as $leave) {
                $start = Carbon::parse($leave->start_date);
                $end = $leave->end_date ? Carbon::parse($leave->end_date) : $start->copy();
                $period = new \Carbon\CarbonPeriod($start, $end);
                foreach ($period as $day) {
                    // Only within range
                    if ($day->lt($dateFrom) || $day->gt($dateTo)) {
                        continue;
                    }
                    $entry = new Dtr([
                        'user_id' => $user->id,
                        'date' => $day->copy(),
                        'total_hours' => 0,
                        'overtime_hours' => 0,
                        'status' => 'on_leave',
                        'remarks' => 'Approved Leave: ' . ($leave->type_label ?? ucfirst(str_replace('_', ' ', $leave->type))),
                    ]);
                    $entry->setRelation('user', $user);
                    $leaveEntries->push($entry);
                }
            }

            // Merge leave entries and re-sort
            if ($leaveEntries->isNotEmpty()) {
                $dtrs = $dtrs->merge($leaveEntries)->sortByDesc(function ($item) {
                    return $item->date;
                })->values();
            }
        }

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

        // ONLY count approved overtime leave requests for overtime balance (ignore DTR overtime totals)
        $netOvertimeHours = 0;
        $expiringOvertimeEntries = [];
        $expiringOvertimeTotal = 0;

        // DTR overtime entries intentionally ignored per requirement
        $dtrOvertimeEntries = collect();

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

        // Overtime balance is ONLY the total of approved overtime leave requests (no deductions)
        $netOvertimeHours = $overtimeFromLeavesMinutes / 60;

        // Build set of weeks where overtime was earned (only from approved overtime leave requests)
        $overtimeWeekKeys = [];
        foreach ($approvedOvertimeRequests as $otRequest) {
            $weekStart = $otRequest->start_date->copy()->startOfWeek()->toDateString();
            $overtimeWeekKeys[$weekStart] = true;
        }

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

    /**
     * Export the authenticated employee/student's DTR records as PDF.
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();

        // Allow employees and students to access
        if (!in_array($user->role, ['employee', 'student'])) {
            abort(403, 'Only employees and students can export DTR records.');
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

        $dtrs = $query->orderBy('date', 'asc')->get();

        // Include approved leave requests as on-leave entries
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from) : ($dtrs->min('date') ? $dtrs->min('date')->copy() : null);
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to) : ($dtrs->max('date') ? $dtrs->max('date')->copy() : null);
        if ($dateFrom && $dateTo && $dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        if ($dateFrom && $dateTo) {
            $approvedLeaves = LeaveRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $dateTo->toDateString())
                ->where(function ($q) use ($dateFrom) {
                    $q->whereNull('end_date')
                      ->orWhereDate('end_date', '>=', $dateFrom->toDateString());
                })
                ->get();

            $leaveEntries = collect();
            foreach ($approvedLeaves as $leave) {
                $start = Carbon::parse($leave->start_date);
                $end = $leave->end_date ? Carbon::parse($leave->end_date) : $start->copy();
                $period = new \Carbon\CarbonPeriod($start, $end);
                foreach ($period as $day) {
                    if ($day->lt($dateFrom) || $day->gt($dateTo)) {
                        continue;
                    }
                    $entry = new Dtr([
                        'user_id' => $user->id,
                        'date' => $day->copy(),
                        'total_hours' => 0,
                        'overtime_hours' => 0,
                        'status' => 'on_leave',
                        'remarks' => 'Approved Leave: ' . ($leave->type_label ?? ucfirst(str_replace('_', ' ', $leave->type))),
                    ]);
                    $entry->setRelation('user', $user);
                    $leaveEntries->push($entry);
                }
            }

            if ($leaveEntries->isNotEmpty()) {
                $dtrs = $dtrs->merge($leaveEntries)->sortBy(function ($item) {
                    return $item->date;
                })->values();
            }
        }

        // Calculate totals
        $totalHours = 0;
        $totalOvertime = 0;
        $totalRecords = $dtrs->count();

        foreach ($dtrs as $dtr) {
            $totalHours += ($dtr->total_hours ?? 0);
            $totalOvertime += ($dtr->overtime_hours ?? 0);
        }

        // Format totals
        $totalMinutes = (int) round($totalHours * 60);
        $totalH = intdiv($totalMinutes, 60);
        $totalM = $totalMinutes % 60;
        $totalHoursFormatted = sprintf('%02d:%02d', $totalH, $totalM);

        $totalOvertimeMinutes = (int) round($totalOvertime * 60);
        $totalOvertimeH = intdiv($totalOvertimeMinutes, 60);
        $totalOvertimeM = $totalOvertimeMinutes % 60;
        $totalOvertimeFormatted = sprintf('%02d:%02d', $totalOvertimeH, $totalOvertimeM);

        // Group by week for better organization
        $groupedByWeek = [];
        foreach ($dtrs as $dtr) {
            $weekStart = $dtr->date->copy()->startOfWeek();
            $weekEnd = $dtr->date->copy()->endOfWeek();
            $weekKey = $weekStart->toDateString() . '_' . $weekEnd->toDateString();
            $weekLabel = $weekStart->format('M d') . ' - ' . $weekEnd->format('M d, Y');

            if (!isset($groupedByWeek[$weekKey])) {
                $groupedByWeek[$weekKey] = [
                    'label' => $weekLabel,
                    'records' => [],
                ];
            }

            $groupedByWeek[$weekKey]['records'][] = $dtr;
        }

        $data = [
            'user' => $user,
            'dtrs' => $dtrs,
            'groupedByWeek' => $groupedByWeek,
            'totalRecords' => $totalRecords,
            'totalHoursFormatted' => $totalHoursFormatted,
            'totalOvertimeFormatted' => $totalOvertimeFormatted,
            'dateFrom' => $dateFrom ? $dateFrom->format('F d, Y') : 'All Time',
            'dateTo' => $dateTo ? $dateTo->format('F d, Y') : 'All Time',
        ];

        $pdf = Pdf::loadView('user.dtr.export-pdf', $data)->setPaper('a4', 'landscape');

        $filename = 'dtr_' . $user->name . '_' . ($dateFrom ? $dateFrom->format('Y-m-d') : 'all') . '_' . ($dateTo ? $dateTo->format('Y-m-d') : 'all') . '.pdf';
        return $pdf->download($filename);
    }
}
