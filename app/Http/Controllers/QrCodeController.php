<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use App\Models\QrCodeToken;
use Illuminate\Http\Request;

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
        if (!$qrToken) {
            return view('qr.not-found', ['qrCodeId' => null, 'settings' => $settings]);
        }

        // Get user with relationships
        $user = $qrToken->user()->with(['department', 'university'])->first();

        // If user not found, show error
        if (!$user) {
            return view('qr.not-found', ['qrCodeId' => null, 'settings' => $settings]);
        }

        // Only show information for employees
        if ($user->role !== 'employee') {
            return view('qr.not-available', compact('settings', 'user'));
        }

        // Don't mark token as used - keep it reusable so QR code stays static
        // Token is permanent and can be scanned multiple times

        return view('qr.scan', compact('user', 'settings'));
    }
}
