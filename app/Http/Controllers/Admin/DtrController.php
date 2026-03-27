<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\TimeExtraction;
use App\Helpers\SmartTimeParser;
use App\Models\Dtr;
use App\Models\DtrDeficit;
use App\Models\User;
use App\Models\University;
use App\Models\Department;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Barryvdh\DomPDF\Facade\Pdf;

class DtrController extends Controller
{
    /**
     * Display a listing of all employee time records.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Dtr::with('user')
            ->whereHas('user', function($q) {
                $q->where('role', 'employee');
            });

        // Apply department restrictions if user has Employee Management with restrictions
        if ($user->canAccessEmployeeManagement()) {
            $allowedDepartmentIds = $user->getAllowedDepartmentIds();
            if ($allowedDepartmentIds !== null) {
                // User has department restrictions - only show allowed departments
                $query->whereHas('user', function($q) use ($allowedDepartmentIds) {
                    $q->whereIn('department_id', $allowedDepartmentIds);
                });
            }
            // If $allowedDepartmentIds is null, user can see all departments (no restrictions)
        }

        // Filter by department (user-selected filter)
        if ($request->filled('department_id')) {
            $selectedDeptId = $request->department_id;
            // Only apply if user can manage this department
            if ($user->canManageDepartment($selectedDeptId)) {
                $query->whereHas('user', function($q) use ($selectedDeptId) {
                    $q->where('department_id', $selectedDeptId);
                });
            }
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

        // Get employees for filter dropdown (only employees, respecting department restrictions)
        $employeesQuery = User::where('role', 'employee')
            ->where('is_active', true);

        // Apply department restrictions to employee list
        if ($user->canAccessEmployeeManagement()) {
            $allowedDepartmentIds = $user->getAllowedDepartmentIds();
            if ($allowedDepartmentIds !== null) {
                $employeesQuery->whereIn('department_id', $allowedDepartmentIds);
            }
        }

        $employees = $employeesQuery->orderBy('name')->get();

        // Get departments for filter dropdown (only departments user can manage)
        $departmentsQuery = Department::active();

        if ($user->canAccessEmployeeManagement()) {
            $allowedDepartmentIds = $user->getAllowedDepartmentIds();
            if ($allowedDepartmentIds !== null) {
                $departmentsQuery->whereIn('id', $allowedDepartmentIds);
            }
        }

        $departments = $departmentsQuery->orderBy('name')->get();

        $dtrs = $query->orderBy('date', 'desc')
            ->orderBy('user_id')
            ->get();

        // Approved overtime from leave requests (only count approved overtime requests)
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from) : ($dtrs->min('date') ? $dtrs->min('date')->copy() : null);
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to) : ($dtrs->max('date') ? $dtrs->max('date')->copy() : null);
        if ($dateFrom && $dateTo && $dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        // Calculate overtime from Total Hours (hours above 8:00 per day)
        foreach ($dtrs as $dtr) {
            $totalHours = (float) ($dtr->total_hours ?? 0);
            // Overtime is calculated as hours above 8:00 per day
            $dtr->overtime_hours = max(0, $totalHours - 8.0);
        }

        // Automatically add DTR records for approved leave requests (excluding overtime)
        if ($dateFrom && $dateTo) {
            // Use all employees in current scope (not only those already having DTR rows)
            $employeeIds = $employees->pluck('id')->unique()->values();
            $approvedLeaves = LeaveRequest::with('user')
                ->whereIn('user_id', $employeeIds)
                ->where('status', 'approved')
                ->where('type', '!=', 'overtime') // Exclude overtime type
                ->whereDate('start_date', '<=', $dateTo->toDateString())
                ->where(function ($q) use ($dateFrom) {
                    $q->whereNull('end_date')
                      ->orWhereDate('end_date', '>=', $dateFrom->toDateString());
                })
                ->get();

            // Create a map of existing DTRs by user_id and date for quick lookup
            $dtrMap = [];
            foreach ($dtrs as $dtr) {
                $dateKey = $dtr->date->format('Y-m-d');
                $dtrMap[$dtr->user_id][$dateKey] = $dtr;
            }

            $leaveEntries = collect();
            foreach ($approvedLeaves as $leave) {
                $start = Carbon::parse($leave->start_date);
                $end = $leave->end_date ? Carbon::parse($leave->end_date) : $start->copy();
                $period = new \Carbon\CarbonPeriod($start, $end);

                foreach ($period as $day) {
                    if ($day->lt($dateFrom) || $day->gt($dateTo)) {
                        continue;
                    }

                    $dateKey = $day->format('Y-m-d');
                    $existingDtr = $dtrMap[$leave->user_id][$dateKey] ?? null;

                    if ($leave->type === 'vacation_leave' || $leave->type === 'sick_leave') {
                        // Vacation/Sick Leave: automatically record 08:00
                        if (!$existingDtr) {
                            // Persist so it consistently appears in DTR listings/exports
                            $entry = Dtr::firstOrCreate(
                                [
                                    'user_id' => $leave->user_id,
                                    'date' => $day->copy(),
                                ],
                                [
                                    'total_hours' => 8.0,
                                    'overtime_hours' => 0, // Will be calculated from total_hours
                                    'status' => 'on_leave',
                                    'remarks' => 'Approved Leave: ' . ($leave->type_label ?? ucfirst(str_replace('_', ' ', $leave->type))),
                                ]
                            );
                            $entry->leave_type_label = $leave->type_label ?? ucfirst(str_replace('_', ' ', $leave->type));
                            $entry->setRelation('user', $leave->user);
                            $leaveEntries->push($entry);
                        }
                    } elseif ($leave->type === 'absent') {
                        // Absent leave: should not add 8 hours, keep 0 hours and mark as absent.
                        if (!$existingDtr) {
                            $entry = Dtr::firstOrCreate(
                                [
                                    'user_id' => $leave->user_id,
                                    'date' => $day->copy(),
                                ],
                                [
                                    'total_hours' => 0,
                                    'overtime_hours' => 0,
                                    'status' => 'absent',
                                    'remarks' => 'Approved Leave: Absent',
                                ]
                            );
                            $entry->leave_type_label = 'Absent';
                            $entry->setRelation('user', $leave->user);
                            $leaveEntries->push($entry);
                        }
                    } elseif ($leave->type === 'offset') {
                        // Offset: parse total hours from reason field and divide by days
                        $raw = $leave->reason ?? '';
                        $totalOffsetHours = 8.0; // Default: 1 day = 8 hours

                        if (preg_match('/Hours to Deduct:\s*([0-9]{2}):([0-9]{2})/', $raw, $m)) {
                            $totalOffsetHours = (int)$m[1] + ((int)$m[2] / 60); // Convert to decimal hours
                        }

                        // Calculate hours per day (distribute total offset hours across all days)
                        $days = $start->diffInDays($end) + 1;
                        $offsetHoursPerDay = $days > 0 ? $totalOffsetHours / $days : $totalOffsetHours;

                        if ($existingDtr) {
                            // If DTR exists, add offset hours to existing total_hours
                            $existingDtr->total_hours = ($existingDtr->total_hours ?? 0) + $offsetHoursPerDay;
                            // Update remarks
                            $existingRemarks = $existingDtr->remarks ?: '';
                            $offsetLabel = 'Offset: ' . ($leave->type_label ?? 'Offset');
                            if ($existingRemarks) {
                                $existingDtr->remarks = $existingRemarks . ' | ' . $offsetLabel;
                            } else {
                                $existingDtr->remarks = $offsetLabel;
                            }
                        } else {
                            // Persist so it consistently appears in DTR listings/exports
                            $entry = Dtr::firstOrCreate(
                                [
                                    'user_id' => $leave->user_id,
                                    'date' => $day->copy(),
                                ],
                                [
                                    'total_hours' => $offsetHoursPerDay,
                                    'overtime_hours' => 0, // Will be calculated from total_hours
                                    'status' => 'on_leave',
                                    'remarks' => 'Offset: ' . ($leave->type_label ?? 'Offset'),
                                ]
                            );
                            $entry->leave_type_label = $leave->type_label ?? 'Offset';
                            $entry->setRelation('user', $leave->user);
                            $leaveEntries->push($entry);
                        }
                    } elseif ($leave->type === 'travel') {
                        // Travel leave: DTR records are already created when approved, so just ensure they're included
                        // If DTR doesn't exist (shouldn't happen, but fallback), create it
                        if (!$existingDtr) {
                            // This shouldn't happen since travel creates DTR on approval, but fallback
                            $entry = Dtr::firstOrCreate(
                                [
                                    'user_id' => $leave->user_id,
                                    'date' => $day->copy(),
                                ],
                                [
                                    'total_hours' => 8.0, // Default, but should use actual hours from DTR
                                    'overtime_hours' => 0,
                                    'status' => 'travel',
                                    'remarks' => 'Approved Travel Leave',
                                ]
                            );
                            $entry->leave_type_label = 'Travel';
                            $entry->setRelation('user', $leave->user);
                            $leaveEntries->push($entry);
                        } else {
                            // DTR exists - ensure it's marked as travel if it's a travel leave
                            if ($existingDtr->status !== 'travel') {
                                $existingDtr->status = 'travel';
                            }
                        }
                    } else {
                        // Other leave types: create entry with 08:00 hours
                        if (!$existingDtr) {
                            $entry = Dtr::firstOrCreate(
                                [
                                    'user_id' => $leave->user_id,
                                    'date' => $day->copy(),
                                ],
                                [
                                    'total_hours' => 8.0,
                                    'overtime_hours' => 0, // Will be calculated from total_hours
                                    'status' => 'on_leave',
                                    'remarks' => 'Approved Leave: ' . ($leave->type_label ?? ucfirst(str_replace('_', ' ', $leave->type))),
                                ]
                            );
                            $entry->leave_type_label = $leave->type_label ?? ucfirst(str_replace('_', ' ', $leave->type));
                            $entry->setRelation('user', $leave->user);
                            $leaveEntries->push($entry);
                        }
                    }
                }
            }

            if ($leaveEntries->isNotEmpty()) {
                $dtrs = $dtrs->merge($leaveEntries)->sortBy([
                    ['date', 'desc'],
                    ['user_id', 'asc'],
                ])->values();
            }
        }

        // Fill missing weekday entries per employee so each week clearly shows:
        // - ABSENT if employee has no DTR on a date where at least one employee has DTR data
        // - HOLIDAY if no employee has DTR data on that date
        if ($dateFrom && $dateTo && !empty($employees) && count($employees) > 0) {
            // Use all employees in current scope (filters/department restrictions),
            // not only employees who already have DTR rows.
            $employeeMap = [];
            foreach ($employees as $employee) {
                $employeeMap[$employee->id] = $employee;
            }

            $existingMap = [];
            $dateHasAnyData = [];
            foreach ($dtrs as $dtr) {
                $dateKey = $dtr->date->format('Y-m-d');
                $existingMap[$dtr->user_id][$dateKey] = true;
                $dateHasAnyData[$dateKey] = true;
            }

            $syntheticEntries = collect();
            $period = CarbonPeriod::create($dateFrom->copy()->startOfDay(), $dateTo->copy()->startOfDay());

            foreach ($period as $day) {
                // Weekdays only for work-week DTR auto-labeling
                if ($day->isWeekend()) {
                    continue;
                }

                $dateKey = $day->format('Y-m-d');
                $isHoliday = !isset($dateHasAnyData[$dateKey]);

                foreach ($employeeMap as $employeeId => $employeeModel) {
                    if (isset($existingMap[$employeeId][$dateKey])) {
                        continue;
                    }

                    $entry = new Dtr([
                        'user_id' => $employeeId,
                        'date' => $day->copy(),
                        'total_hours' => 0,
                        'overtime_hours' => 0,
                        'status' => $isHoliday ? 'holiday' : 'absent',
                        'remarks' => $isHoliday
                            ? 'Auto-labeled holiday (no employee has DTR data for this date).'
                            : 'Auto-labeled absent (no DTR entry for this employee on this date).',
                    ]);
                    $entry->setRelation('user', $employeeModel);
                    $syntheticEntries->push($entry);
                }
            }

            if ($syntheticEntries->isNotEmpty()) {
                $dtrs = $dtrs->merge($syntheticEntries)->sortBy([
                    ['date', 'desc'],
                    ['user_id', 'asc'],
                ])->values();
            }
        }

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
        $user = auth()->user();

        $employeesQuery = User::where('role', 'employee')
            ->where('is_active', true);

        // Apply department restrictions if user has Employee Management with restrictions
        if ($user->canAccessEmployeeManagement()) {
            $allowedDepartmentIds = $user->getAllowedDepartmentIds();
            if ($allowedDepartmentIds !== null) {
                $employeesQuery->whereIn('department_id', $allowedDepartmentIds);
            }
        }

        $employees = $employeesQuery->orderBy('name')->get();

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
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'added_time_from_note' => 'nullable|date_format:H:i',
            'total_hours' => 'required|date_format:H:i',
            // Overtime is auto-computed as (Total Hours - 8:00) when Total Hours > 8:00
            'overtime_hours' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,absent,late,half_day,on_leave,travel',
            'is_travel' => 'nullable|boolean',
            'remarks' => 'nullable|string|max:1000',
        ]);

        // Handle travel checkbox - override status if is_travel is checked
        $status = $request->status;
        if ($request->boolean('is_travel')) {
            $status = 'travel';
        }

        // Convert HH:MM inputs to decimal hours (same for all employees and dates)
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

        // Generate date range (excluding weekends)
        $dateFrom = Carbon::parse($request->date_from);
        $dateTo = Carbon::parse($request->date_to);
        $dateRange = [];
        $currentDate = $dateFrom->copy();
        
        while ($currentDate->lte($dateTo)) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if (!$currentDate->isWeekend()) {
                $dateRange[] = $currentDate->copy();
            }
            $currentDate->addDay();
        }

        if (empty($dateRange)) {
            return redirect()->back()
                ->withErrors(['date_from' => 'The selected date range contains only weekends. Please select a range that includes weekdays.'])
                ->withInput();
        }

        // Process bulk creation for selected employees and date range
        $userIds = $request->user_ids;
        $created = 0;
        $skipped = [];
        $errors = [];
        $today = Carbon::today();

        foreach ($userIds as $userId) {
            // Verify user is an employee
            $employee = User::where('id', $userId)
                ->where('role', 'employee')
                ->first();

            if (!$employee) {
                $errors[] = "User ID {$userId} is not an employee.";
                continue;
            }

            foreach ($dateRange as $date) {
                // Prevent setting 'absent' status for future dates
                $dateStatus = $status;
                if ($date->gt($today) && $status === 'absent') {
                    $dateStatus = 'present'; // Change absent to present for future dates
                }

                // Check if record already exists for this date
                $existingDtr = Dtr::where('user_id', $userId)
                    ->whereDate('date', $date->toDateString())
                    ->first();

                try {
                    if ($existingDtr) {
                        // If DTR record already exists, add the new hours to existing total
                        $existingTotal = (float) ($existingDtr->total_hours ?? 0);
                        $existingAdded = (float) ($existingDtr->added_time_from_note ?? 0);
                        
                        // Add new worked hours and added time to existing values
                        // New total = existing total + new worked hours + new added time
                        $newTotal = $existingTotal + $workedDecimal + $addedDecimal;
                        $newAdded = $existingAdded + $addedDecimal;
                        
                        // Recalculate overtime based on new total
                        $newOvertime = max($newTotal - $standardDecimal, 0);
                        
                        // Update remarks - append new remark if provided
                        $existingRemarks = $existingDtr->remarks ?? '';
                        $newRemarks = $existingRemarks;
                        if (!empty($request->remarks)) {
                            if (!empty($existingRemarks)) {
                                $newRemarks = $existingRemarks . '; ' . $request->remarks;
                            } else {
                                $newRemarks = $request->remarks;
                            }
                        }
                        
                        // Update status only if it's not travel and we're not setting travel
                        // If existing is travel, keep it as travel
                        $finalStatus = $existingDtr->status === 'travel' ? 'travel' : $dateStatus;
                        
                        $existingDtr->update([
                            'added_time_from_note' => $newAdded,
                            'total_hours' => $newTotal,
                            'overtime_hours' => $newOvertime,
                            'status' => $finalStatus,
                            'remarks' => $newRemarks,
                        ]);

                        // Calculate and store weekly deficit for this employee
                        $this->calculateAndStoreWeeklyDeficit($userId, $date);

                        $created++;
                    } else {
                        // Create new DTR record
                        Dtr::create([
                            'user_id' => $userId,
                            'date' => $date->toDateString(),
                            'added_time_from_note' => $addedDecimal,
                            'total_hours' => $totalDecimal,
                            'overtime_hours' => $overtimeDecimal,
                            'status' => $dateStatus,
                            'remarks' => $request->remarks,
                        ]);

                        // Calculate and store weekly deficit for this employee
                        $this->calculateAndStoreWeeklyDeficit($userId, $date);

                        $created++;
                    }
                } catch (\Exception $e) {
                    Log::error("DTR creation/update failed for user {$userId} on {$date->format('Y-m-d')}: " . $e->getMessage());
                    $errors[] = "Failed to create/update DTR for {$employee->name} on {$date->format('Y-m-d')}: " . $e->getMessage();
                }
            }
        }

        // Build success/error messages
        $messages = [];
        if ($created > 0) {
            $messages[] = "Successfully processed {$created} DTR record(s) for " . count($dateRange) . " date(s).";
        }
        if (count($errors) > 0) {
            $messages[] = "Errors: " . implode(' ', array_slice($errors, 0, 3)) . (count($errors) > 3 ? '...' : '');
        }

        if ($created > 0) {
            return redirect('/admin/dtr')
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

            return redirect('/admin/dtr')
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

            return redirect('/admin/dtr')
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

            return redirect('/admin/dtr')
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

                    // Parse Added Time From Note using SmartTimeParser
                    // Supports: 1h, 1hr, 1h30m, 2h 15m, 45m, 1:30, 1.5 hours, half hour,
                    // one hr three mins, about 30 mins, around 2 hrs, 90 minutes, etc.
                    $parsedFromAddedColumn = SmartTimeParser::parse($addedTimeFromNote);
                    $parsedFromRemarks = 0.0;

                    // Also add any time mentioned in remarks to Added Time From Note
                    if ($remarks !== '') {
                        $parsedFromRemarks = SmartTimeParser::parse($remarks);
                    }

                    $addedTimeFromNoteValue = $parsedFromAddedColumn + $parsedFromRemarks;

                    // Build remarks - include the original Added Time From Note value for visibility
                    $remarksParts = [];

                    // Add original Added Time From Note value if provided
                    if ($addedTimeFromNote !== '' && $addedTimeFromNote !== '00:00' && $addedTimeFromNote !== '0') {
                        $remarksParts[] = $addedTimeFromNote;
                    }

                    // Add original remarks if provided
                    if ($remarks !== '') {
                        $remarksParts[] = $remarks;
                    }

                    $finalRemarks = implode(' | ', $remarksParts);

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
                        'remarks' => $finalRemarks,
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

            $redirectUrl = $importType === 'student' ? '/admin/student-dtr' : '/admin/dtr';

            return redirect($redirectUrl)
                ->with('success', $message)
                ->with('import_errors', $errors);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DTR import failed: ' . $e->getMessage());

            $redirectUrl = $request->input('type') === 'student' ? '/admin/student-dtr' : '/admin/dtr';

            return redirect($redirectUrl)
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
        // Check if user has student_management permission or is admin
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->canAccessStudentManagement()) {
            abort(403, 'Access denied. You do not have permission to access Student Management.');
        }

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

        // Filter by student search (name, email, or school/university)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('user', function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('university', function($universityQuery) use ($searchTerm) {
                      $universityQuery->where('name', 'like', '%' . $searchTerm . '%')
                                      ->orWhere('location', 'like', '%' . $searchTerm . '%');
                  });
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

        $allStudents = $studentsQuery->orderBy('name')->get();

        // Calculate remaining hours for each student and filter to only those with remaining time needed
        // BUT: If search is provided, show all matching students regardless of remaining time
        $studentsWithRemainingTime = [];
        $searchProvided = $request->filled('search');
        
        foreach ($allStudents as $student) {
            $requiredHours = (float) ($student->required_training_hours ?? 0);
            $totalDtrHours = (float) Dtr::where('user_id', $student->id)->sum('total_hours');
            $remainingHours = $requiredHours - $totalDtrHours;
            
            // Only include students with remaining time needed (remaining > 0)
            // OR if search is provided, include all students (they'll be filtered by search query)
            if ($remainingHours > 0 || $searchProvided) {
                $studentsWithRemainingTime[] = $student->id;
            }
        }

        // Filter DTR query to only include students with remaining time needed
        // UNLESS search is provided, then show all matching students
        if (!empty($studentsWithRemainingTime)) {
            if (!$searchProvided) {
                // Only filter by remaining time if no search is provided
                $query->whereIn('user_id', $studentsWithRemainingTime);
            }
            // If search is provided, the search filter in the query already handles it
        } else {
            // If no students have remaining time and no search, return empty result
            if (!$searchProvided) {
                $query->whereRaw('1 = 0'); // Force empty result
            }
        }

        // Filter students list to only those with remaining time (or all if search provided)
        $students = $allStudents->filter(function($student) use ($studentsWithRemainingTime, $searchProvided) {
            if ($searchProvided) {
                // If search is provided, show all students (they'll be filtered by the search query)
                return true;
            }
            return in_array($student->id, $studentsWithRemainingTime);
        })->values();

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
        // Check if user has student_management permission or is admin
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->canAccessStudentManagement()) {
            abort(403, 'Access denied. You do not have permission to access Student Management.');
        }

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
        // Check if user has student_management permission or is admin
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->canAccessStudentManagement()) {
            abort(403, 'Access denied. You do not have permission to perform this action.');
        }

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

            return redirect('/admin/student-dtr')
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
        // Check if user has student_management permission or is admin
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->canAccessStudentManagement()) {
            abort(403, 'Access denied. You do not have permission to perform this action.');
        }
        // Verify this is a student DTR
        if (!$dtr->user || $dtr->user->role !== 'student') {
            abort(404, 'DTR record not found for students.');
        }

        $students = User::where('role', 'student')
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

        // Determine if sections should be collapsed by default
        $collapseByDefault = true;

        return view('admin.dtr.student-edit', compact('dtr', 'students', 'collapseByDefault', 'workedFormatted', 'addedFormatted'));
    }

    /**
     * Update the specified student DTR resource in storage.
     */
    public function studentUpdate(Request $request, Dtr $dtr)
    {
        // Check if user has student_management permission or is admin
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->canAccessStudentManagement()) {
            abort(403, 'Access denied. You do not have permission to perform this action.');
        }

        // Log the incoming request
        Log::info('Student DTR Update Request', [
            'dtr_id' => $dtr->id,
            'method' => $request->method(),
            'route' => $request->route()->getName(),
            'user_id' => $request->user_id,
            'date' => $request->date,
        ]);

        // Verify this is a student DTR
        if (!$dtr->user || $dtr->user->role !== 'student') {
            abort(404, 'DTR record not found for students.');
        }

        // Store original values for rollback if needed
        $originalData = [
            'user_id' => $dtr->user_id,
            'date' => $dtr->date->format('Y-m-d'),
            'total_hours' => $dtr->total_hours,
            'added_time_from_note' => $dtr->added_time_from_note,
            'overtime_hours' => $dtr->overtime_hours,
            'status' => $dtr->status,
            'remarks' => $dtr->remarks,
        ];

        // Verify the record exists before we start
        if (!Dtr::where('id', $dtr->id)->exists()) {
            Log::error('DTR record does not exist at start of update!', ['dtr_id' => $dtr->id]);
            abort(404, 'DTR record not found.');
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

        // Store DTR ID before transaction
        $dtrId = $dtr->id;

        // Use database transaction to ensure atomicity
        try {
            return \DB::transaction(function () use ($request, $dtr, $dtrId, $originalData) {
            try {
            // First, check if updating date or user_id would create a duplicate
            // (there's a unique constraint on user_id + date)
            if ($request->user_id != $dtr->user_id || $request->date != $dtr->date->format('Y-m-d')) {
                $existingDtr = Dtr::where('user_id', $request->user_id)
                    ->whereDate('date', $request->date)
                    ->where('id', '!=', $dtrId)
                    ->lockForUpdate() // Lock the row to prevent race conditions
                    ->first();
                
                if ($existingDtr) {
                    return redirect()->back()
                        ->withErrors(['date' => 'A DTR record already exists for this student on this date.'])
                        ->withInput();
                }
            }

            // Convert HH:MM inputs to decimal hours
            $workedDecimal = 0;
            $addedDecimal = 0;

            // total_hours field in form is actually "Worked Hours" (base hours)
            if ($request->filled('total_hours')) {
                [$h, $m] = explode(':', $request->total_hours);
                $workedDecimal = ((int) $h) + ((int) $m / 60);
            } else {
                // If not provided, keep current worked hours
                $workedDecimal = max(($dtr->total_hours ?? 0) - ($dtr->added_time_from_note ?? 0), 0);
            }

            // Added time from note
            if ($request->filled('added_time_from_note')) {
                [$eh, $em] = explode(':', $request->added_time_from_note);
                $addedDecimal = ((int) $eh) + ((int) $em / 60);
            } else {
                // If not provided, keep current added time
                $addedDecimal = $dtr->added_time_from_note ?? 0;
            }

            // Total hours = Worked Hours + Added Time
            $totalDecimal = $workedDecimal + $addedDecimal;

            // Overtime is any hours beyond the standard 8:00
            $standardDecimal = 8.0;
            $overtimeDecimal = $totalDecimal > $standardDecimal
                ? $totalDecimal - $standardDecimal
                : 0;

            // Build update data - explicitly preserve all existing fields
            // Only update the fields we want to change
            $updateData = [
                'user_id' => $request->user_id,
                'date' => $request->date,
                'added_time_from_note' => $addedDecimal,
                'total_hours' => $totalDecimal,
                'overtime_hours' => $overtimeDecimal,
                'status' => $request->status,
            ];

            // Handle remarks - preserve existing if not provided or empty
            if ($request->has('remarks')) {
                $remarksValue = trim($request->remarks ?? '');
                // Only update if value is provided, otherwise preserve existing
                $updateData['remarks'] = $remarksValue !== '' ? $remarksValue : ($dtr->remarks ?? null);
            } else {
                // Field not in request, preserve existing
                $updateData['remarks'] = $dtr->remarks;
            }

            // Log before update for debugging
            Log::info('Updating student DTR', [
                'dtr_id' => $dtr->id,
                'update_data' => $updateData,
                'existing_data' => [
                    'user_id' => $dtr->user_id,
                    'date' => $dtr->date->format('Y-m-d'),
                    'total_hours' => $dtr->total_hours,
                    'added_time_from_note' => $dtr->added_time_from_note,
                    'status' => $dtr->status,
                    'remarks' => $dtr->remarks,
                ]
            ]);

            // IMPORTANT: Use update() method which only updates specified fields
            // This preserves all other fields (time_in, time_out, break_start, break_end, etc.)
            // Lock the record for update to prevent concurrent modifications
            $lockedDtr = Dtr::where('id', $dtrId)->lockForUpdate()->first();
            
            if (!$lockedDtr) {
                Log::error('DTR record does not exist before update!', [
                    'dtr_id' => $dtrId
                ]);
                throw new \Exception('DTR record not found. It may have been deleted.');
            }

            // Verify this is still a student DTR
            if (!$lockedDtr->user || $lockedDtr->user->role !== 'student') {
                throw new \Exception('DTR record does not belong to a student.');
            }

            // Perform the update using query builder to ensure we're updating the correct record
            // IMPORTANT: Use update() which only updates specified columns, preserving others
            Log::info('About to update DTR record', [
                'dtr_id' => $dtrId,
                'update_data' => $updateData,
            ]);
            
            $updated = Dtr::where('id', $dtrId)->update($updateData);
            
            Log::info('DTR update executed', [
                'dtr_id' => $dtrId,
                'updated' => $updated,
                'rows_affected' => $updated,
            ]);
            
            if (!$updated) {
                Log::error('DTR update returned false', [
                    'dtr_id' => $dtrId,
                    'update_data' => $updateData,
                    'original_data' => $originalData
                ]);
                throw new \Exception('Failed to update DTR record. No rows were affected.');
            }

            // Verify the record still exists after update
            $updatedDtr = Dtr::find($dtrId);
            if (!$updatedDtr) {
                Log::error('DTR record disappeared after update!', [
                    'dtr_id' => $dtrId,
                    'update_data' => $updateData,
                    'original_data' => $originalData,
                    'record_exists' => Dtr::where('id', $dtrId)->exists(),
                ]);
                throw new \Exception('DTR record was deleted during update. This should not happen.');
            }

            Log::info('DTR record verified after update', [
                'dtr_id' => $dtrId,
                'user_id' => $updatedDtr->user_id,
                'date' => $updatedDtr->date->format('Y-m-d'),
                'total_hours' => $updatedDtr->total_hours,
            ]);

            // Calculate and store weekly deficit
            $this->calculateAndStoreWeeklyDeficit($request->user_id, Carbon::parse($request->date));

            Log::info('Student DTR updated successfully', [
                'dtr_id' => $dtrId,
                'updated_data' => [
                    'user_id' => $updatedDtr->user_id,
                    'date' => $updatedDtr->date->format('Y-m-d'),
                    'total_hours' => $updatedDtr->total_hours,
                    'added_time_from_note' => $updatedDtr->added_time_from_note,
                    'status' => $updatedDtr->status,
                ]
            ]);

                return redirect('/admin/student-dtr')
                    ->with('success', 'Student DTR record updated successfully.');
            } catch (\Exception $e) {
                // Rollback is automatic in transaction
                Log::error('Student DTR update exception (inside transaction): ' . $e->getMessage(), [
                    'dtr_id' => $dtrId ?? null,
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e; // Re-throw to let outer catch handle it
            }
            }, 5); // 5 attempts for deadlock retry
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Student DTR update database error: ' . $e->getMessage(), [
                'dtr_id' => $dtr->id ?? null,
                'sql' => $e->getSql() ?? null,
                'bindings' => $e->getBindings() ?? null,
            ]);

            // Check if it's a unique constraint violation
            if (str_contains($e->getMessage(), 'UNIQUE constraint') || str_contains($e->getMessage(), 'Duplicate entry')) {
                return redirect()->back()
                    ->withErrors(['date' => 'A DTR record already exists for this student on this date.'])
                    ->withInput();
            }

            return redirect()->back()
                ->withErrors(['error' => 'Database error: ' . $e->getMessage()])
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Student DTR update failed: ' . $e->getMessage(), [
                'dtr_id' => $dtr->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to update student DTR record: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified student DTR resource from storage.
     * Only full-access admins (super admins) can perform this action.
     */
    public function studentDestroy(Dtr $dtr)
    {
        // Check if user has student_management permission or is admin
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->canAccessStudentManagement()) {
            abort(403, 'Access denied. You do not have permission to perform this action.');
        }
        // Ensure the record belongs to a student
        if (!$dtr->user || $dtr->user->role !== 'student') {
            abort(404, 'DTR record not found for students.');
        }

        $currentUser = auth()->user();

        // Only super admins (full access) can delete student DTR records
        if (!$currentUser || !$currentUser->isSuperAdmin()) {
            abort(403, 'Only full-access admins can delete student DTR records.');
        }

        try {
            $userId = $dtr->user_id;
            $date = $dtr->date;

            $dtr->delete();

            // Recalculate weekly deficit after deletion
            $this->calculateAndStoreWeeklyDeficit($userId, Carbon::parse($date));

            return redirect('/admin/student-dtr')
                ->with('success', 'Student DTR record deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Student DTR deletion failed: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete student DTR record: ' . $e->getMessage()]);
        }
    }

    /**
     * Bulk update student DTR records.
     */
    public function studentBulkUpdate(Request $request)
    {
        // Check if user has student_management permission or is admin
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->canAccessStudentManagement()) {
            abort(403, 'Access denied. You do not have permission to perform this action.');
        }
        $request->validate([
            'dtr_ids' => 'required|array',
            'dtr_ids.*' => 'required|integer|exists:dtrs,id',
            'worked_hours.*' => 'nullable|date_format:H:i',
            'total_hours.*' => 'nullable|date_format:H:i',
            'added_time_from_note.*' => 'nullable|date_format:H:i',
            'status.*' => 'nullable|in:present,absent,late,half_day,on_leave,travel',
            'remarks.*' => 'nullable|string|max:1000',
        ]);

        $dtrIds = $request->dtr_ids;

        if (empty($dtrIds)) {
            return redirect()->back()
                ->withErrors(['error' => 'No records selected.']);
        }

        // Get all DTR records and verify they belong to students
        $dtrs = Dtr::whereIn('id', $dtrIds)
            ->whereHas('user', function($q) {
                $q->where('role', 'student');
            })
            ->get();

        if ($dtrs->isEmpty()) {
            return redirect()->back()
                ->withErrors(['error' => 'No valid student DTR records found.']);
        }

        $updatedCount = 0;
        $affectedUsers = [];

        try {
            foreach ($dtrs as $dtr) {
                $updateData = [];
                $needsRecalculation = false;
                $dtrId = $dtr->id;

                // Get worked hours and added time for this specific record
                $workedDecimal = null;
                $addedDecimal = null;

                // Update worked hours if provided for this specific record
                if ($request->has("worked_hours.{$dtrId}") && $request->filled("worked_hours.{$dtrId}")) {
                    $workedHoursInput = $request->input("worked_hours.{$dtrId}");
                    [$h, $m] = explode(':', $workedHoursInput);
                    $workedDecimal = ((int) $h) + ((int) $m / 60);
                } else {
                    // Keep current worked hours
                    $workedDecimal = max(($dtr->total_hours ?? 0) - ($dtr->added_time_from_note ?? 0), 0);
                }

                // Update added time if provided for this specific record
                if ($request->has("added_time_from_note.{$dtrId}") && $request->filled("added_time_from_note.{$dtrId}")) {
                    $addedTimeInput = $request->input("added_time_from_note.{$dtrId}");
                    [$eh, $em] = explode(':', $addedTimeInput);
                    $addedDecimal = ((int) $eh) + ((int) $em / 60);
                } else {
                    // Keep current added time
                    $addedDecimal = $dtr->added_time_from_note ?? 0;
                }

                // Calculate total hours
                $totalDecimal = $workedDecimal + $addedDecimal;
                
                // Build update data - only include fields we want to change
                $updateData = [
                    'added_time_from_note' => $addedDecimal,
                    'total_hours' => $totalDecimal,
                ];
                
                // Calculate overtime
                $standardDecimal = 8.0;
                $updateData['overtime_hours'] = $totalDecimal > $standardDecimal
                    ? $totalDecimal - $standardDecimal
                    : 0;
                
                $needsRecalculation = true;

                // Update status if provided for this specific record (only if not empty)
                if ($request->has("status.{$dtrId}") && $request->filled("status.{$dtrId}")) {
                    $updateData['status'] = $request->input("status.{$dtrId}");
                }
                // If status not provided, don't include it - preserve existing

                // Update remarks if provided for this specific record
                if ($request->has("remarks.{$dtrId}")) {
                    $remarksValue = trim($request->input("remarks.{$dtrId}", ''));
                    // Only update if value is provided, otherwise preserve existing
                    $updateData['remarks'] = $remarksValue !== '' ? $remarksValue : ($dtr->remarks ?? null);
                }
                // If remarks not provided, don't include it - preserve existing

                // Use fill() and save() to ensure only specified fields are updated
                // This preserves time_in, time_out, break_start, break_end, and other fields
                $dtr->fill($updateData);
                $dtr->save();
                
                $updatedCount++;
                
                if ($needsRecalculation) {
                    $affectedUsers[$dtr->user_id] = $dtr->date;
                }
            }

            // Recalculate weekly deficits for affected users
            foreach ($affectedUsers as $userId => $date) {
                $this->calculateAndStoreWeeklyDeficit($userId, Carbon::parse($date));
            }

            return redirect('/admin/student-dtr')
                ->with('success', "Successfully updated {$updatedCount} record(s).");
        } catch (\Exception $e) {
            Log::error('Bulk update failed: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to update records: ' . $e->getMessage()]);
        }
    }

    /**
     * Bulk delete student DTR records.
     */
    public function studentBulkDelete(Request $request)
    {
        // Check if user has student_management permission or is admin
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->canAccessStudentManagement()) {
            abort(403, 'Access denied. You do not have permission to perform this action.');
        }
        $currentUser = auth()->user();

        // Only super admins (full access) can delete student DTR records
        if (!$currentUser || !$currentUser->isSuperAdmin()) {
            abort(403, 'Only full-access admins can delete student DTR records.');
        }

        $request->validate([
            'dtr_ids' => 'required|array',
            'dtr_ids.*' => 'required|integer|exists:dtrs,id',
        ]);

        // Get all DTR records and verify they belong to students
        $dtrs = Dtr::whereIn('id', $request->dtr_ids)
            ->whereHas('user', function($q) {
                $q->where('role', 'student');
            })
            ->get();

        if ($dtrs->isEmpty()) {
            return redirect()->back()
                ->withErrors(['error' => 'No valid student DTR records found.']);
        }

        $deletedCount = 0;
        $affectedUsers = [];

        try {
            foreach ($dtrs as $dtr) {
                $userId = $dtr->user_id;
                $date = $dtr->date;
                
                $dtr->delete();
                $deletedCount++;
                
                $affectedUsers[$userId] = $date;
            }

            // Recalculate weekly deficits for affected users
            foreach ($affectedUsers as $userId => $date) {
                $this->calculateAndStoreWeeklyDeficit($userId, Carbon::parse($date));
            }

            return redirect('/admin/student-dtr')
                ->with('success', "Successfully deleted {$deletedCount} record(s).");
        } catch (\Exception $e) {
            Log::error('Bulk delete failed: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete records: ' . $e->getMessage()]);
        }
    }

    /**
     * Export student DTR records as PDF.
     */
    public function studentExportPdf(Request $request)
    {
        // Check if user has student_management permission or is admin
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->canAccessStudentManagement()) {
            abort(403, 'Access denied. You do not have permission to perform this action.');
        }
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

        // Approved overtime from leave requests (only count approved overtime requests)
        $parsedDateFrom = $dateFrom ? Carbon::parse($dateFrom) : ($dtrs->min('date') ? $dtrs->min('date')->copy() : null);
        $parsedDateTo = $dateTo ? Carbon::parse($dateTo) : ($dtrs->max('date') ? $dtrs->max('date')->copy() : null);
        if ($parsedDateFrom && $parsedDateTo && $parsedDateFrom->gt($parsedDateTo)) {
            [$parsedDateFrom, $parsedDateTo] = [$parsedDateTo, $parsedDateFrom];
        }

        $overtimeMap = [];
        if ($parsedDateFrom && $parsedDateTo) {
            $overtimeRequests = LeaveRequest::where('type', 'overtime')
                ->where('status', 'approved')
                ->whereHas('user', function ($q) {
                    $q->where('role', 'employee');
                })
                ->whereDate('start_date', '<=', $parsedDateTo->toDateString())
                ->where(function ($q) use ($parsedDateFrom) {
                    $q->whereNull('end_date')
                      ->orWhereDate('end_date', '>=', $parsedDateFrom->toDateString());
                })
                ->get();

            foreach ($overtimeRequests as $ot) {
                $start = Carbon::parse($ot->start_date);
                $end = $ot->end_date ? Carbon::parse($ot->end_date) : $start->copy();
                $days = $start->diffInDays($end) + 1;
                $totalHours = (float) ($ot->overtime_hours ?? 0);
                $hoursPerDay = $days > 0 ? $totalHours / $days : $totalHours;

                $current = $start->copy();
                while ($current <= $end) {
                    $dateKey = $current->toDateString();
                    $overtimeMap[$ot->user_id][$dateKey] = ($overtimeMap[$ot->user_id][$dateKey] ?? 0) + $hoursPerDay;
                    $current->addDay();
                }
            }
        }

        // Override overtime_hours to only include approved overtime
        foreach ($dtrs as $dtr) {
            $dateKey = $dtr->date->toDateString();
            $dtr->overtime_hours = $overtimeMap[$dtr->user_id][$dateKey] ?? 0;
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
        $user = auth()->user();

        $query = Dtr::with('user')
            ->whereHas('user', function($q) {
                $q->where('role', 'employee');
            });

        // Apply department restrictions if user has Employee Management with restrictions
        if ($user->canAccessEmployeeManagement()) {
            $allowedDepartmentIds = $user->getAllowedDepartmentIds();
            if ($allowedDepartmentIds !== null) {
                $query->whereHas('user', function($q) use ($allowedDepartmentIds) {
                    $q->whereIn('department_id', $allowedDepartmentIds);
                });
            }
        }

        // Filter by department (user-selected filter)
        $selectedDepartment = null;
        if ($request->filled('department_id')) {
            $selectedDeptId = $request->department_id;
            if ($user->canManageDepartment($selectedDeptId)) {
                $selectedDepartment = Department::find($selectedDeptId);
                $query->whereHas('user', function($q) use ($selectedDeptId) {
                    $q->where('department_id', $selectedDeptId);
                });
            }
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
        $totalOvertimeFromCompletedWeeks = 0; // Only count overtime from approved leave requests
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
                        ];
                    }
                    $weeklyGroups[$weekKey]['total_hours'] += ($dtr->total_hours ?? 0);
                }
            }

            // Calculate deficit per week: max(0, 40 hours - weekly total)
            foreach ($weeklyGroups as $weekGroup) {
                $weeklyBaseHours = 40.0;
                $weeklyDeficit = max(0, $weeklyBaseHours - $weekGroup['total_hours']);
                $totalDeficitHours += $weeklyDeficit;
            }

            // Get approved overtime leave requests for the date range (overtime balance is ONLY from approved leave requests)
            $employeeIds = $dtrs->pluck('user_id')->unique();
            $approvedOvertimeRequests = LeaveRequest::whereIn('user_id', $employeeIds)
                ->where('type', 'overtime')
                ->where('status', 'approved')
                ->whereDate('start_date', '>=', $dateFrom)
                ->whereDate('start_date', '<=', $dateTo)
                ->whereDate('start_date', '<=', $today) // completed weeks only
                ->get();

            $overtimeFromLeavesMinutes = 0;
            foreach ($approvedOvertimeRequests as $otRequest) {
                $raw = $otRequest->reason ?? '';
                if (preg_match('/Total Overtime Hours:\s*([0-9]{2}:[0-9]{2})/', $raw, $m)) {
                    [$h, $mPart] = array_map('intval', explode(':', $m[1]));
                    $overtimeFromLeavesMinutes += $h * 60 + $mPart;
                }
            }

            $totalOvertimeFromCompletedWeeks = $overtimeFromLeavesMinutes / 60;
        } else {
            // If no date range, get all approved overtime leave requests
            $employeeIds = $dtrs->pluck('user_id')->unique();
            $today = Carbon::today();
            $approvedOvertimeRequests = LeaveRequest::whereIn('user_id', $employeeIds)
                ->where('type', 'overtime')
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $today) // completed weeks only
                ->get();

            $overtimeFromLeavesMinutes = 0;
            foreach ($approvedOvertimeRequests as $otRequest) {
                $raw = $otRequest->reason ?? '';
                if (preg_match('/Total Overtime Hours:\s*([0-9]{2}:[0-9]{2})/', $raw, $m)) {
                    [$h, $mPart] = array_map('intval', explode(':', $m[1]));
                    $overtimeFromLeavesMinutes += $h * 60 + $mPart;
                }
            }

            $totalOvertimeFromCompletedWeeks = $overtimeFromLeavesMinutes / 60;
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

        // Get approved leave requests (excluding overtime type) for the date range
        $leaveRequestMap = [];
        $travelRequestMap = [];
        if ($dateFrom && $dateTo) {
            $employeeIds = $dtrs->pluck('user_id')->unique();
            
            // Get all approved leave requests (excluding overtime type)
            $approvedLeaves = LeaveRequest::whereIn('user_id', $employeeIds)
                ->where('status', 'approved')
                ->where('type', '!=', 'overtime') // Exclude overtime type
                ->whereDate('start_date', '<=', $dateTo)
                ->where(function ($q) use ($dateFrom) {
                    $q->whereNull('end_date')
                      ->orWhereDate('end_date', '>=', $dateFrom);
                })
                ->get();

            foreach ($approvedLeaves as $leave) {
                $start = Carbon::parse($leave->start_date);
                $end = $leave->end_date ? Carbon::parse($leave->end_date) : $start->copy();
                $period = new \Carbon\CarbonPeriod($start, $end);
                foreach ($period as $day) {
                    $dateKey = $day->format('Y-m-d');
                    // Separate travel requests from other leave requests
                    if ($leave->type === 'travel') {
                        if (!isset($travelRequestMap[$leave->user_id][$dateKey])) {
                            $travelRequestMap[$leave->user_id][$dateKey] = $leave;
                        }
                    } else {
                        if (!isset($leaveRequestMap[$leave->user_id][$dateKey])) {
                            $leaveRequestMap[$leave->user_id][$dateKey] = $leave;
                        }
                    }
                }
            }
        } else {
            // If no date range, get all approved leave requests
            $employeeIds = $dtrs->pluck('user_id')->unique();
            $approvedLeaves = LeaveRequest::whereIn('user_id', $employeeIds)
                ->where('status', 'approved')
                ->where('type', '!=', 'overtime')
                ->get();

            foreach ($approvedLeaves as $leave) {
                $start = Carbon::parse($leave->start_date);
                $end = $leave->end_date ? Carbon::parse($leave->end_date) : $start->copy();
                $period = new \Carbon\CarbonPeriod($start, $end);
                foreach ($period as $day) {
                    $dateKey = $day->format('Y-m-d');
                    // Separate travel requests from other leave requests
                    if ($leave->type === 'travel') {
                        if (!isset($travelRequestMap[$leave->user_id][$dateKey])) {
                            $travelRequestMap[$leave->user_id][$dateKey] = $leave;
                        }
                    } else {
                        if (!isset($leaveRequestMap[$leave->user_id][$dateKey])) {
                            $leaveRequestMap[$leave->user_id][$dateKey] = $leave;
                        }
                    }
                }
            }
        }

        // Create a map of DTR records by employee and date for quick lookup
        $dtrMapByEmployeeAndDate = [];
        foreach ($dtrs as $dtr) {
            $employeeId = $dtr->user_id;
            $dateKey = $dtr->date->format('Y-m-d');
            if (!isset($dtrMapByEmployeeAndDate[$employeeId])) {
                $dtrMapByEmployeeAndDate[$employeeId] = [];
            }
            $dtrMapByEmployeeAndDate[$employeeId][$dateKey] = $dtr;
        }
        
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
            // Attach leave request and travel request info to each DTR record
            $dateKey = $dtr->date->format('Y-m-d');
            $dtr->leave_request = $leaveRequestMap[$employeeId][$dateKey] ?? null;
            $dtr->travel_request = $travelRequestMap[$employeeId][$dateKey] ?? null;

            $groupedByEmployee[$employeeId]['records'][] = $dtr;
            $groupedByEmployee[$employeeId]['total_hours'] += ($dtr->total_hours ?? 0);
            $groupedByEmployee[$employeeId]['total_overtime'] += ($dtr->overtime_hours ?? 0);
        }

        // Format employee totals and calculate per-employee deficit and balance
        foreach ($groupedByEmployee as &$group) {
            $employee = $group['employee'];
            $employeeId = $employee->id;

            // Add hours from approved leave records (without DTR entries or with 0 hours)
            $leaveHoursToAdd = 0;
            $leaveOvertimeToAdd = 0;
            
            if (isset($leaveRequestMap[$employeeId])) {
                foreach ($leaveRequestMap[$employeeId] as $dateKey => $leave) {
                    if ($leave->status !== 'approved') {
                        continue;
                    }
                    
                    // Check if DTR exists for this date
                    $hasDtr = false;
                    $dtrRecord = null;
                    if (isset($dtrMapByEmployeeAndDate[$employeeId][$dateKey])) {
                        $dtrRecord = $dtrMapByEmployeeAndDate[$employeeId][$dateKey];
                        $hasDtr = true;
                    } else {
                        // Also check in employeeGroup records
                        foreach ($group['records'] as $dtr) {
                            if ($dtr->date->format('Y-m-d') === $dateKey) {
                                $dtrRecord = $dtr;
                                $hasDtr = true;
                                break;
                            }
                        }
                    }
                    
                    // Add hours if no DTR exists OR if DTR exists but has 0 hours
                    if (!$hasDtr || ($dtrRecord && ($dtrRecord->total_hours ?? 0) == 0)) {
                        if ($dtrRecord && ($dtrRecord->total_hours ?? 0) > 0) {
                            // Use DTR hours if available
                            $leaveHoursToAdd += $dtrRecord->total_hours ?? 0;
                            $leaveOvertimeToAdd += $dtrRecord->overtime_hours ?? 0;
                        } else {
                            // Default to 8 hours for approved leave
                            $leaveHoursToAdd += 8.0;
                        }
                    }
                }
            }
            
            // Add hours from approved travel records (without DTR entries or with 0 hours)
            if (isset($travelRequestMap[$employeeId])) {
                foreach ($travelRequestMap[$employeeId] as $dateKey => $travel) {
                    if ($travel->status !== 'approved') {
                        continue;
                    }
                    
                    // Check if DTR exists for this date
                    $hasDtr = false;
                    $dtrRecord = null;
                    if (isset($dtrMapByEmployeeAndDate[$employeeId][$dateKey])) {
                        $dtrRecord = $dtrMapByEmployeeAndDate[$employeeId][$dateKey];
                        $hasDtr = true;
                    } else {
                        // Also check in employeeGroup records
                        foreach ($group['records'] as $dtr) {
                            if ($dtr->date->format('Y-m-d') === $dateKey) {
                                $dtrRecord = $dtr;
                                $hasDtr = true;
                                break;
                            }
                        }
                    }
                    
                    // Add hours if no DTR exists OR if DTR exists but has 0 hours
                    if (!$hasDtr || ($dtrRecord && ($dtrRecord->total_hours ?? 0) == 0)) {
                        if ($dtrRecord && ($dtrRecord->total_hours ?? 0) > 0) {
                            // Use DTR hours if available
                            $leaveHoursToAdd += $dtrRecord->total_hours ?? 0;
                            $leaveOvertimeToAdd += $dtrRecord->overtime_hours ?? 0;
                        } else {
                            // Default to 8 hours for approved travel
                            $leaveHoursToAdd += 8.0;
                        }
                    }
                }
            }
            
            // Add leave/travel hours to employee totals
            $group['total_hours'] += $leaveHoursToAdd;
            $group['total_overtime'] += $leaveOvertimeToAdd;

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
            $employeeOvertimeFromCompletedWeeks = 0; // Only count overtime from approved leave requests
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
                            ];
                        }
                        $weeklyGroups[$weekKey]['total_hours'] += ($dtr->total_hours ?? 0);
                    }
                }

                // Calculate deficit per week: max(0, 40 hours - weekly total)
                foreach ($weeklyGroups as $weekGroup) {
                    $weeklyBaseHours = 40.0;
                    $weeklyDeficit = max(0, $weeklyBaseHours - $weekGroup['total_hours']);
                    $employeeDeficitHours += $weeklyDeficit;
                }

                // Get approved overtime leave requests for this employee in the date range
                $approvedOvertimeRequests = LeaveRequest::where('user_id', $employee->id)
                    ->where('type', 'overtime')
                    ->where('status', 'approved')
                    ->whereDate('start_date', '>=', $dateFrom)
                    ->whereDate('start_date', '<=', $dateTo)
                    ->whereDate('start_date', '<=', $today) // completed weeks only
                    ->get();

                $overtimeFromLeavesMinutes = 0;
                foreach ($approvedOvertimeRequests as $otRequest) {
                    $raw = $otRequest->reason ?? '';
                    if (preg_match('/Total Overtime Hours:\s*([0-9]{2}:[0-9]{2})/', $raw, $m)) {
                        [$h, $mPart] = array_map('intval', explode(':', $m[1]));
                        $overtimeFromLeavesMinutes += $h * 60 + $mPart;
                    }
                }

                $employeeOvertimeFromCompletedWeeks = $overtimeFromLeavesMinutes / 60;
            } else {
                // If no date range, get all approved overtime leave requests for this employee
                $today = Carbon::today();
                $approvedOvertimeRequests = LeaveRequest::where('user_id', $employee->id)
                    ->where('type', 'overtime')
                    ->where('status', 'approved')
                    ->whereDate('start_date', '<=', $today) // completed weeks only
                    ->get();

                $overtimeFromLeavesMinutes = 0;
                foreach ($approvedOvertimeRequests as $otRequest) {
                    $raw = $otRequest->reason ?? '';
                    if (preg_match('/Total Overtime Hours:\s*([0-9]{2}:[0-9]{2})/', $raw, $m)) {
                        [$h, $mPart] = array_map('intval', explode(':', $m[1]));
                        $overtimeFromLeavesMinutes += $h * 60 + $mPart;
                    }
                }

                $employeeOvertimeFromCompletedWeeks = $overtimeFromLeavesMinutes / 60;
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

        // Recalculate overall totals to include leave/travel hours
        $totalHoursWithLeaves = $totalHours;
        $totalOvertimeWithLeaves = $totalOvertime;
        
        // Add hours from approved leave/travel records without DTR entries
        $employeeIds = $dtrs->pluck('user_id')->unique();
        foreach ($employeeIds as $employeeId) {
            // Add leave hours
            if (isset($leaveRequestMap[$employeeId])) {
                foreach ($leaveRequestMap[$employeeId] as $dateKey => $leave) {
                    if ($leave->status !== 'approved') {
                        continue;
                    }
                    
                    // Check if DTR exists
                    $hasDtr = isset($dtrMapByEmployeeAndDate[$employeeId][$dateKey]);
                    $dtrRecord = $hasDtr ? $dtrMapByEmployeeAndDate[$employeeId][$dateKey] : null;
                    
                    // Add if no DTR or DTR has 0 hours
                    if (!$hasDtr || ($dtrRecord && ($dtrRecord->total_hours ?? 0) == 0)) {
                        if ($dtrRecord && ($dtrRecord->total_hours ?? 0) > 0) {
                            $totalHoursWithLeaves += $dtrRecord->total_hours ?? 0;
                            $totalOvertimeWithLeaves += $dtrRecord->overtime_hours ?? 0;
                        } else {
                            $totalHoursWithLeaves += 8.0; // Default 8 hours for leave
                        }
                    }
                }
            }
            
            // Add travel hours
            if (isset($travelRequestMap[$employeeId])) {
                foreach ($travelRequestMap[$employeeId] as $dateKey => $travel) {
                    if ($travel->status !== 'approved') {
                        continue;
                    }
                    
                    // Check if DTR exists
                    $hasDtr = isset($dtrMapByEmployeeAndDate[$employeeId][$dateKey]);
                    $dtrRecord = $hasDtr ? $dtrMapByEmployeeAndDate[$employeeId][$dateKey] : null;
                    
                    // Add if no DTR or DTR has 0 hours
                    if (!$hasDtr || ($dtrRecord && ($dtrRecord->total_hours ?? 0) == 0)) {
                        if ($dtrRecord && ($dtrRecord->total_hours ?? 0) > 0) {
                            $totalHoursWithLeaves += $dtrRecord->total_hours ?? 0;
                            $totalOvertimeWithLeaves += $dtrRecord->overtime_hours ?? 0;
                        } else {
                            $totalHoursWithLeaves += 8.0; // Default 8 hours for travel
                        }
                    }
                }
            }
        }
        
        // Reformat totals with leave/travel hours included
        $totalMinutesWithLeaves = (int) round($totalHoursWithLeaves * 60);
        $totalHWithLeaves = intdiv($totalMinutesWithLeaves, 60);
        $totalMWithLeaves = $totalMinutesWithLeaves % 60;
        $totalHoursFormatted = sprintf('%02d:%02d', $totalHWithLeaves, $totalMWithLeaves);

        $totalOvertimeMinutesWithLeaves = (int) round($totalOvertimeWithLeaves * 60);
        $totalOvertimeHWithLeaves = intdiv($totalOvertimeMinutesWithLeaves, 60);
        $totalOvertimeMWithLeaves = $totalOvertimeMinutesWithLeaves % 60;
        $totalOvertimeFormatted = sprintf('%02d:%02d', $totalOvertimeHWithLeaves, $totalOvertimeMWithLeaves);

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
            'leaveRequestMap' => $leaveRequestMap,
            'travelRequestMap' => $travelRequestMap,
            'dtrMapByEmployeeAndDate' => $dtrMapByEmployeeAndDate,
        ];

        $pdf = Pdf::loadView('admin.dtr.export-pdf', $data)->setPaper('a4', 'landscape');

        $filename = 'employee_dtr_export_' . ($dateFrom ? Carbon::parse($dateFrom)->format('Y-m-d') : 'all') . '_' . ($dateTo ? Carbon::parse($dateTo)->format('Y-m-d') : 'all') . '.pdf';
        return $pdf->stream($filename);
    }
}
