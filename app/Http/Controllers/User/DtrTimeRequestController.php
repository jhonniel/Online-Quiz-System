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
     * Store a new time request (can be multiple days with different hours)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Only students can create time requests
        if ($user->role !== 'student') {
            abort(403, 'Only students can create time requests.');
        }

        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'days' => 'required|array|min:1',
            'days.*.date' => 'required|date',
            'days.*.time' => 'required|date_format:H:i',
            'remarks' => 'nullable|string|max:1000',
        ]);

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

        foreach ($validated['days'] as $index => $day) {
            $date = Carbon::parse($day['date']);
            
            // Convert time (HH:MM) to decimal hours
            $timeParts = explode(':', $day['time']);
            $hours = (float) $timeParts[0] + ((float) $timeParts[1] / 60);
            
            // Validate hours (0.01 to 24)
            if ($hours < 0.01 || $hours > 24) {
                $errors[] = "Time for {$day['date']} must be between 00:01 and 24:00.";
                continue;
            }

            // Validate date is within the requested range
            $requestFrom = Carbon::parse($validated['date_from']);
            $requestTo = Carbon::parse($validated['date_to']);
            
            if ($date->lt($requestFrom) || $date->gt($requestTo)) {
                $errors[] = "Date {$day['date']} is outside the selected date range.";
                continue;
            }

            // Check if there's already a pending or approved request for this date
            $existingRequest = DtrTimeRequest::where('user_id', $user->id)
                ->where('date', $day['date'])
                ->whereIn('status', ['pending', 'approved'])
                ->first();

            if ($existingRequest) {
                $skippedCount++;
                continue;
            }

            // Check if DTR already exists for this date
            $existingDtr = \App\Models\Dtr::where('user_id', $user->id)
                ->whereDate('date', $day['date'])
                ->first();

            if ($existingDtr) {
                $skippedCount++;
                continue;
            }

            // Create time request for this day
            DtrTimeRequest::create([
                'user_id' => $user->id,
                'date' => $day['date'],
                'hours' => $hours,
                'remarks' => $validated['remarks'] ?? null,
                'status' => 'pending',
            ]);

            $createdCount++;
        }

        if (count($errors) > 0) {
            return back()->withErrors(['days' => $errors])->withInput();
        }

        if ($createdCount === 0) {
            return back()->withErrors(['days' => 'No new time requests were created. All dates may already have pending/approved requests or existing DTR records.'])->withInput();
        }

        $message = "Successfully submitted {$createdCount} time request(s).";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} date(s) were skipped (already have requests or DTR records).";
        }

        return back()->with('success', $message);
    }
}
