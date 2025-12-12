<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dtr;
use App\Models\DtrDeficit;
use App\Models\User;
use App\Models\University;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class DtrController extends Controller
{
    /**
     * Display a listing of all employee time records.
     */
    public function index(Request $request)
    {
        $query = Dtr::with('user')
            ->whereHas('user', function($q) {
                $q->where('role', 'employee');
            });

        // Filter by department
        if ($request->filled('department_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }

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

        // Get employees for filter dropdown (only employees)
        $employees = User::where('role', 'employee')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get departments for filter dropdown
        $departments = Department::active()
            ->orderBy('name')
            ->get();

        $dtrs = $query->orderBy('date', 'desc')
            ->orderBy('user_id')
            ->get();

        $totalRecords = $dtrs->count();

        // Group DTRs by Month -> ISO Week -> Employee
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

            $employeeId = $dtr->user_id;

            if (!isset($groupedDtrs[$monthKey])) {
                $groupedDtrs[$monthKey] = [
                    'label' => $monthLabel,
                    'weeks' => [],
                ];
            }

            if (!isset($groupedDtrs[$monthKey]['weeks'][$weekKey])) {
                $groupedDtrs[$monthKey]['weeks'][$weekKey] = [
                    'label' => $weekLabel,
                    'week_start' => $weekStart->toDateString(),
                    'week_end' => $weekEnd->toDateString(),
                    'employees' => [],
                ];
            }

            if (!isset($groupedDtrs[$monthKey]['weeks'][$weekKey]['employees'][$employeeId])) {
                $groupedDtrs[$monthKey]['weeks'][$weekKey]['employees'][$employeeId] = [
                    'employee' => $dtr->user,
                    'records' => [],
                ];
            }

            $groupedDtrs[$monthKey]['weeks'][$weekKey]['employees'][$employeeId]['records'][] = $dtr;
        }

        return view('admin.dtr.index', compact('groupedDtrs', 'employees', 'departments', 'totalRecords'));
    }

    /**
     * Show the form for creating a new DTR record.
     */
    public function create()
    {
        $employees = User::where('role', 'employee')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Determine if sections should be collapsed by default (only for students)
        $collapseByDefault = auth()->check() && auth()->user()->role === 'student';

        return view('admin.dtr.create', compact('employees', 'collapseByDefault'));
    }

    /**
     * Store a newly created DTR record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|exists:users,id',
            'date' => 'required|date',
            'added_time_from_note' => 'nullable|date_format:H:i',
            'total_hours' => 'required|date_format:H:i',
            // Overtime is auto-computed as (Total Hours - 8:00) when Total Hours > 8:00
            'overtime_hours' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,absent,late,half_day,on_leave,travel',
            'is_travel' => 'nullable|boolean',
            'remarks' => 'nullable|string|max:1000',
        ]);

        // Prevent setting 'absent' status for future dates
        $requestDate = Carbon::parse($request->date);
        $today = Carbon::today();
        if ($requestDate->gt($today) && $request->status === 'absent') {
            return redirect()->back()
                ->withErrors(['status' => 'Cannot set absent status for future dates.'])
                ->withInput();
        }

        // Handle travel checkbox - override status if is_travel is checked
        $status = $request->status;
        if ($request->boolean('is_travel')) {
            $status = 'travel';
        }

        // Convert HH:MM inputs to decimal hours (same for all employees)
        $workedDecimal = 0;
        $addedDecimal = 0;
        $overtimeDecimal = 0;

        if ($request->filled('total_hours')) {
            [$h, $m] = explode(':', $request->total_hours);
            $workedDecimal = ((int) $h) + ((int) $m / 60);
        }

        if ($request->filled('added_time_from_note')) {
            [$eh, $em] = explode(':', $request->added_time_from_note);
            $addedDecimal = ((int) $eh) + ((int) $em / 60);
        }

        // Total hours for the day = Worked + Added
        $totalDecimal = $workedDecimal + $addedDecimal;

        // Overtime is any hours beyond the standard 8:00
        $standardDecimal = 8.0;
        $overtimeDecimal = $totalDecimal > $standardDecimal
            ? $totalDecimal - $standardDecimal
            : 0;

        // Process bulk creation for selected employees
        $userIds = $request->user_ids;
        $created = 0;
        $skipped = [];
        $errors = [];

        foreach ($userIds as $userId) {
            // Verify user is an employee
            $employee = User::where('id', $userId)
                ->where('role', 'employee')
                ->first();

            if (!$employee) {
                $errors[] = "User ID {$userId} is not an employee.";
                continue;
            }

            // Check if record already exists for this date
            $existingDtr = Dtr::where('user_id', $userId)
                ->whereDate('date', $request->date)
                ->first();

            if ($existingDtr) {
                $skipped[] = $employee->name;
                continue;
            }

            try {
                Dtr::create([
                    'user_id' => $userId,
                    'date' => $request->date,
                    'added_time_from_note' => $addedDecimal,
                    'total_hours' => $totalDecimal,
                    'overtime_hours' => $overtimeDecimal,
                    'status' => $status,
                    'remarks' => $request->remarks,
                ]);

                // Calculate and store weekly deficit for this employee
                $this->calculateAndStoreWeeklyDeficit($userId, Carbon::parse($request->date));

                $created++;
            } catch (\Exception $e) {
                Log::error("DTR creation failed for user {$userId}: " . $e->getMessage());
                $errors[] = "Failed to create DTR for {$employee->name}: " . $e->getMessage();
            }
        }

        // Build success/error messages
        $messages = [];
        if ($created > 0) {
            $messages[] = "Successfully created {$created} DTR record(s).";
        }
        if (count($skipped) > 0) {
            $messages[] = "Skipped " . count($skipped) . " employee(s) (records already exist): " . implode(', ', $skipped);
        }
        if (count($errors) > 0) {
            $messages[] = "Errors: " . implode(' ', $errors);
        }

        if ($created > 0) {
            return redirect()->route('admin.dtr.index')
                ->with('success', implode(' ', $messages));
        } else {
            return redirect()->back()
                ->withErrors(['error' => implode(' ', $messages)])
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dtr $dtr)
    {
        $employees = User::where('role', 'employee')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Convert stored decimal hours back to HH:MM for form fields
        $workedDecimal = max(($dtr->total_hours ?? 0) - ($dtr->added_time_from_note ?? 0), 0);
        $workedMinutes = (int) round($workedDecimal * 60);
        $workedH = intdiv($workedMinutes, 60);
        $workedM = $workedMinutes % 60;
        $workedFormatted = sprintf('%02d:%02d', $workedH, $workedM);

        $addedMinutes = (int) round(($dtr->added_time_from_note ?? 0) * 60);
        $addedH = intdiv($addedMinutes, 60);
        $addedM = $addedMinutes % 60;
        $addedFormatted = sprintf('%02d:%02d', $addedH, $addedM);

        return view('admin.dtr.edit', compact('dtr', 'employees', 'workedFormatted', 'addedFormatted'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dtr $dtr)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'added_time_from_note' => 'nullable|date_format:H:i',
            'total_hours' => 'required|date_format:H:i',
            'status' => 'required|in:present,absent,late,half_day,on_leave,travel',
            'is_travel' => 'nullable|boolean',
            'remarks' => 'nullable|string|max:1000',
        ]);

        // Prevent setting 'absent' status for future dates
        $requestDate = Carbon::parse($request->date);
        $today = Carbon::today();
        if ($requestDate->gt($today) && $request->status === 'absent') {
            return redirect()->back()
                ->withErrors(['status' => 'Cannot set absent status for future dates.'])
                ->withInput();
        }

        // Handle travel checkbox - override status if is_travel is checked
        $status = $request->status;
        if ($request->boolean('is_travel')) {
            $status = 'travel';
        }

        // Verify user is an employee
        $employee = User::where('id', $request->user_id)
            ->where('role', 'employee')
            ->first();

        if (!$employee) {
            return redirect()->back()
                ->withErrors(['user_id' => 'Selected user is not an employee.'])
                ->withInput();
        }

        try {
            // Convert HH:MM inputs to decimal hours
            $workedDecimal = 0;
            $addedDecimal = 0;

            if ($request->filled('total_hours')) {
                [$h, $m] = explode(':', $request->total_hours);
                $workedDecimal = ((int) $h) + ((int) $m / 60);
            }

            if ($request->filled('added_time_from_note')) {
                [$eh, $em] = explode(':', $request->added_time_from_note);
                $addedDecimal = ((int) $eh) + ((int) $em / 60);
            }

            $totalDecimal = $workedDecimal + $addedDecimal;

            // Overtime is any hours beyond the standard 8:00
            $standardDecimal = 8.0;
            $overtimeDecimal = $totalDecimal > $standardDecimal
                ? $totalDecimal - $standardDecimal
                : 0;

            $dtr->update([
                'user_id' => $request->user_id,
                'date' => $request->date,
                'added_time_from_note' => $addedDecimal,
                'total_hours' => $totalDecimal,
                'overtime_hours' => $overtimeDecimal,
                'status' => $status,
                'remarks' => $request->remarks,
            ]);

            // Calculate and store weekly deficit
            $this->calculateAndStoreWeeklyDeficit($request->user_id, Carbon::parse($request->date));

            return redirect()->route('admin.dtr.index')
                ->with('success', 'DTR record updated successfully.');
        } catch (\Exception $e) {
            Log::error('DTR update failed: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to update DTR record: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dtr $dtr)
    {
        try {
            $userId = $dtr->user_id;
            $date = $dtr->date;

            $dtr->delete();

            // Recalculate weekly deficit after deletion
            $this->calculateAndStoreWeeklyDeficit($userId, Carbon::parse($date));

            return redirect()->route('admin.dtr.index')
                ->with('success', 'DTR record deleted successfully.');
        } catch (\Exception $e) {
            Log::error('DTR deletion failed: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete DTR record: ' . $e->getMessage()]);
        }
    }

    /**
     * Recalculate all deficit records for all employees
     */
    public function recalculateDeficits()
    {
        try {
            $employees = User::where('role', 'employee')
                ->where('is_active', true)
                ->get();

            // Use the later of "today" and the user's last DTR date as the effective current date
            $today = Carbon::today();
            $recalculatedCount = 0;
            $fixedCount = 0;

            foreach ($employees as $employee) {
                // Determine the week range based on DTR data
                $firstDtr = Dtr::where('user_id', $employee->id)->orderBy('date', 'asc')->first();
                $lastDtr = Dtr::where('user_id', $employee->id)->orderBy('date', 'desc')->first();

                if (!$firstDtr || !$lastDtr) {
                    continue;
                }

                // Effective "today" for completed-week checks
                $effectiveToday = $lastDtr->date->gt($today) ? $lastDtr->date->copy() : $today->copy();

                $weekStart = $firstDtr->date->copy()->startOfWeek();
                $lastCompletedWeekEnd = $effectiveToday->copy()->startOfWeek()->subDay(); // end of last completed week

                // Iterate each week from first DTR week to last completed week
                while ($weekStart->lte($lastCompletedWeekEnd)) {
                    $weekEnd = $weekStart->copy()->endOfWeek();

                    // Get DTR records for this week (excluding future dates)
                    $weeklyDtrs = Dtr::where('user_id', $employee->id)
                        ->whereDate('date', '>=', $weekStart->toDateString())
                        ->whereDate('date', '<=', $weekEnd->toDateString())
                        ->whereDate('date', '<=', $effectiveToday->toDateString())
                        ->get();

                    $weeklyTotalHours = $weeklyDtrs->sum('total_hours');
                    $weeklyBaseHours = 40.0;
                    $correctDeficitHours = max(0, $weeklyBaseHours - $weeklyTotalHours);

                    // Upsert or delete deficit record
                    if ($correctDeficitHours > 0) {
                        DtrDeficit::updateOrCreate(
                            [
                                'user_id' => $employee->id,
                                'week_start_date' => $weekStart->toDateString(),
                                'week_end_date' => $weekEnd->toDateString(),
                            ],
                            [
                                'deficit_hours' => $correctDeficitHours,
                                'is_applied' => true,
                            ]
                        );
                        $fixedCount++;
                    } else {
                        // No deficit -> remove any existing record
                        DtrDeficit::where('user_id', $employee->id)
                            ->where('week_start_date', $weekStart->toDateString())
                            ->where('week_end_date', $weekEnd->toDateString())
                            ->delete();
                    }

                    $recalculatedCount++;

                    // Move to next week
                    $weekStart->addWeek();
                }

                // Remove any deficit records outside the valid range (before first DTR or after last completed week)
                DtrDeficit::where('user_id', $employee->id)
                    ->where(function ($q) use ($firstDtr, $lastCompletedWeekEnd) {
                        $q->whereDate('week_start_date', '<', $firstDtr->date->copy()->startOfWeek()->toDateString())
                          ->orWhereDate('week_end_date', '>', $lastCompletedWeekEnd->toDateString());
                    })
                    ->delete();
            }

            return redirect()->route('admin.dtr.index')
                ->with('success', "Recalculated {$recalculatedCount} deficit records. Fixed {$fixedCount} incorrect records.");
        } catch (\Exception $e) {
            Log::error('Deficit recalculation failed: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to recalculate deficits: ' . $e->getMessage()]);
        }
    }

    /**
     * Import DTR records from CSV file
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120', // 5MB max
        ]);

        try {
            $importType = $request->input('type') === 'student' ? 'student' : 'employee';
            $importRoleLabel = $importType === 'student' ? 'Student' : 'Employee';

            $file = $request->file('csv_file');
            $handle = fopen($file->getRealPath(), 'r');

            // Skip header row
            $header = fgetcsv($handle);

            $imported = 0;
            $skipped = 0;
            $errors = [];
            $affectedUserIds = [];

            DB::beginTransaction();

            while (($row = fgetcsv($handle)) !== false) {
                // Require at least 4 columns: Email, Date, Worked Hours, Added Time From Note
                // Remarks is optional (5th column)
                if (count($row) < 4) {
                    $skipped++;
                    continue;
                }

                try {
                    // Expected CSV format (same calculation logic as manual creation):
                    // [Student|Employee] Email, Date (YYYY-MM-DD), Worked Hours (HH:MM), Added Time From Note (HH:MM), Remarks
                    // Total Hours, Overtime Hours, and Status are automatically calculated/determined by the system
                    $email = trim($row[0] ?? '');
                    $date = trim($row[1] ?? '');
                    $workedHours = trim($row[2] ?? '00:00');
                    $addedTimeFromNote = trim($row[3] ?? '00:00');
                    $remarks = trim($row[4] ?? '');

                    // Validate required fields
                    if (empty($email) || empty($date)) {
                        $skipped++;
                        continue;
                    }

                    // Find user by email and role (student vs employee)
                    $user = User::where('email', $email)
                        ->where('role', $importType)
                        ->first();

                    if (!$user) {
                        $errors[] = "{$importRoleLabel} not found: {$email}";
                        $skipped++;
                        continue;
                    }

                    // Parse date
                    try {
                        $dateObj = Carbon::parse($date);
                    } catch (\Exception $e) {
                        $errors[] = "Invalid date format for {$email}: {$date}";
                        $skipped++;
                        continue;
                    }

                    // Helper to convert HH:MM to decimal hours (no AM/PM)
                    $toDecimal = function (?string $time) {
                        $time = trim((string) $time);
                        if ($time === '' || $time === '0' || $time === '00:00') {
                            return 0.0;
                        }

                        // Expect HH:MM, fallback to hours only if no colon
                        if (str_contains($time, ':')) {
                            [$h, $m] = explode(':', $time);
                            $h = (int) $h;
                            $m = (int) $m;
                        } else {
                            $h = (int) $time;
                            $m = 0;
                        }

                        return $h + ($m / 60);
                    };

                    $workedHoursValue = $toDecimal($workedHours);
                    $addedTimeFromNoteValue = $toDecimal($addedTimeFromNote);

                    // Total hours = Worked + Added
                    $totalHoursValue = $workedHoursValue + $addedTimeFromNoteValue;

                    // Overtime is derived as Total Hours beyond standard 8:00
                    $standardHours = 8.0;
                    $overtimeHoursValue = $totalHoursValue > $standardHours
                        ? $totalHoursValue - $standardHours
                        : 0;

                    // Automatically determine status based on total hours
                    // If total hours is 0, status is 'absent' (but not for future dates)
                    // If total hours > 0 and < 4, status is 'half_day'
                    // If total hours >= 4, status is 'present'
                    $today = Carbon::today();
                    if ($totalHoursValue == 0) {
                        // Only set absent if the date is today or in the past
                        if ($dateObj->lte($today)) {
                            $status = 'absent';
                        } else {
                            // For future dates with 0 hours, set as 'present' (not yet recorded)
                            $status = 'present';
                        }
                    } elseif ($totalHoursValue > 0 && $totalHoursValue < 4) {
                        $status = 'half_day';
                    } else {
                        $status = 'present';
                    }

                    // Check if record already exists - same behavior as manual creation (skip/error instead of update)
                    $existingDtr = Dtr::where('user_id', $user->id)
                        ->whereDate('date', $dateObj->format('Y-m-d'))
                        ->first();

                    if ($existingDtr) {
                        $errors[] = "DTR record already exists for {$email} on {$date}. Skipping.";
                        $skipped++;
                        continue;
                    }

                    // Create new record - same logic as manual creation
                    Dtr::create([
                        'user_id' => $user->id,
                        'date' => $dateObj->format('Y-m-d'),
                        'added_time_from_note' => $addedTimeFromNoteValue,
                        'total_hours' => $totalHoursValue,
                        'overtime_hours' => $overtimeHoursValue,
                        'status' => $status,
                        'remarks' => $remarks,
                    ]);

                    // Calculate and store weekly deficit - same as manual creation
                    $this->calculateAndStoreWeeklyDeficit($user->id, $dateObj);

                    // Track affected user
                    if (!in_array($user->id, $affectedUserIds)) {
                        $affectedUserIds[] = $user->id;
                    }

                    $imported++;
                } catch (\Exception $e) {
                    Log::error('DTR import error: ' . $e->getMessage());
                    $errors[] = "Error processing row: " . $e->getMessage();
                    $skipped++;
                }
            }

            fclose($handle);
            DB::commit();

            // Note: Weekly deficits are already calculated per record (same as manual creation)
            // No need to recalculate all weeks since we calculate for each imported record

            $message = "Successfully imported {$imported} {$importRoleLabel} DTR record(s).";
            if ($skipped > 0) {
                $message .= " {$skipped} record(s) skipped.";
            }

            $redirectRoute = $importType === 'student' ? 'admin.student-dtr.index' : 'admin.dtr.index';

            return redirect()->route($redirectRoute)
                ->with('success', $message)
                ->with('import_errors', $errors);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DTR import failed: ' . $e->getMessage());

            $redirectRoute = $request->input('type') === 'student' ? 'admin.student-dtr.index' : 'admin.dtr.index';

            return redirect()->route($redirectRoute)
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download CSV template for DTR import
     */
    public function downloadTemplate()
    {
        $templateData = [
            [
                'Employee Email',
                'Date (YYYY-MM-DD)',
                'Worked Hours (HH:MM)',
                'Added Time From Note (HH:MM)',
                'Remarks',
            ],
            ['employee@example.com', '2024-12-01', '08:00', '00:00', 'Regular work day'],
            ['employee@example.com', '2024-12-02', '08:00', '02:00', 'Overtime work - Total will be 10:00, Overtime will be 02:00'],
            ['employee@example.com', '2024-12-03', '04:00', '00:00', 'Half day - Status will be automatically determined'],
            ['employee@example.com', '2024-12-04', '00:00', '00:00', 'Absent - Status will be automatically determined'],
            ['employee@example.com', '2024-12-05', '08:00', '00:00', 'Full day work'],
        ];

        $filename = 'dtr_import_template_' . date('Y-m-d') . '.csv';

        // Create CSV content
        $handle = fopen('php://temp', 'r+');

        // Add BOM for Excel compatibility (UTF-8 BOM)
        fwrite($handle, "\xEF\xBB\xBF");

        foreach ($templateData as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Transfer-Encoding' => 'binary',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Calculate and store weekly deficit for a user
     */
    private function calculateAndStoreWeeklyDeficit($userId, Carbon $date)
    {
        try {
            // Get the week start and end dates (ISO week)
            $weekStart = $date->copy()->startOfWeek();
            $weekEnd = $date->copy()->endOfWeek();
            $today = Carbon::today();

            // Don't calculate deficit for current week (week hasn't ended yet)
            if ($today->lte($weekEnd)) {
                // Current week - don't store deficit
                return;
            }

            // Get all DTR records for this user in this week, excluding future dates
            $weeklyDtrs = Dtr::where('user_id', $userId)
                ->whereDate('date', '>=', $weekStart->toDateString())
                ->whereDate('date', '<=', $weekEnd->toDateString())
                ->whereDate('date', '<=', $today->toDateString()) // Exclude future dates
                ->get();

            // Calculate weekly total hours (only from past and today, not future)
            $weeklyTotalHours = $weeklyDtrs->sum('total_hours');

            // Calculate deficit: 40:00 (2400 minutes) - weekly total
            $weeklyBaseHours = 40.0; // 40 hours = 40:00
            $deficitHours = max(0, $weeklyBaseHours - $weeklyTotalHours);

            // Store or update deficit record (only for completed weeks with actual deficit)
            // If there's no deficit, delete any existing deficit record for this week
            if ($deficitHours > 0) {
                DtrDeficit::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'week_start_date' => $weekStart->toDateString(),
                        'week_end_date' => $weekEnd->toDateString(),
                    ],
                    [
                        'deficit_hours' => $deficitHours,
                        'is_applied' => true,
                    ]
                );
            } else {
                // No deficit - delete any existing deficit record for this week
                DtrDeficit::where('user_id', $userId)
                    ->where('week_start_date', $weekStart->toDateString())
                    ->where('week_end_date', $weekEnd->toDateString())
                    ->delete();
            }
        } catch (\Exception $e) {
            Log::error('Failed to calculate weekly deficit: ' . $e->getMessage(), [
                'user_id' => $userId,
                'date' => $date->toDateString(),
            ]);
        }
    }

    /**
     * Display a listing of all student time records.
     */
    public function studentIndex(Request $request)
    {
        $query = Dtr::with(['user.university'])
            ->whereHas('user', function($q) {
                $q->where('role', 'student');
            });

        // Filter by university
        if ($request->filled('university_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('university_id', $request->university_id);
            });
        }

        // Filter by student
        if ($request->filled('student_id')) {
            $query->where('user_id', $request->student_id);
        }

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

        // Get universities for filter dropdown
        $universities = University::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get students for filter dropdown (only students, optionally filtered by university)
        $studentsQuery = User::where('role', 'student')
            ->where('is_active', true);

        if ($request->filled('university_id')) {
            $studentsQuery->where('university_id', $request->university_id);
        }

        $students = $studentsQuery->orderBy('name')->get();

        $dtrs = $query->orderBy('date', 'desc')
            ->orderBy('user_id')
            ->get();

        $totalRecords = $dtrs->count();

        // Group DTRs by Month -> ISO Week -> Student
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

            $studentId = $dtr->user_id;

            if (!isset($groupedDtrs[$monthKey])) {
                $groupedDtrs[$monthKey] = [
                    'label' => $monthLabel,
                    'weeks' => [],
                ];
            }

            if (!isset($groupedDtrs[$monthKey]['weeks'][$weekKey])) {
                $groupedDtrs[$monthKey]['weeks'][$weekKey] = [
                    'label' => $weekLabel,
                    'students' => [],
                ];
            }

            if (!isset($groupedDtrs[$monthKey]['weeks'][$weekKey]['students'][$studentId])) {
                $groupedDtrs[$monthKey]['weeks'][$weekKey]['students'][$studentId] = [
                    'student' => $dtr->user,
                    'records' => [],
                ];
            }

            $groupedDtrs[$monthKey]['weeks'][$weekKey]['students'][$studentId]['records'][] = $dtr;
        }

        return view('admin.dtr.student-index', compact('groupedDtrs', 'students', 'universities', 'totalRecords'));
    }

    /**
     * Show the form for creating a new student DTR record.
     */
    public function studentCreate()
    {
        $students = User::where('role', 'student')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Determine if sections should be collapsed by default (only for students)
        $collapseByDefault = true; // Always collapsed for student DTR creation

        return view('admin.dtr.student-create', compact('students', 'collapseByDefault'));
    }

    /**
     * Store a newly created student DTR record.
     */
    public function studentStore(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'added_time_from_note' => 'nullable|date_format:H:i',
            'total_hours' => 'required|date_format:H:i',
            'overtime_hours' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,absent,late,half_day,on_leave,travel',
            'remarks' => 'nullable|string|max:1000',
        ]);

        // Verify user is a student
        $student = User::where('id', $request->user_id)
            ->where('role', 'student')
            ->first();

        if (!$student) {
            return redirect()->back()
                ->withErrors(['user_id' => 'Selected user is not a student.'])
                ->withInput();
        }

        // Check if record already exists for this date
        $existingDtr = Dtr::where('user_id', $request->user_id)
            ->whereDate('date', $request->date)
            ->first();

        if ($existingDtr) {
            return redirect()->back()
                ->withErrors(['date' => 'A DTR record already exists for this student on this date.'])
                ->withInput();
        }

        try {
            // Convert HH:MM inputs to decimal hours
            $workedDecimal = 0;
            $addedDecimal = 0;
            $overtimeDecimal = 0;

            if ($request->filled('total_hours')) {
                [$h, $m] = explode(':', $request->total_hours);
                $workedDecimal = ((int) $h) + ((int) $m / 60);
            }

            if ($request->filled('added_time_from_note')) {
                [$eh, $em] = explode(':', $request->added_time_from_note);
                $addedDecimal = ((int) $eh) + ((int) $em / 60);
            }

            // Total hours for the day = Worked + Added
            $totalDecimal = $workedDecimal + $addedDecimal;

            // Overtime is any hours beyond the standard 8:00
            $standardDecimal = 8.0;
            $overtimeDecimal = $totalDecimal > $standardDecimal
                ? $totalDecimal - $standardDecimal
                : 0;

            $dtr = Dtr::create([
                'user_id' => $request->user_id,
                'date' => $request->date,
                'added_time_from_note' => $addedDecimal,
                'total_hours' => $totalDecimal,
                'overtime_hours' => $overtimeDecimal,
                'status' => $request->status,
                'remarks' => $request->remarks,
            ]);

            // Calculate and store weekly deficit
            $this->calculateAndStoreWeeklyDeficit($request->user_id, Carbon::parse($request->date));

            return redirect()->route('admin.student-dtr.index')
                ->with('success', 'Student DTR record added successfully.');
        } catch (\Exception $e) {
            Log::error('Student DTR creation failed: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to create student DTR record: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified student DTR resource.
     */
    public function studentEdit(Dtr $dtr)
    {
        // Verify this is a student DTR
        if ($dtr->user->role !== 'student') {
            abort(404, 'DTR record not found for students.');
        }

        $students = User::where('role', 'student')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Determine if sections should be collapsed by default
        $collapseByDefault = true;

        return view('admin.dtr.student-edit', compact('dtr', 'students', 'collapseByDefault'));
    }

    /**
     * Update the specified student DTR resource in storage.
     */
    public function studentUpdate(Request $request, Dtr $dtr)
    {
        // Verify this is a student DTR
        if ($dtr->user->role !== 'student') {
            abort(404, 'DTR record not found for students.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'added_time_from_note' => 'nullable|date_format:H:i',
            'total_hours' => 'required|date_format:H:i',
            'status' => 'required|in:present,absent,late,half_day,on_leave,travel',
            'remarks' => 'nullable|string|max:1000',
        ]);

        // Verify user is a student
        $student = User::where('id', $request->user_id)
            ->where('role', 'student')
            ->first();

        if (!$student) {
            return redirect()->back()
                ->withErrors(['user_id' => 'Selected user is not a student.'])
                ->withInput();
        }

        try {
            // Convert HH:MM inputs to decimal hours
            $workedDecimal = 0;
            $addedDecimal = 0;

            if ($request->filled('total_hours')) {
                [$h, $m] = explode(':', $request->total_hours);
                $workedDecimal = ((int) $h) + ((int) $m / 60);
            }

            if ($request->filled('added_time_from_note')) {
                [$eh, $em] = explode(':', $request->added_time_from_note);
                $addedDecimal = ((int) $eh) + ((int) $em / 60);
            }

            $totalDecimal = $workedDecimal + $addedDecimal;

            // Overtime is any hours beyond the standard 8:00
            $standardDecimal = 8.0;
            $overtimeDecimal = $totalDecimal > $standardDecimal
                ? $totalDecimal - $standardDecimal
                : 0;

            $dtr->update([
                'user_id' => $request->user_id,
                'date' => $request->date,
                'added_time_from_note' => $addedDecimal,
                'total_hours' => $totalDecimal,
                'overtime_hours' => $overtimeDecimal,
                'status' => $request->status,
                'remarks' => $request->remarks,
            ]);

            // Calculate and store weekly deficit
            $this->calculateAndStoreWeeklyDeficit($request->user_id, Carbon::parse($request->date));

            return redirect()->route('admin.student-dtr.index')
                ->with('success', 'Student DTR record updated successfully.');
        } catch (\Exception $e) {
            Log::error('Student DTR update failed: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to update student DTR record: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Export student DTR records as PDF.
     */
    public function studentExportPdf(Request $request)
    {
        $query = Dtr::with(['user.university'])
            ->whereHas('user', function($q) {
                $q->where('role', 'student');
            });

        // Filter by university
        $selectedUniversity = null;
        if ($request->filled('university_id')) {
            $selectedUniversity = University::find($request->university_id);
            $query->whereHas('user', function($q) use ($request) {
                $q->where('university_id', $request->university_id);
            });
        }

        // Filter by student
        $selectedStudent = null;
        if ($request->filled('student_id')) {
            $selectedStudent = User::find($request->student_id);
            $query->where('user_id', $request->student_id);
        }

        // Filter by date range
        $dateFrom = $request->filled('date_from') ? $request->date_from : null;
        $dateTo = $request->filled('date_to') ? $request->date_to : null;

        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $dtrs = $query->orderBy('date', 'asc')
            ->orderBy('user_id')
            ->get();

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

        // Group by student for better organization
        $groupedByStudent = [];
        foreach ($dtrs as $dtr) {
            $studentId = $dtr->user_id;
            if (!isset($groupedByStudent[$studentId])) {
                $groupedByStudent[$studentId] = [
                    'student' => $dtr->user,
                    'records' => [],
                    'total_hours' => 0,
                    'total_overtime' => 0,
                ];
            }
            $groupedByStudent[$studentId]['records'][] = $dtr;
            $groupedByStudent[$studentId]['total_hours'] += ($dtr->total_hours ?? 0);
            $groupedByStudent[$studentId]['total_overtime'] += ($dtr->overtime_hours ?? 0);
        }

        // Format student totals
        foreach ($groupedByStudent as &$group) {
            $studentTotalMinutes = (int) round($group['total_hours'] * 60);
            $studentTotalH = intdiv($studentTotalMinutes, 60);
            $studentTotalM = $studentTotalMinutes % 60;
            $group['total_hours_formatted'] = sprintf('%02d:%02d', $studentTotalH, $studentTotalM);

            $studentOvertimeMinutes = (int) round($group['total_overtime'] * 60);
            $studentOvertimeH = intdiv($studentOvertimeMinutes, 60);
            $studentOvertimeM = $studentOvertimeMinutes % 60;
            $group['total_overtime_formatted'] = sprintf('%02d:%02d', $studentOvertimeH, $studentOvertimeM);
        }

        $data = [
            'dtrs' => $dtrs,
            'groupedByStudent' => $groupedByStudent,
            'totalRecords' => $totalRecords,
            'totalHoursFormatted' => $totalHoursFormatted,
            'totalOvertimeFormatted' => $totalOvertimeFormatted,
            'dateFrom' => $dateFrom ? Carbon::parse($dateFrom)->format('F d, Y') : 'All Time',
            'dateTo' => $dateTo ? Carbon::parse($dateTo)->format('F d, Y') : 'All Time',
            'selectedStudent' => $selectedStudent,
            'selectedUniversity' => $selectedUniversity,
        ];

        $pdf = Pdf::loadView('admin.dtr.student-export-pdf', $data)->setPaper('a4', 'landscape');

        $filename = 'student_dtr_export_' . ($dateFrom ? Carbon::parse($dateFrom)->format('Y-m-d') : 'all') . '_' . ($dateTo ? Carbon::parse($dateTo)->format('Y-m-d') : 'all') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export employee DTR records as PDF.
     */
    public function exportPdf(Request $request)
    {
        $query = Dtr::with('user')
            ->whereHas('user', function($q) {
                $q->where('role', 'employee');
            });

        // Filter by department
        $selectedDepartment = null;
        if ($request->filled('department_id')) {
            $selectedDepartment = Department::find($request->department_id);
            $query->whereHas('user', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // Filter by employee
        $selectedEmployee = null;
        if ($request->filled('employee_id')) {
            $selectedEmployee = User::find($request->employee_id);
            $query->where('user_id', $request->employee_id);
        }

        // Filter by date range
        $dateFrom = $request->filled('date_from') ? $request->date_from : null;
        $dateTo = $request->filled('date_to') ? $request->date_to : null;

        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $dtrs = $query->orderBy('date', 'asc')
            ->orderBy('user_id')
            ->get();

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

        // Calculate total deficit hours for the date range
        // Calculate deficit directly from DTR records grouped by week
        $totalDeficitHours = 0;
        $totalOvertimeFromCompletedWeeks = 0; // Only count overtime from completed weeks
        if ($dateFrom && $dateTo) {
            $today = Carbon::today();

            // Group DTR records by employee and week
            $weeklyGroups = [];
            foreach ($dtrs as $dtr) {
                $weekStart = $dtr->date->copy()->startOfWeek();
                $weekEnd = $dtr->date->copy()->endOfWeek();
                $weekKey = $dtr->user_id . '_' . $weekStart->toDateString() . '_' . $weekEnd->toDateString();

                // Only calculate deficit for completed weeks (not current week)
                if ($today->gt($weekEnd)) {
                    if (!isset($weeklyGroups[$weekKey])) {
                        $weeklyGroups[$weekKey] = [
                            'user_id' => $dtr->user_id,
                            'week_start' => $weekStart,
                            'week_end' => $weekEnd,
                            'total_hours' => 0,
                            'overtime_hours' => 0,
                        ];
                    }
                    $weeklyGroups[$weekKey]['total_hours'] += ($dtr->total_hours ?? 0);
                    $weeklyGroups[$weekKey]['overtime_hours'] += ($dtr->overtime_hours ?? 0);
                }
            }

            // Calculate deficit per week: max(0, 40 hours - weekly total)
            foreach ($weeklyGroups as $weekGroup) {
                $weeklyBaseHours = 40.0;
                $weeklyDeficit = max(0, $weeklyBaseHours - $weekGroup['total_hours']);
                $totalDeficitHours += $weeklyDeficit;
                $totalOvertimeFromCompletedWeeks += $weekGroup['overtime_hours'];
            }
        } else {
            // If no date range, use all overtime (but this shouldn't happen in PDF export)
            $totalOvertimeFromCompletedWeeks = $totalOvertime;
        }

        // Format deficit
        $totalDeficitMinutes = (int) round($totalDeficitHours * 60);
        $totalDeficitH = intdiv($totalDeficitMinutes, 60);
        $totalDeficitM = $totalDeficitMinutes % 60;
        $totalDeficitFormatted = sprintf('%02d:%02d', $totalDeficitH, $totalDeficitM);

        // Calculate balance overtime: Deficit - Overtime (from completed weeks only)
        $balanceOvertimeHours = $totalOvertimeFromCompletedWeeks - $totalDeficitHours;
        $balanceOvertimeMinutes = (int) round(abs($balanceOvertimeHours) * 60);
        $balanceOvertimeH = intdiv($balanceOvertimeMinutes, 60);
        $balanceOvertimeM = $balanceOvertimeMinutes % 60;
        $balanceOvertimeFormatted = ($balanceOvertimeHours < 0 ? '-' : '') . sprintf('%02d:%02d', $balanceOvertimeH, $balanceOvertimeM);
        $isBalanceNegative = $balanceOvertimeHours < 0;

        // Group by employee for better organization
        $groupedByEmployee = [];
        foreach ($dtrs as $dtr) {
            $employeeId = $dtr->user_id;
            if (!isset($groupedByEmployee[$employeeId])) {
                $groupedByEmployee[$employeeId] = [
                    'employee' => $dtr->user,
                    'records' => [],
                    'total_hours' => 0,
                    'total_overtime' => 0,
                ];
            }
            $groupedByEmployee[$employeeId]['records'][] = $dtr;
            $groupedByEmployee[$employeeId]['total_hours'] += ($dtr->total_hours ?? 0);
            $groupedByEmployee[$employeeId]['total_overtime'] += ($dtr->overtime_hours ?? 0);
        }

        // Format employee totals and calculate per-employee deficit and balance
        foreach ($groupedByEmployee as &$group) {
            $employee = $group['employee'];

            $employeeTotalMinutes = (int) round($group['total_hours'] * 60);
            $employeeTotalH = intdiv($employeeTotalMinutes, 60);
            $employeeTotalM = $employeeTotalMinutes % 60;
            $group['total_hours_formatted'] = sprintf('%02d:%02d', $employeeTotalH, $employeeTotalM);

            $employeeOvertimeMinutes = (int) round($group['total_overtime'] * 60);
            $employeeOvertimeH = intdiv($employeeOvertimeMinutes, 60);
            $employeeOvertimeM = $employeeOvertimeMinutes % 60;
            $group['total_overtime_formatted'] = sprintf('%02d:%02d', $employeeOvertimeH, $employeeOvertimeM);

            // Calculate deficit for this employee based on date range
            // Calculate deficit directly from DTR records grouped by week
            $employeeDeficitHours = 0;
            $employeeOvertimeFromCompletedWeeks = 0; // Only count overtime from completed weeks
            if ($dateFrom && $dateTo) {
                $today = Carbon::today();

                // Group DTR records by week
                $weeklyGroups = [];
                foreach ($group['records'] as $dtr) {
                    $weekStart = $dtr->date->copy()->startOfWeek();
                    $weekEnd = $dtr->date->copy()->endOfWeek();
                    $weekKey = $weekStart->toDateString() . '_' . $weekEnd->toDateString();

                    // Only calculate deficit for completed weeks (not current week)
                    if ($today->gt($weekEnd)) {
                        if (!isset($weeklyGroups[$weekKey])) {
                            $weeklyGroups[$weekKey] = [
                                'week_start' => $weekStart,
                                'week_end' => $weekEnd,
                                'total_hours' => 0,
                                'overtime_hours' => 0,
                            ];
                        }
                        $weeklyGroups[$weekKey]['total_hours'] += ($dtr->total_hours ?? 0);
                        $weeklyGroups[$weekKey]['overtime_hours'] += ($dtr->overtime_hours ?? 0);
                    }
                }

                // Calculate deficit per week: max(0, 40 hours - weekly total)
                foreach ($weeklyGroups as $weekGroup) {
                    $weeklyBaseHours = 40.0;
                    $weeklyDeficit = max(0, $weeklyBaseHours - $weekGroup['total_hours']);
                    $employeeDeficitHours += $weeklyDeficit;
                    $employeeOvertimeFromCompletedWeeks += $weekGroup['overtime_hours'];
                }
            } else {
                // If no date range, use all overtime (but this shouldn't happen in PDF export)
                $employeeOvertimeFromCompletedWeeks = $group['total_overtime'];
            }

            // Ensure deficit is not negative (show 00:00 if negative)
            $employeeDeficitHours = max(0, $employeeDeficitHours);

            // Format employee deficit
            $employeeDeficitMinutes = (int) round($employeeDeficitHours * 60);
            $employeeDeficitH = intdiv($employeeDeficitMinutes, 60);
            $employeeDeficitM = $employeeDeficitMinutes % 60;
            $group['total_deficit_formatted'] = sprintf('%02d:%02d', $employeeDeficitH, $employeeDeficitM);

            // Calculate balance overtime for this employee: Overtime - Deficit (from completed weeks only)
            // Negative balance means deficit exceeds overtime
            $employeeBalanceOvertimeHours = $employeeOvertimeFromCompletedWeeks - $employeeDeficitHours;
            $employeeBalanceOvertimeMinutes = (int) round(abs($employeeBalanceOvertimeHours) * 60);
            $employeeBalanceOvertimeH = intdiv($employeeBalanceOvertimeMinutes, 60);
            $employeeBalanceOvertimeM = $employeeBalanceOvertimeMinutes % 60;
            $group['balance_overtime_formatted'] = ($employeeBalanceOvertimeHours < 0 ? '-' : '') . sprintf('%02d:%02d', $employeeBalanceOvertimeH, $employeeBalanceOvertimeM);
            $group['is_balance_negative'] = $employeeBalanceOvertimeHours < 0;
        }

        $data = [
            'dtrs' => $dtrs,
            'groupedByEmployee' => $groupedByEmployee,
            'totalRecords' => $totalRecords,
            'totalHoursFormatted' => $totalHoursFormatted,
            'totalOvertimeFormatted' => $totalOvertimeFormatted,
            'totalDeficitFormatted' => $totalDeficitFormatted,
            'balanceOvertimeFormatted' => $balanceOvertimeFormatted,
            'isBalanceNegative' => $isBalanceNegative,
            'dateFrom' => $dateFrom ? Carbon::parse($dateFrom)->format('F d, Y') : 'All Time',
            'dateTo' => $dateTo ? Carbon::parse($dateTo)->format('F d, Y') : 'All Time',
            'selectedEmployee' => $selectedEmployee,
            'selectedDepartment' => $selectedDepartment,
        ];

        $pdf = Pdf::loadView('admin.dtr.export-pdf', $data)->setPaper('a4', 'landscape');

        $filename = 'employee_dtr_export_' . ($dateFrom ? Carbon::parse($dateFrom)->format('Y-m-d') : 'all') . '_' . ($dateTo ? Carbon::parse($dateTo)->format('Y-m-d') : 'all') . '.pdf';
        return $pdf->stream($filename);
    }
}
