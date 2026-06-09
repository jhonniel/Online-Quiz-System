<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Support\EmployeeDocumentRequestTypes;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

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
        $settings['ojt_total_slots'] = (int) ($settings['ojt_total_slots'] ?? 0);

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

        // Hiring Admin Notification Email
        $hiringAdminNotificationEmailSetting = Setting::where('key', 'hiring_admin_notification_email')->first();
        if ($hiringAdminNotificationEmailSetting && $hiringAdminNotificationEmailSetting->value !== null && trim($hiringAdminNotificationEmailSetting->value) !== '') {
            $settings['hiring_admin_notification_email'] = $hiringAdminNotificationEmailSetting->value;
        } else {
            $settings['hiring_admin_notification_email'] = '';
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
        $socialFacebookSetting = Setting::where('key', 'social_facebook')->first();
        $socialTwitterSetting = Setting::where('key', 'social_twitter')->first();
        $socialLinkedInSetting = Setting::where('key', 'social_linkedin')->first();
        $socialInstagramSetting = Setting::where('key', 'social_instagram')->first();
        $socialYouTubeSetting = Setting::where('key', 'social_youtube')->first();

        // Set contact information values
        $settings['contact_email'] = ($contactEmailSetting && $contactEmailSetting->value !== null && trim($contactEmailSetting->value) !== '') ? $contactEmailSetting->value : 'support@system.com';
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
        // Social Media Links
        $settings['social_facebook'] = ($socialFacebookSetting && $socialFacebookSetting->value !== null && trim($socialFacebookSetting->value) !== '') ? $socialFacebookSetting->value : '';
        $settings['social_twitter'] = ($socialTwitterSetting && $socialTwitterSetting->value !== null && trim($socialTwitterSetting->value) !== '') ? $socialTwitterSetting->value : '';
        $settings['social_linkedin'] = ($socialLinkedInSetting && $socialLinkedInSetting->value !== null && trim($socialLinkedInSetting->value) !== '') ? $socialLinkedInSetting->value : '';
        $settings['social_instagram'] = ($socialInstagramSetting && $socialInstagramSetting->value !== null && trim($socialInstagramSetting->value) !== '') ? $socialInstagramSetting->value : '';
        $settings['social_youtube'] = ($socialYouTubeSetting && $socialYouTubeSetting->value !== null && trim($socialYouTubeSetting->value) !== '') ? $socialYouTubeSetting->value : '';

        // Email Configuration Settings - reload directly from database
        $mailMailerSetting = Setting::where('key', 'mail_mailer')->first();
        $mailHostSetting = Setting::where('key', 'mail_host')->first();
        $mailPortSetting = Setting::where('key', 'mail_port')->first();
        $mailUsernameSetting = Setting::where('key', 'mail_username')->first();
        $mailPasswordSetting = Setting::where('key', 'mail_password')->first();
        $mailEncryptionSetting = Setting::where('key', 'mail_encryption')->first();
        $mailFromAddressSetting = Setting::where('key', 'mail_from_address')->first();
        $mailFromNameSetting = Setting::where('key', 'mail_from_name')->first();
        $mailgunDomainSetting = Setting::where('key', 'mailgun_domain')->first();
        $mailgunSecretSetting = Setting::where('key', 'mailgun_secret')->first();
        $mailgunEndpointSetting = Setting::where('key', 'mailgun_endpoint')->first();

        if ($mailMailerSetting && $mailMailerSetting->value !== null && $mailMailerSetting->value !== '' && trim($mailMailerSetting->value) !== '') {
            $settings['mail_mailer'] = $mailMailerSetting->value;
        } else {
            $settings['mail_mailer'] = (string) env('MAIL_MAILER', 'smtp');
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

        if ($mailgunDomainSetting && $mailgunDomainSetting->value !== null && $mailgunDomainSetting->value !== '' && trim($mailgunDomainSetting->value) !== '') {
            $settings['mailgun_domain'] = $mailgunDomainSetting->value;
        } else {
            $settings['mailgun_domain'] = '';
        }

        if ($mailgunSecretSetting && $mailgunSecretSetting->value !== null && $mailgunSecretSetting->value !== '' && trim($mailgunSecretSetting->value) !== '') {
            $settings['mailgun_secret'] = $mailgunSecretSetting->value;
        } else {
            $settings['mailgun_secret'] = '';
        }

        if ($mailgunEndpointSetting && $mailgunEndpointSetting->value !== null && $mailgunEndpointSetting->value !== '' && trim($mailgunEndpointSetting->value) !== '') {
            $settings['mailgun_endpoint'] = $mailgunEndpointSetting->value;
        } else {
            $settings['mailgun_endpoint'] = 'api.mailgun.net';
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
        $fileStorageStudentAccessSetting = Setting::where('key', 'file_storage_student_access')->first();
        $employeeDocumentsNavSetting = Setting::where('key', 'employee_documents_nav_enabled')->first();

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
        $hiringTorPdfSetting = Setting::where('key', 'hiring_tor_pdf')->first();
        $settings['hiring_tor_pdf'] = ($hiringTorPdfSetting && $hiringTorPdfSetting->value !== null && trim($hiringTorPdfSetting->value) !== '') ? $hiringTorPdfSetting->value : null;
        $privacyPolicyPdfSetting = Setting::where('key', 'privacy_policy_pdf')->first();
        $settings['privacy_policy_pdf'] = ($privacyPolicyPdfSetting && $privacyPolicyPdfSetting->value !== null && trim($privacyPolicyPdfSetting->value) !== '') ? $privacyPolicyPdfSetting->value : null;
        $settings['file_storage_student_access'] = ($fileStorageStudentAccessSetting && $fileStorageStudentAccessSetting->value !== null && trim($fileStorageStudentAccessSetting->value) !== '') ? $fileStorageStudentAccessSetting->value : 'disabled';
        $settings['employee_documents_nav_enabled'] = ($employeeDocumentsNavSetting && $employeeDocumentsNavSetting->value !== null && trim($employeeDocumentsNavSetting->value) !== '') ? $employeeDocumentsNavSetting->value : 'enabled';
        $employeeDocumentP12Setting = Setting::where('key', 'employee_document_p12_path')->first();
        $settings['employee_document_p12_path'] = ($employeeDocumentP12Setting && $employeeDocumentP12Setting->value !== null && trim($employeeDocumentP12Setting->value) !== '') ? $employeeDocumentP12Setting->value : null;

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
            'sick_in_array' => isset($settings['default_sick_leave_balance']) ? 'YES' : 'NO',
        ]);

        $settings['employee_document_request_types'] = EmployeeDocumentRequestTypes::all();

        // Get system health information
        $health = $this->getSystemHealth();

        // Pass settings to view - ensure it's passed correctly
        return view('admin.settings.index', [
            'settings' => $settings,
            'health' => $health,
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
        $health['server']['ram_current_usage'] = 'Unavailable';
        $health['server']['ram_total'] = null;
        $health['server']['ram_used_percent'] = null;

        $ramStats = $this->getSystemRamUsage();
        if (! empty($ramStats)) {
            $health['server']['ram_current_usage'] = $ramStats['used'];
            $health['server']['ram_total'] = $ramStats['total'];
            $health['server']['ram_used_percent'] = $ramStats['used_percent'];
        }

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

        return round($bytes, $precision).' '.$units[$i];
    }

    /**
     * Best-effort system RAM usage (cross-platform with graceful fallback).
     */
    private function getSystemRamUsage(): array
    {
        try {
            $os = PHP_OS_FAMILY;

            if ($os === 'Linux' && is_readable('/proc/meminfo')) {
                $content = @file_get_contents('/proc/meminfo');
                if (is_string($content) && $content !== '') {
                    $mem = [];
                    foreach (preg_split('/\R/', $content) as $line) {
                        if (preg_match('/^(MemTotal|MemAvailable):\s+(\d+)\s+kB$/', trim($line), $m)) {
                            $mem[$m[1]] = (int) $m[2] * 1024;
                        }
                    }

                    if (! empty($mem['MemTotal']) && ! empty($mem['MemAvailable'])) {
                        $total = (float) $mem['MemTotal'];
                        $used = max($total - (float) $mem['MemAvailable'], 0.0);
                        $percent = $total > 0 ? round(($used / $total) * 100, 2) : null;

                        return [
                            'used' => $this->formatBytes($used),
                            'total' => $this->formatBytes($total),
                            'used_percent' => $percent,
                        ];
                    }
                }
            }

            if ($os === 'Darwin' && function_exists('shell_exec')) {
                $totalBytesRaw = @shell_exec('sysctl -n hw.memsize 2>/dev/null');
                $vmStatRaw = @shell_exec('vm_stat 2>/dev/null');
                if (is_string($totalBytesRaw) && is_string($vmStatRaw)) {
                    $total = (float) trim($totalBytesRaw);
                    if ($total > 0) {
                        preg_match('/page size of (\d+) bytes/', $vmStatRaw, $pageSizeMatch);
                        $pageSize = isset($pageSizeMatch[1]) ? (int) $pageSizeMatch[1] : 4096;

                        $freePages = 0;
                        foreach (['Pages free', 'Pages inactive', 'Pages speculative'] as $label) {
                            if (preg_match('/'.preg_quote($label, '/').':\s+(\d+)\./', $vmStatRaw, $m)) {
                                $freePages += (int) $m[1];
                            }
                        }

                        $available = (float) $freePages * $pageSize;
                        $used = max($total - $available, 0.0);
                        $percent = round(($used / $total) * 100, 2);

                        return [
                            'used' => $this->formatBytes($used),
                            'total' => $this->formatBytes($total),
                            'used_percent' => $percent,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore and return empty stats.
        }

        return [];
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
            'hiring_tor_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'privacy_policy_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'file_storage_student_access' => 'nullable|string|in:enabled,disabled',
            'employee_documents_nav_enabled' => 'nullable|string|in:enabled,disabled',
            'employee_document_p12' => 'nullable|file|max:5120|mimes:p12,pfx',
            'overtime_months_credited' => 'nullable|integer|in:12,9,6,3,1',
            'ojt_total_slots' => 'nullable|integer|min:0|max:1000000',
            'leave_immediate_supervisor' => 'nullable|string|max:255',
            'leave_hr_admin' => 'nullable|string|max:255',
            'leave_cto' => 'nullable|string|max:255',
            'leave_admin_notification_email' => 'nullable|array',
            'leave_admin_notification_email.*' => 'nullable|email|max:255',
            'hiring_admin_notification_email' => 'nullable|array',
            'hiring_admin_notification_email.*' => 'nullable|email|max:255',
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
            'contact_address' => 'nullable|string|max:500',
            // Social Media Links
            'social_facebook' => 'nullable|url|max:500',
            'social_twitter' => 'nullable|url|max:500',
            'social_linkedin' => 'nullable|url|max:500',
            'social_instagram' => 'nullable|url|max:500',
            'social_youtube' => 'nullable|url|max:500',
            'interview_reschedule_social_media_link' => 'nullable|url|max:500',
            'default_sick_leave_balance' => 'nullable|numeric|min:0|max:365',
            'employee_document_request_types' => 'nullable|array|max:50',
            'employee_document_request_types.*.label' => 'required_with:employee_document_request_types|string|max:120',
            'employee_document_request_types.*.key' => 'nullable|string|max:80|regex:/^[a-z0-9_]*$/',
            'employee_document_request_types.*.enabled' => 'nullable',
            'employee_document_request_types.*.sort' => 'nullable|integer|min:0|max:999',
            // Landing Page - Employees
            // Email Configuration
            'mail_mailer' => 'nullable|string|in:smtp,sendmail,mailgun,ses,postmark,resend,log,array',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl,null',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
            'mailgun_domain' => 'nullable|string|max:255',
            'mailgun_secret' => 'nullable|string|max:255',
            'mailgun_endpoint' => 'nullable|string|max:255',
            'qr_code_prefix' => 'nullable|string|max:20',
            'app_timezone' => 'nullable|string|max:50',
            // Say-it image generation (Stable Diffusion Web UI / ComfyUI)
            'sayit_image_driver' => 'nullable|string|in:disabled,sdwebui,comfyui',
            'sayit_composer_ai_image_enabled' => 'nullable|string|in:enabled,disabled',
            'sayit_sd_webui_base_url' => 'nullable|string|max:512',
            'sayit_sd_webui_internal_base_url' => 'nullable|string|max:512',
            'sayit_sd_webui_verify_ssl' => 'nullable|string|in:enabled,disabled',
            'sayit_image_http_timeout' => 'nullable|integer|min:30|max:600',
            'sayit_comfyui_base_url' => 'nullable|string|max:512',
            'sayit_comfyui_internal_base_url' => 'nullable|string|max:512',
            'sayit_comfyui_workflow_path' => 'nullable|string|max:1024',
            'sayit_comfyui_prompt_placeholder' => 'nullable|string|max:120',
            'sayit_comfyui_verify_ssl' => 'nullable|string|in:enabled,disabled',
        ]);

        // Update system name
        Setting::set('system_name', $request->system_name, 'text', 'The name of the system');

        // Update system description
        Setting::set('system_description', $request->system_description, 'text', 'System description');

        // Update QR code prefix
        $qrCodePrefix = trim($request->qr_code_prefix ?? 'QR');
        if (empty($qrCodePrefix)) {
            $qrCodePrefix = 'QR';
        }
        Setting::set('qr_code_prefix', $qrCodePrefix, 'text', 'QR code prefix for user IDs');

        // Clear cache to ensure new QR codes use the updated prefix immediately
        \Illuminate\Support\Facades\Cache::forget('setting.qr_code_prefix');

        // Update colors
        if ($request->primary_color) {
            Setting::set('primary_color', $request->primary_color, 'color', 'Primary color for the system');
        }
        if ($request->secondary_color) {
            Setting::set('secondary_color', $request->secondary_color, 'color', 'Secondary color for the system');
        }

        // Use DigitalOcean if configured; otherwise fallback to public disk.
        $digitaloceanConfig = config('filesystems.disks.digitalocean', []);
        $isDigitaloceanConfigured = ! empty($digitaloceanConfig['bucket'])
            && ! empty($digitaloceanConfig['key'])
            && ! empty($digitaloceanConfig['secret'])
            && ! empty($digitaloceanConfig['endpoint']);
        $assetDisk = $isDigitaloceanConfigured ? 'digitalocean' : 'public';
        $assetRoot = $isDigitaloceanConfigured ? trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/') : '';
        $logoDir = $assetRoot ? $assetRoot.'/logos' : 'logos';
        $iconDir = $assetRoot ? $assetRoot.'/icons' : 'icons';

        // Handle logo upload
        if ($request->hasFile('system_logo')) {
            // Delete old logo if exists
            $oldLogo = Setting::get('system_logo');
            if ($oldLogo && Storage::disk($assetDisk)->exists($oldLogo)) {
                Storage::disk($assetDisk)->delete($oldLogo);
            }

            // Store new logo (respect root path prefix)
            $logoPath = $request->file('system_logo')->store($logoDir, $assetDisk);
            Setting::set('system_logo', $logoPath, 'image', 'The system logo');
        }

        // Handle logo removal
        if ($request->has('remove_logo') && $request->remove_logo) {
            $oldLogo = Setting::get('system_logo');
            if ($oldLogo && Storage::disk($assetDisk)->exists($oldLogo)) {
                Storage::disk($assetDisk)->delete($oldLogo);
            }
            Setting::set('system_logo', null, 'image', 'The system logo');
        }

        // Handle icon upload
        if ($request->hasFile('system_icon')) {
            // Delete old icon if exists
            $oldIcon = Setting::get('system_icon');
            if ($oldIcon && Storage::disk($assetDisk)->exists($oldIcon)) {
                Storage::disk($assetDisk)->delete($oldIcon);
            }

            // Store new icon (respect root path prefix)
            $iconPath = $request->file('system_icon')->store($iconDir, $assetDisk);
            Setting::set('system_icon', $iconPath, 'image', 'The system icon/favicon');
        }

        // Handle icon removal
        if ($request->has('remove_icon') && $request->remove_icon) {
            $oldIcon = Setting::get('system_icon');
            if ($oldIcon && Storage::disk($assetDisk)->exists($oldIcon)) {
                Storage::disk($assetDisk)->delete($oldIcon);
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

        // Handle File Storage student access
        $fileStorageStudentAccess = $request->file_storage_student_access ?? 'disabled';
        Setting::set('file_storage_student_access', $fileStorageStudentAccess, 'text', 'File Storage access for students (enabled, disabled)');

        $employeeDocumentsNavEnabled = $request->employee_documents_nav_enabled ?? 'enabled';
        Setting::set('employee_documents_nav_enabled', $employeeDocumentsNavEnabled, 'text', 'Show Documents section in employee navigation (enabled, disabled)');

        $p12Dir = $assetRoot ? $assetRoot.'/employee-document-certificates' : 'employee-document-certificates';
        if ($request->hasFile('employee_document_p12')) {
            $oldP12 = Setting::get('employee_document_p12_path');
            if ($oldP12) {
                foreach ([$assetDisk, 'public', 'local'] as $disk) {
                    try {
                        if (Storage::disk($disk)->exists($oldP12)) {
                            Storage::disk($disk)->delete($oldP12);
                        }
                    } catch (\Throwable) {
                        continue;
                    }
                }
            }

            $p12Path = $request->file('employee_document_p12')->store($p12Dir, $assetDisk);
            Setting::set('employee_document_p12_path', $p12Path, 'file', 'PKCS#12 certificate for employee document digital signatures');
        }

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

        // Handle TOR PDF upload
        $torDir = $assetRoot ? $assetRoot.'/hiring/tor' : 'hiring/tor';

        if ($request->hasFile('hiring_tor_pdf')) {
            $spacesConfig = config('filesystems.disks.spaces', []);
            $isSpacesConfigured = ! empty($spacesConfig['bucket'])
                && ! empty($spacesConfig['key'])
                && ! empty($spacesConfig['secret'])
                && ! empty($spacesConfig['endpoint']);
            $torDisk = $isSpacesConfigured ? 'spaces' : $assetDisk;

            // Delete old TOR PDF if exists
            $oldTorPdf = Setting::get('hiring_tor_pdf');
            if ($oldTorPdf) {
                foreach (['spaces', 'digitalocean', 'public'] as $disk) {
                    try {
                        if (Storage::disk($disk)->exists($oldTorPdf)) {
                            Storage::disk($disk)->delete($oldTorPdf);
                        }
                    } catch (\Throwable $e) {
                        // Continue cleanup on other disks.
                    }
                }
            }

            // Store new TOR PDF (prefer Spaces disk).
            $torPdfPath = $request->file('hiring_tor_pdf')->store($torDir, $torDisk);
            Setting::set('hiring_tor_pdf', $torPdfPath, 'file', 'TOR (Term of Reference) PDF for Internship positions');
        }

        // Handle Privacy Policy PDF upload
        $privacyPolicyDir = $assetRoot ? $assetRoot.'/privacy-policy' : 'privacy-policy';

        if ($request->hasFile('privacy_policy_pdf')) {
            // Delete old Privacy Policy PDF if exists
            $oldPrivacyPolicyPdf = Setting::get('privacy_policy_pdf');
            if ($oldPrivacyPolicyPdf && Storage::disk($assetDisk)->exists($oldPrivacyPolicyPdf)) {
                Storage::disk($assetDisk)->delete($oldPrivacyPolicyPdf);
            }

            // Store new Privacy Policy PDF
            $privacyPolicyPdfPath = $request->file('privacy_policy_pdf')->store($privacyPolicyDir, $assetDisk);
            Setting::set('privacy_policy_pdf', $privacyPolicyPdfPath, 'file', 'Privacy Policy PDF file');
        }

        // Handle overtime credited window (global for all employees)
        if ($request->filled('overtime_months_credited')) {
            $months = (int) $request->overtime_months_credited;
            Setting::set('overtime_months_credited', $months, 'number', 'Months of overtime history credited for balances');

            // Apply to all employees so controllers can read from user model
            User::where('role', 'employee')->update([
                'overtime_months_credited' => $months,
            ]);
        }

        // Global OJT slot capacity (0 means not configured / unlimited display context).
        $ojtTotalSlots = $request->input('ojt_total_slots');
        $ojtTotalSlots = ($ojtTotalSlots !== null && $ojtTotalSlots !== '')
            ? (int) $ojtTotalSlots
            : 0;
        Setting::set('ojt_total_slots', $ojtTotalSlots, 'number', 'Total available OJT slots for student capacity tracking');

        // Leave Request Signatories
        $leaveImmediateSupervisor = $request->leave_immediate_supervisor ?? 'CHARMAINE JOY ROSATACE';
        Setting::set('leave_immediate_supervisor', $leaveImmediateSupervisor, 'text', 'Name for Immediate Supervisor in leave request letters');

        $leaveHrAdmin = $request->leave_hr_admin ?? 'MAY GRACE ACOSTA';
        Setting::set('leave_hr_admin', $leaveHrAdmin, 'text', 'Name for HR Admin in leave request letters');

        $leaveCto = $request->leave_cto ?? 'NITISH KHEMANI';
        Setting::set('leave_cto', $leaveCto, 'text', 'Name for Chief Technology Officer in leave request letters');

        // Handle multiple admin notification emails for leave requests
        $adminEmails = $request->leave_admin_notification_email ?? [];
        $adminEmails = is_array($adminEmails) ? $adminEmails : [$adminEmails];
        $adminEmails = array_filter(array_map('trim', $adminEmails)); // Remove empty values and trim
        $leaveAdminNotificationEmail = ! empty($adminEmails) ? implode(',', $adminEmails) : '';
        Setting::set('leave_admin_notification_email', $leaveAdminNotificationEmail, 'text', 'Email addresses (comma-separated) to receive notifications when employees submit leave requests');

        // Handle multiple admin notification emails for hiring applications
        $hiringAdminEmails = $request->hiring_admin_notification_email ?? [];
        $hiringAdminEmails = is_array($hiringAdminEmails) ? $hiringAdminEmails : [$hiringAdminEmails];
        $hiringAdminEmails = array_filter(array_map('trim', $hiringAdminEmails)); // Remove empty values and trim
        $hiringAdminNotificationEmail = ! empty($hiringAdminEmails) ? implode(',', $hiringAdminEmails) : '';
        Setting::set('hiring_admin_notification_email', $hiringAdminNotificationEmail, 'text', 'Email addresses (comma-separated) to receive notifications when hiring applications are submitted');

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

        if ($request->has('employee_document_request_types')) {
            EmployeeDocumentRequestTypes::persistFromRequest(
                $request->input('employee_document_request_types', [])
            );
        }

        // Email Configuration Settings
        $mailMailer = $request->input('mail_mailer', (string) env('MAIL_MAILER', 'smtp'));
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

        // Mailgun Configuration
        $mailgunDomain = $request->mailgun_domain ?? '';
        Setting::set('mailgun_domain', $mailgunDomain, 'text', 'Mailgun domain');

        if ($request->filled('mailgun_secret')) {
            $mailgunSecret = $request->mailgun_secret;
            Setting::set('mailgun_secret', $mailgunSecret, 'text', 'Mailgun API secret key');
        }

        $mailgunEndpoint = $request->mailgun_endpoint ?? 'api.mailgun.net';
        Setting::set('mailgun_endpoint', $mailgunEndpoint, 'text', 'Mailgun API endpoint');

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

        $contactAddress = $request->contact_address ?? '';
        Setting::set('contact_address', $contactAddress, 'text', 'Company address');

        // Social Media Links
        $socialFacebook = $request->social_facebook ?? '';
        Setting::set('social_facebook', $socialFacebook, 'text', 'Facebook page URL');

        $socialTwitter = $request->social_twitter ?? '';
        Setting::set('social_twitter', $socialTwitter, 'text', 'Twitter/X profile URL');

        $socialLinkedIn = $request->social_linkedin ?? '';
        Setting::set('social_linkedin', $socialLinkedIn, 'text', 'LinkedIn company/profile URL');

        $socialInstagram = $request->social_instagram ?? '';
        Setting::set('social_instagram', $socialInstagram, 'text', 'Instagram profile URL');

        $socialYouTube = $request->social_youtube ?? '';
        Setting::set('social_youtube', $socialYouTube, 'text', 'YouTube channel URL');

        $interviewRescheduleSocialMediaLink = $request->interview_reschedule_social_media_link ?? '';
        Setting::set('interview_reschedule_social_media_link', $interviewRescheduleSocialMediaLink, 'text', 'Social media link for interview reschedule requests');

        // Say-it (confession board) — AI image generation
        $sayitDriver = $request->sayit_image_driver ?? 'disabled';
        Setting::set('sayit_image_driver', $sayitDriver, 'text', 'Say-it image driver: disabled, sdwebui, or comfyui');

        $sayitComposerAiImage = $request->sayit_composer_ai_image_enabled ?? 'enabled';
        if (! in_array($sayitComposerAiImage, ['enabled', 'disabled'], true)) {
            $sayitComposerAiImage = 'enabled';
        }
        Setting::set(
            'sayit_composer_ai_image_enabled',
            $sayitComposerAiImage,
            'text',
            'Say-it: show Generate image on composer (enabled/disabled)'
        );

        $sayitSdUrl = trim((string) ($request->sayit_sd_webui_base_url ?? ''));
        Setting::set('sayit_sd_webui_base_url', $sayitSdUrl, 'text', 'Say-it: Stable Diffusion Web UI base URL (no trailing slash)');

        $sayitSdInternal = trim((string) ($request->sayit_sd_webui_internal_base_url ?? ''));
        Setting::set(
            'sayit_sd_webui_internal_base_url',
            $sayitSdInternal,
            'text',
            'Say-it: SD Web UI internal API URL (used for HTTP when set; e.g. http://127.0.0.1:7860)'
        );

        $sayitSdVerify = $request->sayit_sd_webui_verify_ssl ?? 'enabled';
        Setting::set('sayit_sd_webui_verify_ssl', $sayitSdVerify, 'text', 'Say-it: verify SSL for SD Web UI (enabled/disabled)');

        $sayitTimeout = $request->sayit_image_http_timeout;
        Setting::set(
            'sayit_image_http_timeout',
            $sayitTimeout !== null && $sayitTimeout !== '' ? (string) max(30, min(600, (int) $sayitTimeout)) : '',
            'text',
            'Say-it: HTTP timeout seconds for image generation APIs'
        );

        $sayitComfyUrl = trim((string) ($request->sayit_comfyui_base_url ?? ''));
        Setting::set('sayit_comfyui_base_url', $sayitComfyUrl, 'text', 'Say-it: ComfyUI base URL (no trailing slash)');

        $sayitComfyInternal = trim((string) ($request->sayit_comfyui_internal_base_url ?? ''));
        Setting::set(
            'sayit_comfyui_internal_base_url',
            $sayitComfyInternal,
            'text',
            'Say-it: ComfyUI internal API URL (used for HTTP when set)'
        );

        $sayitComfyWorkflow = trim((string) ($request->sayit_comfyui_workflow_path ?? ''));
        Setting::set('sayit_comfyui_workflow_path', $sayitComfyWorkflow, 'text', 'Say-it: ComfyUI API workflow JSON path (absolute or relative to project root)');

        $sayitComfyPlaceholder = trim((string) ($request->sayit_comfyui_prompt_placeholder ?? '__SAYIT_PROMPT__'));
        Setting::set(
            'sayit_comfyui_prompt_placeholder',
            $sayitComfyPlaceholder !== '' ? $sayitComfyPlaceholder : '__SAYIT_PROMPT__',
            'text',
            'Say-it: placeholder string in ComfyUI workflow JSON for prompt injection'
        );

        $sayitComfyVerify = $request->sayit_comfyui_verify_ssl ?? 'enabled';
        Setting::set('sayit_comfyui_verify_ssl', $sayitComfyVerify, 'text', 'Say-it: verify SSL for ComfyUI (enabled/disabled)');

        // Update Application Timezone
        if ($request->filled('app_timezone')) {
            $timezone = $request->app_timezone;
            // Validate timezone
            try {
                new \DateTimeZone($timezone);
                // Update config file
                $configPath = config_path('app.php');
                $configContent = file_get_contents($configPath);
                // Replace timezone in config file
                $configContent = preg_replace(
                    "/'timezone'\s*=>\s*['\"][^'\"]*['\"]/",
                    "'timezone' => '{$timezone}'",
                    $configContent
                );
                file_put_contents($configPath, $configContent);
                // Clear config cache
                \Artisan::call('config:clear');
            } catch (\Exception $e) {
                \Log::error('Invalid timezone provided: '.$timezone);
            }
        }

        // Clear cache to ensure changes are reflected immediately
        // Explicitly clear cache for leave balance settings
        Cache::forget('setting.default_vacation_balance');
        Cache::forget('setting.default_sick_leave_balance');
        Cache::forget('setting.ojt_total_slots');
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
        // Clear cache for social media settings
        Cache::forget('setting.social_facebook');
        Cache::forget('setting.social_twitter');
        Cache::forget('setting.social_linkedin');
        Cache::forget('setting.social_instagram');
        Cache::forget('setting.social_youtube');
        // Clear cache for landing page settings
        for ($i = 1; $i <= 4; $i++) {
            Cache::forget("setting.employee_{$i}_name");
            Cache::forget("setting.employee_{$i}_position");
            Cache::forget("setting.employee_{$i}_image");
        }
        for ($i = 1; $i <= 3; $i++) {
            Cache::forget("setting.project_{$i}_name");
            Cache::forget("setting.project_{$i}_description");
            Cache::forget("setting.project_{$i}_image");
            Cache::forget("setting.project_{$i}_url");
        }
        // Clear cache for leave admin notification email
        Cache::forget('setting.leave_admin_notification_email');
        // Clear cache for hiring admin notification email
        Cache::forget('setting.hiring_admin_notification_email');
        // Clear cache for hiring process configuration settings
        Cache::forget('setting.hiring_process_enabled');
        Cache::forget('setting.hiring_process_description');
        Cache::forget('setting.minimum_quiz_score');
        Cache::forget('setting.auto_approve_score');
        Cache::forget('setting.hiring_stages');
        Cache::forget('setting.hiring_email_notifications');
        Cache::forget('setting.hiring_instructions');
        Cache::forget('setting.student_rules_regulations_html');
        Cache::forget('setting.hiring_application_public_access');
        Cache::forget('setting.hiring_application_url');
        Cache::forget('setting.hiring_tor_pdf');
        Cache::forget('setting.privacy_policy_pdf');
        Cache::forget('setting.file_storage_student_access');
        Cache::forget('setting.employee_documents_nav_enabled');
        Cache::forget('setting.employee_document_p12_path');
        foreach ([
            'sayit_image_driver',
            'sayit_composer_ai_image_enabled',
            'sayit_sd_webui_base_url',
            'sayit_sd_webui_internal_base_url',
            'sayit_sd_webui_verify_ssl',
            'sayit_image_http_timeout',
            'sayit_comfyui_base_url',
            'sayit_comfyui_internal_base_url',
            'sayit_comfyui_workflow_path',
            'sayit_comfyui_prompt_placeholder',
            'sayit_comfyui_verify_ssl',
        ] as $sayitKey) {
            Cache::forget("setting.{$sayitKey}");
        }
        Setting::clearCache();

        return redirect('/admin/settings')
            ->with('success', 'Settings updated successfully.');
    }

    /**
     * Dedicated editor for student RULES AND REGULATIONS modal body (System → Rules).
     */
    public function rulesRegulations()
    {
        $html = (string) Setting::get('student_rules_regulations_html', '');
        $defaultRulesHtml = View::make('components.student-rules-regulations-default-body')->render();
        $meritAutoEnabled = (string) Setting::get('student_merit_auto_notices_enabled', 'enabled') === 'enabled';
        $meritWarningThreshold = max(1, (int) Setting::get('student_merit_violation_warning_threshold', 1));
        $meritFinalThreshold = max(1, (int) Setting::get('student_merit_final_notice_threshold', 3));

        return view('admin.settings.rules-regulations', [
            'student_rules_regulations_html' => $html,
            'default_rules_html' => $defaultRulesHtml,
            'has_custom_rules' => trim($html) !== '',
            'merit_auto_notices_enabled' => $meritAutoEnabled,
            'merit_violation_warning_threshold' => $meritWarningThreshold,
            'merit_final_notice_threshold' => $meritFinalThreshold,
        ]);
    }

    public function updateMeritNoticeSettings(Request $request)
    {
        $request->validate([
            'student_merit_auto_notices_enabled' => 'required|in:enabled,disabled',
            'student_merit_violation_warning_threshold' => 'required|integer|min:1|max:999',
            'student_merit_final_notice_threshold' => 'required|integer|min:1|max:999',
        ]);

        $warning = (int) $request->input('student_merit_violation_warning_threshold');
        $final = (int) $request->input('student_merit_final_notice_threshold');

        if ($final <= $warning) {
            return back()
                ->withErrors([
                    'student_merit_final_notice_threshold' => 'Final notice threshold must be greater than the violation warning threshold.',
                ])
                ->withInput();
        }

        Setting::set(
            'student_merit_auto_notices_enabled',
            $request->input('student_merit_auto_notices_enabled'),
            'text',
            'Enable automatic student rules notices from merit counts (enabled or disabled)'
        );
        Setting::set(
            'student_merit_violation_warning_threshold',
            (string) $warning,
            'number',
            'Minimum total merits to auto-enable rules violation warning'
        );
        Setting::set(
            'student_merit_final_notice_threshold',
            (string) $final,
            'number',
            'Minimum total merits to auto-enable final notice (scrolling banner)'
        );

        Cache::forget('setting.student_merit_auto_notices_enabled');
        Cache::forget('setting.student_merit_violation_warning_threshold');
        Cache::forget('setting.student_merit_final_notice_threshold');
        Setting::clearCache();

        return redirect('/admin/system/rules')
            ->with('success', 'Merit-based rules notice settings saved.');
    }

    /**
     * Save HTML body shown under "RULES AND REGULATIONS" in the student agreement modal.
     */
    public function updateRulesRegulations(Request $request)
    {
        $request->validate([
            'student_rules_regulations_html' => 'nullable|string|max:65000',
        ]);

        $html = (string) ($request->input('student_rules_regulations_html') ?? '');
        Setting::set('student_rules_regulations_html', $html, 'text', 'HTML body for student rules and regulations login modal');

        Cache::forget('setting.student_rules_regulations_html');
        Setting::clearCache();

        return redirect('/admin/system/rules')
            ->with('success', 'RULES AND REGULATIONS body saved to the database. Students will see updates the next time the agreement modal is shown.');
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
     * Get lightweight live traffic + suspicious activity metrics via AJAX.
     *
     * These are heuristics intended for monitoring, not definitive intrusion detection.
     */
    public function getHealthMetrics()
    {
        $buckets = Cache::get(\App\Support\SystemHealthMetricsStore::RECENT_BUCKETS_KEY, []);
        if (! is_array($buckets)) {
            $buckets = [];
        }

        // Keep last 10 minutes
        $buckets = array_slice($buckets, -10);

        $sum = [
            'requests' => 0,
            'reads' => 0,
            'writes' => 0,
            'errors_5xx' => 0,
            'not_found' => 0,
            'rate_limited' => 0,
            'auth_denied' => 0,
        ];

        $perMinute = [];
        $ip404 = [];
        $ipDenied = [];
        $ipRateLimited = [];
        $path404 = [];

        foreach ($buckets as $bucket) {
            $data = \App\Support\SystemHealthMetricsStore::loadBucket((string) $bucket);

            $reads = (int) ($data['reads'] ?? 0);
            $writes = (int) ($data['writes'] ?? 0);
            $requests = (int) ($data['requests'] ?? 0);
            $errors5xx = (int) ($data['errors_5xx'] ?? 0);
            $notFound = (int) ($data['not_found'] ?? 0);
            $rateLimited = (int) ($data['rate_limited'] ?? 0);
            $authDenied = (int) ($data['auth_denied'] ?? 0);

            $sum['requests'] += $requests;
            $sum['reads'] += $reads;
            $sum['writes'] += $writes;
            $sum['errors_5xx'] += $errors5xx;
            $sum['not_found'] += $notFound;
            $sum['rate_limited'] += $rateLimited;
            $sum['auth_denied'] += $authDenied;

            $perMinute[] = [
                'bucket' => $bucket,
                'reads' => $reads,
                'writes' => $writes,
                'requests' => $requests,
                'errors_5xx' => $errors5xx,
                'not_found' => $notFound,
                'rate_limited' => $rateLimited,
                'auth_denied' => $authDenied,
            ];

            $ip404 = $this->mergeCountMaps($ip404, $data['ip_404'] ?? []);
            $ipDenied = $this->mergeCountMaps($ipDenied, $data['ip_auth_denied'] ?? []);
            $ipRateLimited = $this->mergeCountMaps($ipRateLimited, $data['ip_rate_limited'] ?? []);
            $path404 = $this->mergeCountMaps($path404, $data['path_404'] ?? []);
        }

        arsort($ip404);
        arsort($ipDenied);
        arsort($ipRateLimited);
        arsort($path404);

        return response()->json([
            'window_minutes' => count($buckets),
            'totals' => $sum,
            'per_minute' => $perMinute,
            'top' => [
                'ip_404' => array_slice($ip404, 0, 10, true),
                'ip_auth_denied' => array_slice($ipDenied, 0, 10, true),
                'ip_rate_limited' => array_slice($ipRateLimited, 0, 10, true),
                'path_404' => array_slice($path404, 0, 10, true),
            ],
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    private function mergeCountMaps(array $base, $incoming): array
    {
        if (! is_array($incoming)) {
            return $base;
        }

        foreach ($incoming as $k => $v) {
            $base[(string) $k] = (int) (($base[(string) $k] ?? 0) + (int) $v);
        }

        return $base;
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

            // Ensure mail configuration is up to date from settings and purge cached mailer so new config is used
            MailConfigService::configure();
            $mailer = config('mail.default');
            if (! is_string($mailer) || $mailer === '') {
                $mailer = 'log';
            }
            try {
                app('mail.manager')->purge($mailer);
            } catch (\Throwable $e) {
                // Purge can fail if driver not resolved yet; continue
            }

            // With "log" driver no real email is sent — require SMTP (or another real mailer) for test
            if ($mailer === 'log' || $mailer === 'array') {
                return response()->json([
                    'success' => false,
                    'message' => 'Test email cannot send with the "log" or "array" driver. Save Mail settings with Mailer set to "smtp" or "mailgun", configure the required fields, and try again.',
                ], 422);
            }

            if ($mailer === 'smtp') {
                $host = config('mail.mailers.smtp.host');
                $port = config('mail.mailers.smtp.port');
                $username = config('mail.mailers.smtp.username');
                $password = config('mail.mailers.smtp.password');
                $fromAddress = config('mail.from.address');

                if (empty($host) || empty($port) || empty($username) || empty($password) || empty($fromAddress)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'SMTP configuration is incomplete. Please set Mailer to "smtp", then fill Host, Port, Username, Password, and From Address, save, and try again.',
                    ], 422);
                }
            }

            if ($mailer === 'mailgun') {
                $mailgunDomain = config('services.mailgun.domain');
                $mailgunSecret = config('services.mailgun.secret');
                $fromAddress = config('mail.from.address');

                if (empty($mailgunDomain) || empty($mailgunSecret) || empty($fromAddress)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mailgun configuration is incomplete. Please set Mailer to "mailgun", then fill Mailgun Domain, Mailgun Secret Key, and From Address, save, and try again.',
                    ], 422);
                }
            }

            // Log config for debugging
            Log::info('Test email attempt', [
                'mailer' => $mailer,
                'to' => $testEmail,
                'from' => config('mail.from.address'),
                'smtp_host' => $mailer === 'smtp' ? config('mail.mailers.smtp.host') : null,
                'mailgun_domain' => $mailer === 'mailgun' ? config('services.mailgun.domain') : null,
            ]);

            // Attempt to send the email synchronously (use configured from for envelope)
            $fromAddress = config('mail.from.address');
            $fromName = config('mail.from.name');
            if (! $fromAddress) {
                $fromAddress = 'noreply@'.(parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST) ?: 'localhost');
                $fromName = $fromName ?: config('app.name', 'Laravel');
            }
            Mail::to($testEmail)->send((new \App\Mail\TestEmail)->from($fromAddress, $fromName));

            // If the underlying mailer exposes failures, check them as an extra safety net
            try {
                $mailerInstance = Mail::getFacadeRoot();
                if (is_object($mailerInstance) && method_exists($mailerInstance, 'failures')) {
                    $failures = $mailerInstance->failures();
                    if (! empty($failures)) {
                        Log::error('Test email reported transport failures', ['failures' => $failures]);

                        return response()->json([
                            'success' => false,
                            'message' => 'The mailer reported a delivery problem for: '.implode(', ', $failures).'. Check credentials and try again.',
                        ], 500);
                    }
                }
            } catch (\Throwable $t) {
                // If the method is not available (newer mailer), ignore
            }

            Log::info('Test email sent successfully', [
                'to' => $testEmail,
                'mailer' => $mailer,
                'from' => config('mail.from.address'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test email was sent successfully to '.$testEmail.'. If the recipient does not see it: check the Spam/Junk folder, confirm the address is correct, and ensure your sending domain (From address) has SPF and DKIM set up in DNS.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Test email failed', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: '.$e->getMessage().' (Check logs for details)',
            ], 500);
        }
    }
}
