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
            ->orderBy('time_in', 'desc')
            ->paginate(50);

        return view('admin.dtr.index', compact('dtrs', 'employees'));
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
            'overtime_hours' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,absent,late,half_day,on_leave',
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

            if ($request->filled('overtime_hours')) {
                [$oh, $om] = explode(':', $request->overtime_hours);
                $overtimeDecimal = ((int) $oh) + ((int) $om / 60);
            }

            $totalDecimal = $workedDecimal + $addedDecimal;

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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
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
                    // Expected CSV format: Email, Date, Added Time From Note, Total Hours, Overtime Hours, Status, Remarks
                    $email = trim($row[0] ?? '');
                    $date = trim($row[1] ?? '');
                    $addedTimeFromNote = trim($row[2] ?? '');
                    $totalHours = trim($row[3] ?? '0');
                    $overtimeHours = trim($row[4] ?? '0');
                    $status = trim($row[5] ?? 'present');
                    $remarks = trim($row[6] ?? '');

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

                    // Parse numeric values
                    $addedTimeFromNoteValue = is_numeric($addedTimeFromNote) ? (float)$addedTimeFromNote : 0;
                    $totalHoursValue = is_numeric($totalHours) ? (float)$totalHours : 0;
                    $overtimeHoursValue = is_numeric($overtimeHours) ? (float)$overtimeHours : 0;

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
                            'total_hours' => $totalHoursValue + $addedTimeFromNoteValue,
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
                            'total_hours' => $totalHoursValue + $addedTimeFromNoteValue,
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
            ['Employee Email', 'Date (YYYY-MM-DD)', 'Added Time From Note', 'Total Hours', 'Overtime Hours', 'Status', 'Remarks'],
            ['employee@example.com', '2024-12-01', '8 hours regular work', '8.00', '0.00', 'present', 'Regular work day'],
            ['employee@example.com', '2024-12-02', '8 hours + 2 hours overtime', '10.00', '2.00', 'present', 'Overtime work'],
            ['employee@example.com', '2024-12-03', 'Half day work', '4.00', '0.00', 'half_day', 'Left early'],
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
