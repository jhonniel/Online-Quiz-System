<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'system_name' => 'required|string|max:255',
            'system_description' => 'nullable|string|max:500',
            'system_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'system_icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            'remove_logo' => 'boolean',
            'remove_icon' => 'boolean',
            'primary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'seasonal_effects' => 'nullable|string|in:halloween,christmas,disabled',
            'maintenance_mode' => 'nullable|string|in:enabled,disabled',
            'maintenance_message' => 'nullable|string|max:1000',
        ]);

        // Update system name
        Setting::set('system_name', $request->system_name, 'text', 'The name of the quiz system');

        // Update system description
        Setting::set('system_description', $request->system_description, 'text', 'System description');

        // Update colors
        if ($request->primary_color) {
            Setting::set('primary_color', $request->primary_color, 'color', 'Primary color for the system');
        }
        if ($request->secondary_color) {
            Setting::set('secondary_color', $request->secondary_color, 'color', 'Secondary color for the system');
        }

        // Handle logo upload
        if ($request->hasFile('system_logo')) {
            // Delete old logo if exists
            $oldLogo = Setting::get('system_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            // Store new logo
            $logoPath = $request->file('system_logo')->store('logos', 'public');
            Setting::set('system_logo', $logoPath, 'image', 'The system logo');
        }

        // Handle logo removal
        if ($request->has('remove_logo') && $request->remove_logo) {
            $oldLogo = Setting::get('system_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            Setting::set('system_logo', null, 'image', 'The system logo');
        }

        // Handle icon upload
        if ($request->hasFile('system_icon')) {
            // Delete old icon if exists
            $oldIcon = Setting::get('system_icon');
            if ($oldIcon && Storage::disk('public')->exists($oldIcon)) {
                Storage::disk('public')->delete($oldIcon);
            }

            // Store new icon
            $iconPath = $request->file('system_icon')->store('icons', 'public');
            Setting::set('system_icon', $iconPath, 'image', 'The system icon/favicon');
        }

        // Handle icon removal
        if ($request->has('remove_icon') && $request->remove_icon) {
            $oldIcon = Setting::get('system_icon');
            if ($oldIcon && Storage::disk('public')->exists($oldIcon)) {
                Storage::disk('public')->delete($oldIcon);
            }
            Setting::set('system_icon', null, 'image', 'The system icon/favicon');
        }

        // Handle seasonal effects
        $seasonalEffect = $request->seasonal_effects ?? 'disabled';
        Setting::set('seasonal_effects', $seasonalEffect, 'text', 'Seasonal effects (halloween, christmas, disabled)');

        // Handle maintenance mode
        $maintenanceMode = $request->maintenance_mode ?? 'disabled';
        Setting::set('maintenance_mode', $maintenanceMode, 'text', 'System maintenance mode (enabled, disabled)');

        // Handle maintenance message
        $maintenanceMessage = $request->maintenance_message ?? 'We are currently performing scheduled maintenance. Please check back later.';
        Setting::set('maintenance_message', $maintenanceMessage, 'text', 'Maintenance mode message');

        // Clear cache to ensure changes are reflected immediately
        Setting::clearCache();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
