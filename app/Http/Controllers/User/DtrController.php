<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Dtr;
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

        // Calculate totals
        $totalHours = 0;
        $totalOvertime = 0;

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

        return view('user.dtr.index', compact('groupedDtrs', 'totalRecords', 'totalHoursFormatted', 'totalOvertimeFormatted'));
    }
}
