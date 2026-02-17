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
        $user = $qrToken->user()->with(['department', 'university', 'adminPermission'])->first();

        // If user not found, show error
        if (!$user) {
            return view('qr.not-found', ['qrCodeId' => null, 'settings' => $settings]);
        }

        // Check if user has permission to have QR code scanned
        // Employees can always have their QR code scanned
        // Other users can have QR code scanned if they have an adminPermission record (admin granted permission)
        $canAccessQrCode = false;
        
        if ($user->role === 'employee') {
            // Employees can always access
            $canAccessQrCode = true;
        } elseif ($user->adminPermission) {
            // Non-employees can access if they have an adminPermission record (admin granted permission)
            $canAccessQrCode = true;
        }

        if (!$canAccessQrCode) {
            return view('qr.not-available', compact('settings', 'user'));
        }

        // Don't mark token as used - keep it reusable so QR code stays static
        // Token is permanent and can be scanned multiple times

        return view('qr.scan', compact('user', 'settings'));
    }
}
