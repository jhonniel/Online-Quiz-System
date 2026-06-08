<?php

namespace App\Http\Controllers;

use App\Models\DocumentExportVerification;
use App\Models\Setting;

class DocumentExportVerificationController extends Controller
{
    public function show(string $token)
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $verification = DocumentExportVerification::with('generator')
            ->where('token', $token)
            ->first();

        if (! $verification) {
            return view('verify.document-export-not-found', compact('settings'));
        }

        return view('verify.document-export', compact('verification', 'settings'));
    }
}
