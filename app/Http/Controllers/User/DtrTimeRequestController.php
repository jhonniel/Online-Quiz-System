<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DtrTimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DtrTimeRequestController extends Controller
{
    /**
     * Display user's pending and rejected time requests (not approved)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Only students can view time requests
        if ($user->role !== 'student') {
            abort(403, 'Only students can view time requests.');
        }

        // Get only pending and rejected requests (not approved)
        $timeRequests = DtrTimeRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'rejected'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['timeRequests' => $timeRequests]);
    }

    /**
     * Store a new time request (can be multiple days with different hours)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Only students can create time requests
        if ($user->role !== 'student') {
            abort(403, 'Only students can create time requests.');
        }

        // Log incoming request for debugging
        \Log::info('DTR Time Request Store - Input', [
            'user_id' => $user->id,
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'days_count' => count($request->input('days', [])),
            'days' => $request->input('days', []),
        ]);

        // Filter out days with empty time before validation
        $days = $request->input('days', []);
        $filteredDays = [];
        
        foreach ($days as $day) {
            if (!empty($day['time']) && trim($day['time']) !== '') {
                $time = trim($day['time']);
                // Ensure time is in HH:MM format
                if (preg_match('/^([0-1]?[0-9]|2[0-3]):([0-5][0-9])$/', $time)) {
                    // Normalize to HH:MM format (pad hours if needed)
                    $parts = explode(':', $time);
                    $hours = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                    $minutes = $parts[1];
                    $day['time'] = $hours . ':' . $minutes;
                    $filteredDays[] = $day;
                } else {
                    \Log::warning('Invalid time format', ['time' => $time, 'day' => $day]);
                }
            }
        }
        
        // If no valid days after filtering, return error
        if (empty($filteredDays)) {
            \Log::warning('No valid days with time', ['original_days' => $days]);
            return back()->withErrors(['days' => 'Please enter time for at least one day in HH:MM format (e.g., 08:00).'])->withInput();
        }
        
        // Replace days in request with filtered days
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
            
            \Log::info('DTR Time Request Validation Passed', ['validated' => $validated]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('DTR Time Request Validation Failed', [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);
            return back()->withErrors($e->errors())->withInput();
        }

        // Check if date range is within the filter range (if provided)
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
        $skippedCount = 0;
        $errors = [];
        $processedDates = [];

        $today = Carbon::today()->startOfDay();
        
        foreach ($validated['days'] as $index => $day) {
            $date = Carbon::parse($day['date'])->startOfDay();
            $dateKey = $date->toDateString();

            if (in_array($dateKey, $processedDates, true)) {
                $errors[] = "Duplicate date detected in this submission: {$dateKey}. Please keep only one entry per day.";
                continue;
            }
            
            // Validate date is not in the future (compare dates only, not time)
            if ($date->gt($today)) {
                $errors[] = "Date {$day['date']} cannot be in the future. Only past and today's dates are allowed.";
                continue;
            }
            
            // Convert time (HH:MM) to decimal hours
            $timeParts = explode(':', $day['time']);
            if (count($timeParts) !== 2) {
                $errors[] = "Invalid time format for {$day['date']}. Expected HH:MM format.";
                continue;
            }
            
            $hours = (float) $timeParts[0] + ((float) $timeParts[1] / 60);
            
            // Validate hours (0 to 24, allow 00:00)
            if ($hours < 0 || $hours > 24) {
                $errors[] = "Time for {$day['date']} must be between 00:00 and 24:00.";
                continue;
            }
            
            // Allow 00:00 but warn if it's exactly 0
            if ($hours == 0) {
                \Log::info('Zero hours time request', ['date' => $day['date'], 'user_id' => $user->id]);
            }

            // Validate date is within the requested range (using date comparison only, ignore time)
            // Use date strings directly to avoid timezone issues
            $dayDateStr = $day['date'];
            $requestFromStr = $validated['date_from'];
            $requestToStr = $validated['date_to'];
            
            // Simple string comparison for dates (YYYY-MM-DD format)
            if ($dayDateStr < $requestFromStr || $dayDateStr > $requestToStr) {
                \Log::warning('Date outside range', [
                    'day_date' => $dayDateStr,
                    'date_from' => $requestFromStr,
                    'date_to' => $requestToStr,
                    'user_id' => $user->id
                ]);
                $errors[] = "Date {$dayDateStr} is outside the selected date range ({$requestFromStr} to {$requestToStr}).";
                continue;
            }

            // Enforce one time request per day (any status) to avoid duplication.
            $existingRequest = DtrTimeRequest::where('user_id', $user->id)
                ->whereDate('date', $day['date'])
                ->first();

            if ($existingRequest) {
                $errors[] = "A time request already exists for {$day['date']} (one request per day only).";
                $skippedCount++;
                continue;
            }

            // Create time request for this day
            try {
                $timeRequest = DtrTimeRequest::create([
                    'user_id' => $user->id,
                    'date' => $day['date'],
                    'hours' => $hours,
                    'remarks' => $validated['remarks'] ?? null,
                    'status' => 'pending',
                ]);
                
                \Log::info('DTR Time Request Created', [
                    'id' => $timeRequest->id,
                    'user_id' => $user->id,
                    'date' => $day['date'],
                    'hours' => $hours,
                ]);
                
                $createdCount++;
                $processedDates[] = $dateKey;
            } catch (\Exception $e) {
                \Log::error('Failed to create DTR Time Request', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'date' => $day['date'],
                    'hours' => $hours,
                    'trace' => $e->getTraceAsString(),
                ]);
                $errors[] = "Failed to create time request for {$day['date']}: " . $e->getMessage();
            }
        }

        if (count($errors) > 0) {
            \Log::warning('DTR Time Request Errors', [
                'errors' => $errors,
                'user_id' => $user->id,
                'created_count' => $createdCount,
                'skipped_count' => $skippedCount
            ]);
            return back()->withErrors(['days' => $errors])->withInput();
        }

        if ($createdCount === 0) {
            \Log::warning('No DTR Time Requests Created', [
                'user_id' => $user->id,
                'skipped_count' => $skippedCount,
                'errors_count' => count($errors)
            ]);
            return back()->withErrors(['days' => 'No new time requests were created. All dates may already have pending/approved requests.'])->withInput();
        }
        
        \Log::info('DTR Time Requests Created Successfully', [
            'user_id' => $user->id,
            'created_count' => $createdCount,
            'skipped_count' => $skippedCount
        ]);

        $message = "Successfully submitted {$createdCount} time request(s).";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} date(s) were skipped (already have pending/approved requests).";
        }

        return back()->with('success', $message);
    }

    /**
     * Allow student to discard their own pending time request.
     */
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

        $dtrTimeRequest->delete();

        return back()->with('success', 'Pending time request discarded successfully.');
    }
}
