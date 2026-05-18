<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\QrCodeToken;
use App\Models\Setting;

class QrCodeController extends Controller
{
    /**
     * Display user information when QR code is scanned
     * Uses one-time token for security
     */
    public function scan($token)
    {
        // Get settings for the view
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        // Find token
        $qrToken = QrCodeToken::where('token', $token)->first();

        // If token not found, show not found message
        if (! $qrToken) {
            return view('qr.not-found', ['qrCodeId' => null, 'settings' => $settings]);
        }

        // Get user with relationships
        $user = $qrToken->user()->with(['department', 'university'])->first();

        // If user not found, show error
        if (! $user) {
            return view('qr.not-found', ['qrCodeId' => null, 'settings' => $settings]);
        }

        if (! $user->canAccessQrCode()) {
            return view('qr.not-available', compact('settings', 'user'));
        }

        // Don't mark token as used - keep it reusable so QR code stays static
        // Token is permanent and can be scanned multiple times

        // Approved leaves to show on ID only when today is the same or within the leave duration
        // Only types: Vacation, Sick, Work from home, Travel
        $today = now()->startOfDay();
        $approvedLeaves = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereIn('type', ['vacation_leave', 'sick_leave', 'work_from_home', 'travel'])
            ->where(function ($q) use ($today) {
                // Single-day leave (no end_date or end_date = start_date): show only if start_date is today
                $q->where(function ($q2) use ($today) {
                    $q2->whereNull('end_date')->whereDate('start_date', $today);
                })
                    ->orWhere(function ($q2) use ($today) {
                        // Multi-day: today must be between start_date and end_date (inclusive)
                        $q2->whereNotNull('end_date')
                            ->whereDate('start_date', '<=', $today)
                            ->whereDate('end_date', '>=', $today);
                    });
            })
            ->orderBy('start_date', 'desc')
            ->get();

        return view('qr.scan', compact('user', 'settings', 'approvedLeaves'));
    }
}
