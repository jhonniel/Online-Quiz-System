<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dtr;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

        return view('admin.dtr.index', compact('groupedDtrs', 'employees', 'totalRecords'));
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

        return view('admin.dtr.create', compact('employees'));
    }

    /**
     * Store a newly created DTR record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'added_time_from_note' => 'nullable|date_format:H:i',
            'total_hours' => 'required|date_format:H:i',
            // Overtime is auto-computed as (Total Hours - 8:00) when Total Hours > 8:00
            'overtime_hours' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,absent,late,half_day,on_leave,travel',
            'remarks' => 'nullable|string|max:1000',
        ]);

        // Verify user is an employee
        $employee = User::where('id', $request->user_id)
            ->where('role', 'employee')
            ->first();

        if (!$employee) {
            return redirect()->back()
                ->withErrors(['user_id' => 'Selected user is not an employee.'])
                ->withInput();
        }

        // Check if record already exists for this date
        $existingDtr = Dtr::where('user_id', $request->user_id)
            ->whereDate('date', $request->date)
            ->first();

        if ($existingDtr) {
            return redirect()->back()
                ->withErrors(['date' => 'A DTR record already exists for this employee on this date.'])
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

            Dtr::create([
                'user_id' => $request->user_id,
                'date' => $request->date,
                'added_time_from_note' => $addedDecimal,
                'total_hours' => $totalDecimal,
                'overtime_hours' => $overtimeDecimal,
                'status' => $request->status,
                'remarks' => $request->remarks,
            ]);

            return redirect()->route('admin.dtr.index')
                ->with('success', 'DTR record added successfully.');
        } catch (\Exception $e) {
            Log::error('DTR creation failed: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to create DTR record: ' . $e->getMessage()])
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
            'remarks' => 'nullable|string|max:1000',
        ]);

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
                'status' => $request->status,
                'remarks' => $request->remarks,
            ]);

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
    public function destroy(string $id)
    {
        //
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
            $file = $request->file('csv_file');
            $handle = fopen($file->getRealPath(), 'r');

            // Skip header row
            $header = fgetcsv($handle);

            $imported = 0;
            $skipped = 0;
            $errors = [];

            DB::beginTransaction();

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 3) {
                    $skipped++;
                    continue;
                }

                try {
                    // Expected CSV format:
                    // Employee Email, Date (YYYY-MM-DD), Worked Hours (HH:MM), Added Time From Note (HH:MM),
                    // Total Hours (HH:MM - optional, will be recalculated), Overtime (HH:MM - optional),
                    // Status, Remarks
                    $email = trim($row[0] ?? '');
                    $date = trim($row[1] ?? '');
                    $workedHours = trim($row[2] ?? '00:00');
                    $addedTimeFromNote = trim($row[3] ?? '00:00');
                    $csvTotalHours = trim($row[4] ?? '00:00');      // not strictly needed, kept for compatibility
                    $csvOvertime = trim($row[5] ?? '00:00');        // not used; we recalc overtime
                    $status = trim($row[6] ?? 'present');
                    $remarks = trim($row[7] ?? '');

                    // Validate required fields
                    if (empty($email) || empty($date)) {
                        $skipped++;
                        continue;
                    }

                    // Find employee by email
                    $employee = User::where('email', $email)
                        ->where('role', 'employee')
                        ->first();

                    if (!$employee) {
                        $errors[] = "Employee not found: {$email}";
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

                    // Validate status
                    $validStatuses = ['present', 'absent', 'late', 'half_day', 'on_leave'];
                    if (!in_array($status, $validStatuses)) {
                        $status = 'present';
                    }

                    // Check if record already exists
                    $existingDtr = Dtr::where('user_id', $employee->id)
                        ->whereDate('date', $dateObj->format('Y-m-d'))
                        ->first();

                    if ($existingDtr) {
                        // Update existing record
                        $existingDtr->update([
                            'added_time_from_note' => $addedTimeFromNoteValue,
                            'total_hours' => $totalHoursValue,
                            'overtime_hours' => $overtimeHoursValue,
                            'status' => $status,
                            'remarks' => $remarks,
                        ]);
                    } else {
                        // Create new record
                        Dtr::create([
                            'user_id' => $employee->id,
                            'date' => $dateObj->format('Y-m-d'),
                            'added_time_from_note' => $addedTimeFromNoteValue,
                            'total_hours' => $totalHoursValue,
                            'overtime_hours' => $overtimeHoursValue,
                            'status' => $status,
                            'remarks' => $remarks,
                        ]);
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

            $message = "Successfully imported {$imported} DTR record(s).";
            if ($skipped > 0) {
                $message .= " {$skipped} record(s) skipped.";
            }

            return redirect()->route('admin.dtr.index')
                ->with('success', $message)
                ->with('import_errors', $errors);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DTR import failed: ' . $e->getMessage());

            return redirect()->route('admin.dtr.index')
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
                'Total Hours (HH:MM, optional)',
                'Overtime (HH:MM, optional)',
                'Status',
                'Remarks',
            ],
            ['employee@example.com', '2024-12-01', '08:00', '00:00', '08:00', '00:00', 'present', 'Regular work day'],
            ['employee@example.com', '2024-12-02', '08:00', '02:00', '10:00', '02:00', 'present', 'Overtime work'],
            ['employee@example.com', '2024-12-03', '04:00', '00:00', '04:00', '00:00', 'half_day', 'Left early'],
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
}
