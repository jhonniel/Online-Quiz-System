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
        // Load all settings directly from database (bypass cache)
        // Use fresh() to ensure we get the latest data from database, not cached
        $settingsCollection = Setting::all()->keyBy('key');
        $settings = [];
        foreach ($settingsCollection as $key => $setting) {
            $settings[$key] = $setting->value;
        }

        // Overtime credited window (read from settings, default to 12 months)
        $settings['overtime_months_credited'] = (int) ($settings['overtime_months_credited'] ?? 12);

        // Default leave balances - ALWAYS reload directly from database to ensure latest values
        // This is critical - we must bypass any potential caching
        // Do this AFTER all other settings are loaded to ensure we overwrite any cached values
        $vacationSetting = Setting::withoutGlobalScopes()
            ->where('key', 'default_vacation_balance')
            ->first();
        $sickSetting = Setting::withoutGlobalScopes()
            ->where('key', 'default_sick_leave_balance')
            ->first();

        // Use database value if it exists and is not empty, otherwise default to 0
        if ($vacationSetting && $vacationSetting->value !== null && $vacationSetting->value !== '') {
            $settings['default_vacation_balance'] = (float) $vacationSetting->value;
        } else {
            $settings['default_vacation_balance'] = 0.0;
        }

        if ($sickSetting && $sickSetting->value !== null && $sickSetting->value !== '') {
            $settings['default_sick_leave_balance'] = (float) $sickSetting->value;
        } else {
            $settings['default_sick_leave_balance'] = 0.0;
        }

        // Email settings will be set at the end to ensure they're not overwritten

        // CRITICAL: Set overtime, leave balances, and signatories at the VERY END to ensure they're not overwritten
        // Reload directly from database one more time to be absolutely sure we have the latest values

        // Overtime months credited
        $overtimeSetting = Setting::where('key', 'overtime_months_credited')->first();
        if ($overtimeSetting && $overtimeSetting->value !== null && $overtimeSetting->value !== '' && trim($overtimeSetting->value) !== '') {
            $settings['overtime_months_credited'] = (int) $overtimeSetting->value;
        } else {
            $settings['overtime_months_credited'] = 12;
        }

        // Leave Request Signatories
        $leaveSupervisorSetting = Setting::where('key', 'leave_immediate_supervisor')->first();
        $leaveHrSetting = Setting::where('key', 'leave_hr_admin')->first();
        $leaveCtoSetting = Setting::where('key', 'leave_cto')->first();

        if ($leaveSupervisorSetting && $leaveSupervisorSetting->value !== null && $leaveSupervisorSetting->value !== '' && trim($leaveSupervisorSetting->value) !== '') {
            $settings['leave_immediate_supervisor'] = $leaveSupervisorSetting->value;
        } else {
            $settings['leave_immediate_supervisor'] = 'CHARMAINE JOY ROSATACE';
        }

        if ($leaveHrSetting && $leaveHrSetting->value !== null && $leaveHrSetting->value !== '' && trim($leaveHrSetting->value) !== '') {
            $settings['leave_hr_admin'] = $leaveHrSetting->value;
        } else {
            $settings['leave_hr_admin'] = 'MAY GRACE ACOSTA';
        }

        if ($leaveCtoSetting && $leaveCtoSetting->value !== null && $leaveCtoSetting->value !== '' && trim($leaveCtoSetting->value) !== '') {
            $settings['leave_cto'] = $leaveCtoSetting->value;
        } else {
            $settings['leave_cto'] = 'NITISH KHEMANI';
        }

        // Leave Admin Notification Email
        $leaveAdminNotificationEmailSetting = Setting::where('key', 'leave_admin_notification_email')->first();
        if ($leaveAdminNotificationEmailSetting && $leaveAdminNotificationEmailSetting->value !== null && trim($leaveAdminNotificationEmailSetting->value) !== '') {
            $settings['leave_admin_notification_email'] = $leaveAdminNotificationEmailSetting->value;
        } else {
            $settings['leave_admin_notification_email'] = '';
        }

        // Default leave balances - use database value if it exists and is not empty, otherwise default to 0
        $vacationSetting = Setting::where('key', 'default_vacation_balance')->first();
        $sickSetting = Setting::where('key', 'default_sick_leave_balance')->first();

        // Final check - use database value if it exists and is not empty, otherwise default to 0
        // Be very explicit about checking for empty values
        if ($vacationSetting && $vacationSetting->value !== null && $vacationSetting->value !== '' && trim($vacationSetting->value) !== '') {
            $settings['default_vacation_balance'] = (float) $vacationSetting->value;
        } else {
            $settings['default_vacation_balance'] = 0.0;
        }

        if ($sickSetting && $sickSetting->value !== null && $sickSetting->value !== '' && trim($sickSetting->value) !== '') {
            $settings['default_sick_leave_balance'] = (float) $sickSetting->value;
        } else {
            $settings['default_sick_leave_balance'] = 0.0;
        }

        // Contact Information Settings - reload directly from database
        $contactEmailSetting = Setting::where('key', 'contact_email')->first();
        $contactPhoneSetting = Setting::where('key', 'contact_phone')->first();
        $contactPhoneHoursSetting = Setting::where('key', 'contact_phone_hours')->first();
        $contactEmailResponseTimeSetting = Setting::where('key', 'contact_email_response_time')->first();
        $contactLiveChatDescriptionSetting = Setting::where('key', 'contact_live_chat_description')->first();
        $contactLiveChatHoursSetting = Setting::where('key', 'contact_live_chat_hours')->first();
        $contactFaqUrlSetting = Setting::where('key', 'contact_faq_url')->first();
        $contactFaqTextSetting = Setting::where('key', 'contact_faq_text')->first();
        $contactEmailSupportHoursSetting = Setting::where('key', 'contact_email_support_hours')->first();
        $contactEmailSupportResponseSetting = Setting::where('key', 'contact_email_support_response')->first();
        $contactPhoneSupportDaysSetting = Setting::where('key', 'contact_phone_support_days')->first();
        $contactPhoneSupportTimeSetting = Setting::where('key', 'contact_phone_support_time')->first();
        $contactLiveChatDaysSetting = Setting::where('key', 'contact_live_chat_days')->first();
        $contactLiveChatTimeSetting = Setting::where('key', 'contact_live_chat_time')->first();

        // Set contact information values
        $settings['contact_email'] = ($contactEmailSetting && $contactEmailSetting->value !== null && trim($contactEmailSetting->value) !== '') ? $contactEmailSetting->value : 'support@quizsystem.com';
        $settings['contact_phone'] = ($contactPhoneSetting && $contactPhoneSetting->value !== null && trim($contactPhoneSetting->value) !== '') ? $contactPhoneSetting->value : '+1 (555) 123-4567';
        $settings['contact_phone_hours'] = ($contactPhoneHoursSetting && $contactPhoneHoursSetting->value !== null && trim($contactPhoneHoursSetting->value) !== '') ? $contactPhoneHoursSetting->value : 'Monday - Friday, 9 AM - 6 PM EST';
        $settings['contact_email_response_time'] = ($contactEmailResponseTimeSetting && $contactEmailResponseTimeSetting->value !== null && trim($contactEmailResponseTimeSetting->value) !== '') ? $contactEmailResponseTimeSetting->value : 'We typically respond within 24 hours';
        $settings['contact_live_chat_description'] = ($contactLiveChatDescriptionSetting && $contactLiveChatDescriptionSetting->value !== null && trim($contactLiveChatDescriptionSetting->value) !== '') ? $contactLiveChatDescriptionSetting->value : 'Available on our platform';
        $settings['contact_live_chat_hours'] = ($contactLiveChatHoursSetting && $contactLiveChatHoursSetting->value !== null && trim($contactLiveChatHoursSetting->value) !== '') ? $contactLiveChatHoursSetting->value : 'Get instant help while using the system';
        $settings['contact_faq_url'] = ($contactFaqUrlSetting && $contactFaqUrlSetting->value !== null && trim($contactFaqUrlSetting->value) !== '') ? $contactFaqUrlSetting->value : '#';
        $settings['contact_faq_text'] = ($contactFaqTextSetting && $contactFaqTextSetting->value !== null && trim($contactFaqTextSetting->value) !== '') ? $contactFaqTextSetting->value : 'View FAQ →';
        $settings['contact_email_support_hours'] = ($contactEmailSupportHoursSetting && $contactEmailSupportHoursSetting->value !== null && trim($contactEmailSupportHoursSetting->value) !== '') ? $contactEmailSupportHoursSetting->value : '24/7 Available';
        $settings['contact_email_support_response'] = ($contactEmailSupportResponseSetting && $contactEmailSupportResponseSetting->value !== null && trim($contactEmailSupportResponseSetting->value) !== '') ? $contactEmailSupportResponseSetting->value : 'Response within 24 hours';
        $settings['contact_phone_support_days'] = ($contactPhoneSupportDaysSetting && $contactPhoneSupportDaysSetting->value !== null && trim($contactPhoneSupportDaysSetting->value) !== '') ? $contactPhoneSupportDaysSetting->value : 'Monday - Friday';
        $settings['contact_phone_support_time'] = ($contactPhoneSupportTimeSetting && $contactPhoneSupportTimeSetting->value !== null && trim($contactPhoneSupportTimeSetting->value) !== '') ? $contactPhoneSupportTimeSetting->value : '9:00 AM - 6:00 PM EST';
        $settings['contact_live_chat_days'] = ($contactLiveChatDaysSetting && $contactLiveChatDaysSetting->value !== null && trim($contactLiveChatDaysSetting->value) !== '') ? $contactLiveChatDaysSetting->value : 'Monday - Friday';
        $settings['contact_live_chat_time'] = ($contactLiveChatTimeSetting && $contactLiveChatTimeSetting->value !== null && trim($contactLiveChatTimeSetting->value) !== '') ? $contactLiveChatTimeSetting->value : '10:00 AM - 5:00 PM EST';

        // Email Configuration Settings - reload directly from database
        $mailMailerSetting = Setting::where('key', 'mail_mailer')->first();
        $mailHostSetting = Setting::where('key', 'mail_host')->first();
        $mailPortSetting = Setting::where('key', 'mail_port')->first();
        $mailUsernameSetting = Setting::where('key', 'mail_username')->first();
        $mailPasswordSetting = Setting::where('key', 'mail_password')->first();
        $mailEncryptionSetting = Setting::where('key', 'mail_encryption')->first();
        $mailFromAddressSetting = Setting::where('key', 'mail_from_address')->first();
        $mailFromNameSetting = Setting::where('key', 'mail_from_name')->first();

        if ($mailMailerSetting && $mailMailerSetting->value !== null && $mailMailerSetting->value !== '' && trim($mailMailerSetting->value) !== '') {
            $settings['mail_mailer'] = $mailMailerSetting->value;
        } else {
            $settings['mail_mailer'] = 'log';
        }

        if ($mailHostSetting && $mailHostSetting->value !== null && $mailHostSetting->value !== '' && trim($mailHostSetting->value) !== '') {
            $settings['mail_host'] = $mailHostSetting->value;
        } else {
            $settings['mail_host'] = '';
        }

        if ($mailPortSetting && $mailPortSetting->value !== null && $mailPortSetting->value !== '' && trim($mailPortSetting->value) !== '') {
            $settings['mail_port'] = $mailPortSetting->value;
        } else {
            $settings['mail_port'] = '587';
        }

        if ($mailUsernameSetting && $mailUsernameSetting->value !== null && $mailUsernameSetting->value !== '' && trim($mailUsernameSetting->value) !== '') {
            $settings['mail_username'] = $mailUsernameSetting->value;
        } else {
            $settings['mail_username'] = '';
        }

        // Password - only set if exists (for security, don't display in view)
        if ($mailPasswordSetting && $mailPasswordSetting->value !== null && $mailPasswordSetting->value !== '' && trim($mailPasswordSetting->value) !== '') {
            $settings['mail_password'] = $mailPasswordSetting->value;
        } else {
            $settings['mail_password'] = '';
        }

        if ($mailEncryptionSetting && $mailEncryptionSetting->value !== null && $mailEncryptionSetting->value !== '' && trim($mailEncryptionSetting->value) !== '') {
            $settings['mail_encryption'] = $mailEncryptionSetting->value;
        } else {
            $settings['mail_encryption'] = 'tls';
        }

        if ($mailFromAddressSetting && $mailFromAddressSetting->value !== null && $mailFromAddressSetting->value !== '' && trim($mailFromAddressSetting->value) !== '') {
            $settings['mail_from_address'] = $mailFromAddressSetting->value;
        } else {
            $settings['mail_from_address'] = '';
        }

        if ($mailFromNameSetting && $mailFromNameSetting->value !== null && $mailFromNameSetting->value !== '' && trim($mailFromNameSetting->value) !== '') {
            $settings['mail_from_name'] = $mailFromNameSetting->value;
        } else {
            $settings['mail_from_name'] = '';
        }

        // Hiring Process Configuration Settings - reload directly from database
        $hiringProcessEnabledSetting = Setting::where('key', 'hiring_process_enabled')->first();
        $hiringProcessDescriptionSetting = Setting::where('key', 'hiring_process_description')->first();
        $minimumQuizScoreSetting = Setting::where('key', 'minimum_quiz_score')->first();
        $autoApproveScoreSetting = Setting::where('key', 'auto_approve_score')->first();
        $hiringStagesSetting = Setting::where('key', 'hiring_stages')->first();
        $hiringEmailNotificationsSetting = Setting::where('key', 'hiring_email_notifications')->first();
        $hiringInstructionsSetting = Setting::where('key', 'hiring_instructions')->first();
        $hiringApplicationPublicAccessSetting = Setting::where('key', 'hiring_application_public_access')->first();
        $hiringApplicationUrlSetting = Setting::where('key', 'hiring_application_url')->first();

        // Set hiring process configuration values
        $settings['hiring_process_enabled'] = ($hiringProcessEnabledSetting && $hiringProcessEnabledSetting->value !== null && trim($hiringProcessEnabledSetting->value) !== '') ? $hiringProcessEnabledSetting->value : 'enabled';
        $settings['hiring_process_description'] = ($hiringProcessDescriptionSetting && $hiringProcessDescriptionSetting->value !== null && trim($hiringProcessDescriptionSetting->value) !== '') ? $hiringProcessDescriptionSetting->value : '';
        $settings['minimum_quiz_score'] = ($minimumQuizScoreSetting && $minimumQuizScoreSetting->value !== null && trim($minimumQuizScoreSetting->value) !== '') ? (int) $minimumQuizScoreSetting->value : 70;
        $settings['auto_approve_score'] = ($autoApproveScoreSetting && $autoApproveScoreSetting->value !== null && trim($autoApproveScoreSetting->value) !== '') ? (int) $autoApproveScoreSetting->value : '';
        $settings['hiring_stages'] = ($hiringStagesSetting && $hiringStagesSetting->value !== null && trim($hiringStagesSetting->value) !== '') ? $hiringStagesSetting->value : '';
        $settings['hiring_email_notifications'] = ($hiringEmailNotificationsSetting && $hiringEmailNotificationsSetting->value !== null && trim($hiringEmailNotificationsSetting->value) !== '') ? $hiringEmailNotificationsSetting->value : 'enabled';
        $settings['hiring_instructions'] = ($hiringInstructionsSetting && $hiringInstructionsSetting->value !== null && trim($hiringInstructionsSetting->value) !== '') ? $hiringInstructionsSetting->value : '';
        $settings['hiring_application_public_access'] = ($hiringApplicationPublicAccessSetting && $hiringApplicationPublicAccessSetting->value !== null && trim($hiringApplicationPublicAccessSetting->value) !== '') ? $hiringApplicationPublicAccessSetting->value : 'disabled';
        $settings['hiring_application_url'] = ($hiringApplicationUrlSetting && $hiringApplicationUrlSetting->value !== null && trim($hiringApplicationUrlSetting->value) !== '') ? $hiringApplicationUrlSetting->value : 'hiring/apply';

        // Debug: Log what we're passing to the view - this will help us see what's happening
        \Log::info('Settings Controller - Final values being passed to view', [
            'default_vacation_balance' => $settings['default_vacation_balance'],
            'default_sick_leave_balance' => $settings['default_sick_leave_balance'],
            'vacation_db_value' => $vacationSetting ? $vacationSetting->value : 'NULL',
            'sick_db_value' => $sickSetting ? $sickSetting->value : 'NULL',
            'vacation_db_exists' => $vacationSetting ? 'yes' : 'no',
            'sick_db_exists' => $sickSetting ? 'yes' : 'no',
            'vacation_is_empty' => $vacationSetting && ($vacationSetting->value === null || trim($vacationSetting->value) === '') ? 'yes' : 'no',
            'sick_is_empty' => $sickSetting && ($sickSetting->value === null || trim($sickSetting->value) === '') ? 'yes' : 'no',
            'settings_array_keys' => array_keys($settings),
            'vacation_in_array' => isset($settings['default_vacation_balance']) ? 'YES' : 'NO',
            'sick_in_array' => isset($settings['default_sick_leave_balance']) ? 'YES' : 'NO'
        ]);

        // Get system health information
        $health = $this->getSystemHealth();

        // Pass settings to view - ensure it's passed correctly
        return view('admin.settings.index', [
            'settings' => $settings,
            'health' => $health
        ]);
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
        $validated = $request->validate([
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
            'leave_admin_notification_email' => 'nullable|array',
            'leave_admin_notification_email.*' => 'nullable|email|max:255',
            'default_vacation_balance' => 'nullable|numeric|min:0|max:365',
            // Contact Information
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'contact_phone_hours' => 'nullable|string|max:255',
            'contact_email_response_time' => 'nullable|string|max:255',
            'contact_live_chat_description' => 'nullable|string|max:255',
            'contact_live_chat_hours' => 'nullable|string|max:255',
            'contact_faq_url' => 'nullable|string|max:500',
            'contact_faq_text' => 'nullable|string|max:255',
            'contact_email_support_hours' => 'nullable|string|max:255',
            'contact_email_support_response' => 'nullable|string|max:255',
            'contact_phone_support_days' => 'nullable|string|max:255',
            'contact_phone_support_time' => 'nullable|string|max:255',
            'contact_live_chat_days' => 'nullable|string|max:255',
            'contact_live_chat_time' => 'nullable|string|max:255',
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

        // Handle hiring process settings - save directly from request
        $hiringProcessEnabled = $request->hiring_process_enabled ?? 'enabled';
        Setting::set('hiring_process_enabled', $hiringProcessEnabled, 'text', 'Enable or disable hiring process feature');

        $hiringProcessDescription = $request->hiring_process_description ?? '';
        Setting::set('hiring_process_description', $hiringProcessDescription, 'text', 'Description of the hiring process workflow');

        $minimumQuizScore = $request->minimum_quiz_score ?? '';
        Setting::set('minimum_quiz_score', $minimumQuizScore !== '' ? (int) $minimumQuizScore : 70, 'number', 'Minimum quiz score percentage required to pass');

        $autoApproveScore = $request->auto_approve_score ?? '';
        Setting::set('auto_approve_score', $autoApproveScore !== '' ? (int) $autoApproveScore : null, 'number', 'Quiz score percentage for automatic approval');

        $hiringStages = $request->hiring_stages ?? '';
        Setting::set('hiring_stages', $hiringStages, 'text', 'List of hiring process stages');

        $hiringEmailNotifications = $request->hiring_email_notifications ?? 'enabled';
        Setting::set('hiring_email_notifications', $hiringEmailNotifications, 'text', 'Enable or disable email notifications for hiring process');

        $hiringInstructions = $request->hiring_instructions ?? '';
        Setting::set('hiring_instructions', $hiringInstructions, 'text', 'Instructions displayed to applicants before quiz');

        $hiringApplicationPublicAccess = $request->hiring_application_public_access ?? 'disabled';
        Setting::set('hiring_application_public_access', $hiringApplicationPublicAccess, 'text', 'Enable or disable public access to hiring application form');

        $hiringApplicationUrl = $request->hiring_application_url ?? '';
        // Ensure URL doesn't start with / and is a valid path
        $hiringApplicationUrl = ltrim($hiringApplicationUrl, '/');
        Setting::set('hiring_application_url', $hiringApplicationUrl !== '' ? $hiringApplicationUrl : 'hiring/apply', 'text', 'Custom URL path for hiring application form (e.g., careers, jobs, apply)');

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

        // Handle multiple admin notification emails
        $adminEmails = $request->leave_admin_notification_email ?? [];
        $adminEmails = is_array($adminEmails) ? $adminEmails : [$adminEmails];
        $adminEmails = array_filter(array_map('trim', $adminEmails)); // Remove empty values and trim
        $leaveAdminNotificationEmail = !empty($adminEmails) ? implode(',', $adminEmails) : '';
        Setting::set('leave_admin_notification_email', $leaveAdminNotificationEmail, 'text', 'Email addresses (comma-separated) to receive notifications when employees submit leave requests');

        // Default Leave Balances
        // Always save these values - form fields are always present in the form
        // Get values from request - if empty, use 0
        $vacationBalance = $request->input('default_vacation_balance');
        $sickLeaveBalance = $request->input('default_sick_leave_balance');

        // Save vacation balance - use form value or 0 if empty
        $vacationValue = ($vacationBalance !== null && $vacationBalance !== '')
            ? (float) $vacationBalance
            : 0.0;
        Setting::set('default_vacation_balance', $vacationValue, 'number', 'Default vacation leave balance in days for new employees');

        // Save sick leave balance - use form value or 0 if empty
        $sickValue = ($sickLeaveBalance !== null && $sickLeaveBalance !== '')
            ? (float) $sickLeaveBalance
            : 0.0;
        Setting::set('default_sick_leave_balance', $sickValue, 'number', 'Default sick leave balance in days for new employees');

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

        // Contact Information Settings - save directly from request
        $contactEmail = $request->contact_email ?? '';
        Setting::set('contact_email', $contactEmail, 'text', 'Contact email address');

        $contactPhone = $request->contact_phone ?? '';
        Setting::set('contact_phone', $contactPhone, 'text', 'Contact phone number');

        $contactPhoneHours = $request->contact_phone_hours ?? '';
        Setting::set('contact_phone_hours', $contactPhoneHours, 'text', 'Phone support hours');

        $contactEmailResponseTime = $request->contact_email_response_time ?? '';
        Setting::set('contact_email_response_time', $contactEmailResponseTime, 'text', 'Email response time message');

        $contactLiveChatDescription = $request->contact_live_chat_description ?? '';
        Setting::set('contact_live_chat_description', $contactLiveChatDescription, 'text', 'Live chat description');

        $contactLiveChatHours = $request->contact_live_chat_hours ?? '';
        Setting::set('contact_live_chat_hours', $contactLiveChatHours, 'text', 'Live chat hours description');

        $contactFaqUrl = $request->contact_faq_url ?? '';
        Setting::set('contact_faq_url', $contactFaqUrl, 'text', 'FAQ page URL');

        $contactFaqText = $request->contact_faq_text ?? '';
        Setting::set('contact_faq_text', $contactFaqText, 'text', 'FAQ link text');

        $contactEmailSupportHours = $request->contact_email_support_hours ?? '';
        Setting::set('contact_email_support_hours', $contactEmailSupportHours, 'text', 'Email support hours');

        $contactEmailSupportResponse = $request->contact_email_support_response ?? '';
        Setting::set('contact_email_support_response', $contactEmailSupportResponse, 'text', 'Email support response time');

        $contactPhoneSupportDays = $request->contact_phone_support_days ?? '';
        Setting::set('contact_phone_support_days', $contactPhoneSupportDays, 'text', 'Phone support days');

        $contactPhoneSupportTime = $request->contact_phone_support_time ?? '';
        Setting::set('contact_phone_support_time', $contactPhoneSupportTime, 'text', 'Phone support time');

        $contactLiveChatDays = $request->contact_live_chat_days ?? '';
        Setting::set('contact_live_chat_days', $contactLiveChatDays, 'text', 'Live chat support days');

        $contactLiveChatTime = $request->contact_live_chat_time ?? '';
        Setting::set('contact_live_chat_time', $contactLiveChatTime, 'text', 'Live chat support time');

        // Clear cache to ensure changes are reflected immediately
        // Explicitly clear cache for leave balance settings
        Cache::forget('setting.default_vacation_balance');
        Cache::forget('setting.default_sick_leave_balance');
        // Clear cache for contact information settings
        Cache::forget('setting.contact_email');
        Cache::forget('setting.contact_phone');
        Cache::forget('setting.contact_phone_hours');
        Cache::forget('setting.contact_email_response_time');
        Cache::forget('setting.contact_live_chat_description');
        Cache::forget('setting.contact_live_chat_hours');
        Cache::forget('setting.contact_faq_url');
        Cache::forget('setting.contact_faq_text');
        Cache::forget('setting.contact_email_support_hours');
        Cache::forget('setting.contact_email_support_response');
        Cache::forget('setting.contact_phone_support_days');
        Cache::forget('setting.contact_phone_support_time');
        Cache::forget('setting.contact_live_chat_days');
        Cache::forget('setting.contact_live_chat_time');
        // Clear cache for leave admin notification email
        Cache::forget('setting.leave_admin_notification_email');
        // Clear cache for hiring process configuration settings
        Cache::forget('setting.hiring_process_enabled');
        Cache::forget('setting.hiring_process_description');
        Cache::forget('setting.minimum_quiz_score');
        Cache::forget('setting.auto_approve_score');
        Cache::forget('setting.hiring_stages');
        Cache::forget('setting.hiring_email_notifications');
        Cache::forget('setting.hiring_instructions');
        Cache::forget('setting.hiring_application_public_access');
        Cache::forget('setting.hiring_application_url');
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
