<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index()
    {
        $settingsCollection = Setting::all()->keyBy('key');
        $settings = [];
        foreach ($settingsCollection as $key => $setting) {
            $settings[$key] = $setting->value;
        }

        // Overtime credited window (read from settings, default to 12 months)
        $settings['overtime_months_credited'] = (int) Setting::get('overtime_months_credited', 12);

        // Default leave balances
        $settings['default_vacation_balance'] = (float) Setting::get('default_vacation_balance', 15);
        $settings['default_sick_leave_balance'] = (float) Setting::get('default_sick_leave_balance', 10);

        // Ensure all email settings are loaded with defaults
        $settings['mail_mailer'] = Setting::get('mail_mailer', 'log');
        $settings['mail_host'] = Setting::get('mail_host', '');
        $settings['mail_port'] = Setting::get('mail_port', '587');
        $settings['mail_username'] = Setting::get('mail_username', '');
        $settings['mail_password'] = Setting::get('mail_password', ''); // Note: password is stored but not displayed for security
        $settings['mail_encryption'] = Setting::get('mail_encryption', 'tls');
        $settings['mail_from_address'] = Setting::get('mail_from_address', '');
        $settings['mail_from_name'] = Setting::get('mail_from_name', '');

        // Get system health information
        $health = $this->getSystemHealth();

        return view('admin.settings.index', compact('settings', 'health'));
    }

    /**
     * Get system health information
     */
    private function getSystemHealth()
    {
        $health = [
            'status' => 'healthy',
            'checks' => [],
            'server' => [],
            'database' => [],
            'application' => [],
        ];

        try {
            // Database Connection Check
            \DB::connection()->getPdo();
            $health['database']['status'] = 'connected';
            $health['database']['driver'] = \DB::connection()->getDriverName();
            $health['database']['version'] = \DB::select('SELECT version() as version')[0]->version ?? 'Unknown';
            $health['checks']['database'] = true;
        } catch (\Exception $e) {
            $health['database']['status'] = 'disconnected';
            $health['database']['error'] = $e->getMessage();
            $health['checks']['database'] = false;
            $health['status'] = 'unhealthy';
        }

        // PHP Information
        $health['server']['php_version'] = PHP_VERSION;
        $health['server']['php_memory_limit'] = ini_get('memory_limit');
        $health['server']['php_max_execution_time'] = ini_get('max_execution_time');
        $health['server']['php_upload_max_filesize'] = ini_get('upload_max_filesize');
        $health['server']['php_post_max_size'] = ini_get('post_max_size');

        // Laravel Information
        $health['application']['laravel_version'] = app()->version();
        $health['application']['app_name'] = config('app.name');
        $health['application']['app_env'] = config('app.env');
        $health['application']['app_debug'] = config('app.debug') ? 'Enabled' : 'Disabled';
        $health['application']['timezone'] = config('app.timezone');

        // Cache Status
        try {
            \Cache::put('health_check', 'ok', 1);
            $health['checks']['cache'] = \Cache::get('health_check') === 'ok';
            $health['application']['cache_driver'] = config('cache.default');
        } catch (\Exception $e) {
            $health['checks']['cache'] = false;
            $health['status'] = 'unhealthy';
        }

        // Storage Status
        try {
            $disk = \Storage::disk('public');
            $health['checks']['storage'] = $disk->exists('.') || $disk->put('health_check.txt', 'ok');
            if ($health['checks']['storage']) {
                $disk->delete('health_check.txt');
            }
        } catch (\Exception $e) {
            $health['checks']['storage'] = false;
            $health['status'] = 'unhealthy';
        }

        // Queue Status (if queue driver is not sync)
        if (config('queue.default') !== 'sync') {
            try {
                $health['checks']['queue'] = true;
                $health['application']['queue_driver'] = config('queue.default');
            } catch (\Exception $e) {
                $health['checks']['queue'] = false;
            }
        } else {
            $health['checks']['queue'] = true;
            $health['application']['queue_driver'] = 'sync';
        }

        // Memory Usage
        $health['server']['memory_usage'] = $this->formatBytes(memory_get_usage(true));
        $health['server']['memory_peak'] = $this->formatBytes(memory_get_peak_usage(true));

        // Disk Space (if available)
        if (function_exists('disk_free_space') && function_exists('disk_total_space')) {
            $free = disk_free_space(base_path());
            $total = disk_total_space(base_path());
            if ($free !== false && $total !== false) {
                $health['server']['disk_free'] = $this->formatBytes($free);
                $health['server']['disk_total'] = $this->formatBytes($total);
                $health['server']['disk_used_percent'] = round((($total - $free) / $total) * 100, 2);
            }
        }

        // Count Statistics
        try {
            $health['statistics'] = [
                'total_users' => \App\Models\User::count(),
                'active_users' => \App\Models\User::where('is_active', true)->count(),
                'total_quizzes' => \App\Models\Quiz::count(),
                'active_quizzes' => \App\Models\Quiz::where('is_active', true)->count(),
                'total_attempts' => \App\Models\QuizAttempt::count(),
                'today_attempts' => \App\Models\QuizAttempt::whereDate('created_at', today())->count(),
                'pending_applications' => \App\Models\HiringApplication::where('status', 'pending')->count(),
            ];
        } catch (\Exception $e) {
            $health['statistics'] = [];
        }

        // Overall Status
        if (in_array(false, $health['checks'])) {
            $health['status'] = 'unhealthy';
        } elseif (in_array(null, $health['checks'])) {
            $health['status'] = 'warning';
        } else {
            $health['status'] = 'healthy';
        }

        return $health;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
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
            'hiring_process_enabled' => 'nullable|string|in:enabled,disabled',
            'hiring_process_description' => 'nullable|string|max:1000',
            'minimum_quiz_score' => 'nullable|integer|min:0|max:100',
            'auto_approve_score' => 'nullable|integer|min:0|max:100',
            'hiring_stages' => 'nullable|string|max:2000',
            'hiring_email_notifications' => 'nullable|string|in:enabled,disabled',
            'hiring_instructions' => 'nullable|string|max:2000',
            'hiring_application_public_access' => 'nullable|string|in:enabled,disabled',
            'hiring_application_url' => 'nullable|string|max:255|regex:/^[a-z0-9\-\/_]+$/i',
            'overtime_months_credited' => 'nullable|integer|in:12,9,6,3,1',
            'leave_immediate_supervisor' => 'nullable|string|max:255',
            'leave_hr_admin' => 'nullable|string|max:255',
            'leave_cto' => 'nullable|string|max:255',
            'default_vacation_balance' => 'nullable|numeric|min:0|max:365',
            'default_sick_leave_balance' => 'nullable|numeric|min:0|max:365',
            // Email Configuration
            'mail_mailer' => 'nullable|string|in:smtp,sendmail,mailgun,ses,postmark,resend,log,array',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl,null',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
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

        // Handle hiring process settings
        $hiringProcessEnabled = $request->hiring_process_enabled ?? 'enabled';
        Setting::set('hiring_process_enabled', $hiringProcessEnabled, 'text', 'Enable or disable hiring process feature');

        $hiringProcessDescription = $request->hiring_process_description ?? '';
        Setting::set('hiring_process_description', $hiringProcessDescription, 'text', 'Description of the hiring process workflow');

        $minimumQuizScore = $request->minimum_quiz_score ?? 70;
        Setting::set('minimum_quiz_score', $minimumQuizScore, 'number', 'Minimum quiz score percentage required to pass');

        $autoApproveScore = $request->auto_approve_score ?? null;
        Setting::set('auto_approve_score', $autoApproveScore, 'number', 'Quiz score percentage for automatic approval');

        $hiringStages = $request->hiring_stages ?? '';
        Setting::set('hiring_stages', $hiringStages, 'text', 'List of hiring process stages');

        $hiringEmailNotifications = $request->hiring_email_notifications ?? 'enabled';
        Setting::set('hiring_email_notifications', $hiringEmailNotifications, 'text', 'Enable or disable email notifications for hiring process');

        $hiringInstructions = $request->hiring_instructions ?? '';
        Setting::set('hiring_instructions', $hiringInstructions, 'text', 'Instructions displayed to applicants before quiz');

        $hiringApplicationPublicAccess = $request->hiring_application_public_access ?? 'disabled';
        Setting::set('hiring_application_public_access', $hiringApplicationPublicAccess, 'text', 'Enable or disable public access to hiring application form');

        $hiringApplicationUrl = $request->hiring_application_url ?? 'hiring/apply';
        // Ensure URL doesn't start with / and is a valid path
        $hiringApplicationUrl = ltrim($hiringApplicationUrl, '/');
        Setting::set('hiring_application_url', $hiringApplicationUrl, 'text', 'Custom URL path for hiring application form (e.g., careers, jobs, apply)');

        // Handle overtime credited window (global for all employees)
        if ($request->filled('overtime_months_credited')) {
            $months = (int) $request->overtime_months_credited;
            Setting::set('overtime_months_credited', $months, 'number', 'Months of overtime history credited for balances');

            // Apply to all employees so controllers can read from user model
            User::where('role', 'employee')->update([
                'overtime_months_credited' => $months,
            ]);
        }

        // Leave Request Signatories
        $leaveImmediateSupervisor = $request->leave_immediate_supervisor ?? 'CHARMAINE JOY ROSATACE';
        Setting::set('leave_immediate_supervisor', $leaveImmediateSupervisor, 'text', 'Name for Immediate Supervisor in leave request letters');

        $leaveHrAdmin = $request->leave_hr_admin ?? 'MAY GRACE ACOSTA';
        Setting::set('leave_hr_admin', $leaveHrAdmin, 'text', 'Name for HR Admin in leave request letters');

        $leaveCto = $request->leave_cto ?? 'NITISH KHEMANI';
        Setting::set('leave_cto', $leaveCto, 'text', 'Name for Chief Technology Officer in leave request letters');

        // Default Leave Balances
        $defaultVacationBalance = $request->default_vacation_balance ?? 15;
        Setting::set('default_vacation_balance', $defaultVacationBalance, 'number', 'Default vacation leave balance in days for new employees');

        $defaultSickLeaveBalance = $request->default_sick_leave_balance ?? 10;
        Setting::set('default_sick_leave_balance', $defaultSickLeaveBalance, 'number', 'Default sick leave balance in days for new employees');

        // Email Configuration Settings
        $mailMailer = $request->mail_mailer ?? 'log';
        Setting::set('mail_mailer', $mailMailer, 'text', 'Email mailer driver (smtp, sendmail, mailgun, ses, postmark, resend, log, array)');

        $mailHost = $request->mail_host ?? '';
        Setting::set('mail_host', $mailHost, 'text', 'SMTP server hostname');

        $mailPort = $request->mail_port ?? 587;
        Setting::set('mail_port', $mailPort, 'number', 'SMTP server port (usually 587 for TLS, 465 for SSL)');

        $mailUsername = $request->mail_username ?? '';
        Setting::set('mail_username', $mailUsername, 'text', 'SMTP username/email');

        // Only update password if a new one is provided (leave blank to keep current)
        if ($request->filled('mail_password')) {
            $mailPassword = $request->mail_password;
            Setting::set('mail_password', $mailPassword, 'text', 'SMTP password (stored encrypted)');
        }

        $mailEncryption = $request->mail_encryption ?? 'tls';
        Setting::set('mail_encryption', $mailEncryption, 'text', 'SMTP encryption (tls, ssl, or null)');

        $mailFromAddress = $request->mail_from_address ?? '';
        Setting::set('mail_from_address', $mailFromAddress, 'text', 'Default "From" email address');

        $mailFromName = $request->mail_from_name ?? '';
        Setting::set('mail_from_name', $mailFromName, 'text', 'Default "From" name');

        // Clear cache to ensure changes are reflected immediately
        Setting::clearCache();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    /**
     * Get system health data via AJAX
     */
    public function getHealth()
    {
        $health = $this->getSystemHealth();
        return response()->json($health);
    }

    /**
     * Send a test email
     */
    public function testEmail(Request $request)
    {
        try {
            $request->validate([
                'test_email' => 'required|email|max:255',
            ]);

            $testEmail = $request->input('test_email');

            // Ensure mail configuration is up to date from settings
            MailConfigService::configure();

            // Basic config validation for SMTP mailer
            $mailer = Setting::get('mail_mailer', 'log');
            if ($mailer === 'smtp') {
                $host = Setting::get('mail_host');
                $port = Setting::get('mail_port');
                $username = Setting::get('mail_username');
                $password = Setting::get('mail_password');
                $fromAddress = Setting::get('mail_from_address');

                if (empty($host) || empty($port) || empty($username) || empty($password) || empty($fromAddress)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'SMTP configuration is incomplete. Please make sure Host, Port, Username, Password and From Address are all set before sending a test email.'
                    ], 422);
                }
            }

            // Attempt to send the email synchronously
            Mail::to($testEmail)->send(new \App\Mail\TestEmail());

            // If the underlying mailer exposes failures, check them as an extra safety net
            try {
                $mailerInstance = Mail::getFacadeRoot();
                if (is_object($mailerInstance) && method_exists($mailerInstance, 'failures')) {
                    $failures = $mailerInstance->failures();
                    if (!empty($failures)) {
                        Log::error('Test email reported transport failures', ['failures' => $failures]);
                        return response()->json([
                            'success' => false,
                            'message' => 'The mailer reported a delivery problem for: ' . implode(', ', $failures) . '. Please verify your email configuration.'
                        ], 500);
                    }
                }
            } catch (\Throwable $t) {
                // If the method is not available (newer mailer), ignore and rely on exceptions from send()
            }

            return response()->json([
                'success' => true,
                'message' => 'Test email was sent successfully to ' . $testEmail . '. Please check that it arrives in the inbox (and spam folder).'
            ]);
        } catch (\Exception $e) {
            Log::error('Test email failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ], 500);
        }
    }
}
