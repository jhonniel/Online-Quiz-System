<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DtrTimeRequest;
use App\Support\DtrTimeRequestHours;
use App\Support\StudentMeritRulesNotice;
use App\Support\TimeRequestOvertimeLeaveImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DtrTimeRequestController extends Controller
{
    /**
     * Display user's pending and rejected time requests (not approved)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'student') {
            abort(403, 'Only students can view time requests.');
        }

        $timeRequests = DtrTimeRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'rejected'])
            ->where(function ($q): void {
                $q->where('request_type', 'regular')
                    ->orWhereNull('request_type');
            })
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['timeRequests' => $timeRequests]);
    }

    /**
     * Store time requests (regular hours on DTR; overtime → Leave Requests).
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'student') {
            abort(403, 'Only students can create time requests.');
        }

        if (! $user->canStudentRecordAttendance()) {
            return back()->withErrors([
                'nda' => 'You must upload a signed NDA and have it approved by an administrator before recording attendance.',
            ])->withInput();
        }

        $days = $request->input('days', []);
        $filteredDays = [];

        foreach ($days as $day) {
            if (! empty($day['time']) && trim($day['time']) !== '') {
                $time = trim($day['time']);
                if (preg_match('/^([0-1]?[0-9]|2[0-3]):([0-5][0-9])$/', $time)) {
                    $parts = explode(':', $time);
                    $day['time'] = str_pad($parts[0], 2, '0', STR_PAD_LEFT).':'.$parts[1];
                    $filteredDays[] = $day;
                }
            }
        }

        if (empty($filteredDays)) {
            return back()->withErrors(['days' => 'Please enter time for at least one day in HH:MM format (e.g., 08:00).'])->withInput();
        }

        $request->merge(['days' => $filteredDays]);

        try {
            $validated = $request->validate([
                'date_from' => 'required|date|before_or_equal:today',
                'date_to' => 'required|date|after_or_equal:date_from|before_or_equal:today',
                'days' => 'required|array|min:1',
                'days.*.date' => 'required|date|before_or_equal:today',
                'days.*.time' => 'required|date_format:H:i',
                'remarks' => 'nullable|string|max:1000',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $filterDateFrom = $request->input('filter_date_from');
        $filterDateTo = $request->input('filter_date_to');

        if ($filterDateFrom && $filterDateTo) {
            $requestFrom = Carbon::parse($validated['date_from']);
            $requestTo = Carbon::parse($validated['date_to']);
            $filterFrom = Carbon::parse($filterDateFrom);
            $filterTo = Carbon::parse($filterDateTo);

            if ($requestFrom->lt($filterFrom) || $requestTo->gt($filterTo)) {
                return back()->withErrors(['date_from' => 'The date range must be within the selected filter range.'])->withInput();
            }
        }

        $createdCount = 0;
        $updatedCount = 0;
        $leaveOvertimeCount = 0;
        $skippedCount = 0;
        $errors = [];
        $processedDates = [];
        $createdOvertimeLeaveIds = [];
        $today = Carbon::today()->startOfDay();

        foreach ($validated['days'] as $day) {
            $date = Carbon::parse($day['date'])->startOfDay();
            $dateKey = $date->toDateString();

            if (in_array($dateKey, $processedDates, true)) {
                $errors[] = "Duplicate date detected in this submission: {$dateKey}. Please keep only one entry per day.";
                continue;
            }

            if ($date->gt($today)) {
                $errors[] = "Date {$day['date']} cannot be in the future.";
                continue;
            }

            $pendingRegular = $this->pendingRegularRequest($user->id, $day['date']);
            $hours = DtrTimeRequestHours::timeStringToDecimal($day['time']);

            if ($pendingRegular) {
                $storedTotal = (float) ($pendingRegular->requested_total_hours ?? $pendingRegular->hours);
                $hours = max($hours, $storedTotal);
            }

            if ($hours < 0 || $hours > 24) {
                $errors[] = "Time for {$day['date']} must be between 00:00 and 24:00.";
                continue;
            }

            if ($day['date'] < $validated['date_from'] || $day['date'] > $validated['date_to']) {
                $errors[] = "Date {$day['date']} is outside the selected date range.";
                continue;
            }

            $batchId = $pendingRegular?->submission_batch ?: (string) Str::uuid();
            $split = DtrTimeRequestHours::splitTotalHours($hours);
            $overtimeHours = $split['overtime'];

            if ($pendingRegular && $overtimeHours <= 0) {
                $storedTotal = (float) ($pendingRegular->requested_total_hours ?? $pendingRegular->hours);
                $overtimeHours = max($storedTotal - DtrTimeRequestHours::STANDARD_DAY_HOURS, 0);
                if ($overtimeHours > 0 && $hours < $storedTotal) {
                    $hours = $storedTotal;
                    $split = DtrTimeRequestHours::splitTotalHours($hours);
                }
            }

            if ($pendingRegular) {
                if ($split['regular'] > 0 || $hours > 0) {
                    $pendingRegular->update([
                        'hours' => $split['regular'] > 0 ? $split['regular'] : (float) $pendingRegular->hours,
                        'requested_total_hours' => $hours > 0 ? $hours : $pendingRegular->requested_total_hours,
                        'remarks' => $validated['remarks'] ?? $pendingRegular->remarks,
                    ]);
                    $updatedCount++;
                }
            } elseif ($split['regular'] > 0) {
                $this->createRegularRequest(
                    $user->id,
                    $day['date'],
                    $split['regular'],
                    $batchId,
                    $hours,
                    $validated['remarks'] ?? null
                );
                $createdCount++;
            } elseif ($hours <= 0) {
                $errors[] = "Time for {$day['date']} must be greater than 00:00.";
                continue;
            }

            if ($pendingRegular && $overtimeHours <= 0 && $hours <= DtrTimeRequestHours::STANDARD_DAY_HOURS) {
                // Pending regular updated only; day total is 08:00 or less.
            }

            if ($overtimeHours > 0) {
                if ($this->hasExistingOvertimeForDate($user->id, $day['date'])) {
                    $errors[] = "An Additional Time request already exists for {$day['date']} (check Leave Requests).";
                    $skippedCount++;
                } else {
                    $regularRequest = $this->pendingRegularRequest($user->id, $day['date']);
                    if ($regularRequest) {
                        $overtimeLeave = TimeRequestOvertimeLeaveImport::ensurePendingAdditionalTimeFromRegularTimeRequest(
                            $regularRequest
                        );
                        if ($overtimeLeave) {
                            $createdOvertimeLeaveIds[] = $overtimeLeave->id;
                            $leaveOvertimeCount++;
                        }
                    }
                }
            }

            $processedDates[] = $dateKey;
        }

        if (count($errors) > 0 && $createdCount === 0 && $updatedCount === 0 && $leaveOvertimeCount === 0) {
            return back()->withErrors(['days' => $errors])->withInput();
        }

        if ($createdCount === 0 && $updatedCount === 0 && $leaveOvertimeCount === 0) {
            return back()->withErrors(['days' => array_merge($errors, ['No new time requests were created.'])])->withInput();
        }

        $parts = [];
        if ($createdCount > 0) {
            $parts[] = "{$createdCount} regular time request(s) submitted";
        }
        if ($updatedCount > 0) {
            $parts[] = "{$updatedCount} pending time request(s) updated";
        }
        if ($leaveOvertimeCount > 0) {
            $parts[] = "{$leaveOvertimeCount} Additional Time request(s) created in Leave Requests (details required)";
        }
        $message = 'Successfully '.implode('; ', $parts).'.';
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} entry/entries were skipped.";
        }
        if (count($errors) > 0) {
            $message .= ' Some dates had issues: '.implode(' ', array_slice($errors, 0, 3));
        }

        StudentMeritRulesNotice::syncForStudent($user);

        if ($leaveOvertimeCount === 1 && count($createdOvertimeLeaveIds) === 1) {
            return redirect()
                ->route('user.leave-requests.complete-attendance-overtime', $createdOvertimeLeaveIds[0])
                ->with('info', $message.' Please complete the Additional Time form (reason required) to submit for approval.');
        }

        if ($leaveOvertimeCount > 0) {
            $message .= ' Open each Additional Time request under Leave Requests and use Complete details.';

            return redirect()
                ->route('user.leave-requests.index')
                ->with('info', $message);
        }

        return back()->with('success', $message);
    }

    public function destroy(DtrTimeRequest $dtrTimeRequest)
    {
        $user = Auth::user();

        if ($user->role !== 'student') {
            abort(403, 'Only students can discard time requests.');
        }

        if ((int) $dtrTimeRequest->user_id !== (int) $user->id) {
            abort(403, 'You can only discard your own time requests.');
        }

        if ($dtrTimeRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending time requests can be discarded.']);
        }

        $batchId = $dtrTimeRequest->submission_batch;
        $datesToClear = [];

        if ($batchId) {
            $pendingInBatch = DtrTimeRequest::query()
                ->where('user_id', $user->id)
                ->where('submission_batch', $batchId)
                ->where('status', 'pending')
                ->get();

            $datesToClear = $pendingInBatch
                ->map(fn (DtrTimeRequest $request) => $request->date?->format('Y-m-d'))
                ->filter()
                ->unique()
                ->values()
                ->all();

            DtrTimeRequest::query()
                ->where('user_id', $user->id)
                ->where('submission_batch', $batchId)
                ->where('status', 'pending')
                ->delete();

            TimeRequestOvertimeLeaveImport::discardPendingLeaveForBatch((int) $user->id, $batchId);
        } else {
            $dateStr = $dtrTimeRequest->date?->format('Y-m-d');
            if ($dateStr) {
                $datesToClear = [$dateStr];
            }
            $dtrTimeRequest->delete();
        }

        if ($datesToClear === []) {
            $fallbackDate = $dtrTimeRequest->date?->format('Y-m-d');
            if ($fallbackDate) {
                $datesToClear = [$fallbackDate];
            }
        }

        TimeRequestOvertimeLeaveImport::discardPendingAttendanceOvertimeForDates((int) $user->id, $datesToClear);

        StudentMeritRulesNotice::syncForStudent($user);

        return back()->with('success', 'Pending time request(s) and linked Additional Time leave request(s) discarded successfully.');
    }

    private function pendingRegularRequest(int $userId, string $date): ?DtrTimeRequest
    {
        return DtrTimeRequest::query()
            ->where('user_id', $userId)
            ->whereDate('date', $date)
            ->where('status', 'pending')
            ->where(function ($q): void {
                $q->where('request_type', 'regular')->orWhereNull('request_type');
            })
            ->first();
    }

    private function hasExistingOvertimeForDate(int $userId, string $date): bool
    {
        if (TimeRequestOvertimeLeaveImport::hasPendingOrApprovedOvertimeLeaveForDate($userId, $date)) {
            return true;
        }

        return DtrTimeRequest::query()
            ->where('user_id', $userId)
            ->whereDate('date', $date)
            ->where('request_type', 'overtime')
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
    }

    private function createRegularRequest(
        int $userId,
        string $date,
        float $hours,
        string $batchId,
        ?float $requestedTotalHours,
        ?string $remarks
    ): void {
        DtrTimeRequest::create([
            'user_id' => $userId,
            'date' => $date,
            'hours' => $hours,
            'request_type' => 'regular',
            'submission_batch' => $batchId,
            'requested_total_hours' => $requestedTotalHours,
            'remarks' => $remarks,
            'status' => 'pending',
        ]);
    }
}
