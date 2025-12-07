<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Setting;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share settings with all views
        // IMPORTANT: Merge with existing settings if they exist, don't overwrite
        View::composer('*', function ($view) {
            // Get existing settings from view data if they exist
            $existingSettings = $view->getData()['settings'] ?? [];
            
            // Base settings that should be available in all views
            $baseSettings = [
                'system_name' => Setting::get('system_name', 'Quiz System'),
                'system_logo' => Setting::get('system_logo'),
                'system_icon' => Setting::get('system_icon'),
                'system_description' => Setting::get('system_description', 'Online Quiz Management System'),
                'primary_color' => Setting::get('primary_color', '#4F46E5'),
                'secondary_color' => Setting::get('secondary_color', '#6B7280'),
                'hiring_application_public_access' => Setting::get('hiring_application_public_access', 'disabled'),
                'hiring_application_url' => Setting::get('hiring_application_url', 'hiring/apply'),
                // Contact Information
                'contact_email' => Setting::get('contact_email', 'support@quizsystem.com'),
                'contact_phone' => Setting::get('contact_phone', '+1 (555) 123-4567'),
                'contact_phone_hours' => Setting::get('contact_phone_hours', 'Monday - Friday, 9 AM - 6 PM EST'),
                'contact_email_response_time' => Setting::get('contact_email_response_time', 'We typically respond within 24 hours'),
                'contact_live_chat_description' => Setting::get('contact_live_chat_description', 'Available on our platform'),
                'contact_live_chat_hours' => Setting::get('contact_live_chat_hours', 'Get instant help while using the system'),
                'contact_faq_url' => Setting::get('contact_faq_url', '#'),
                'contact_faq_text' => Setting::get('contact_faq_text', 'View FAQ →'),
                'contact_email_support_hours' => Setting::get('contact_email_support_hours', '24/7 Available'),
                'contact_email_support_response' => Setting::get('contact_email_support_response', 'Response within 24 hours'),
                'contact_phone_support_days' => Setting::get('contact_phone_support_days', 'Monday - Friday'),
                'contact_phone_support_time' => Setting::get('contact_phone_support_time', '9:00 AM - 6:00 PM EST'),
                'contact_live_chat_days' => Setting::get('contact_live_chat_days', 'Monday - Friday'),
                'contact_live_chat_time' => Setting::get('contact_live_chat_time', '10:00 AM - 5:00 PM EST'),
            ];
            
            // Merge existing settings (from controller) with base settings
            // Existing settings take precedence to preserve controller values
            $mergedSettings = array_merge($baseSettings, $existingSettings);
            
            $view->with('settings', $mergedSettings);
        });
    }
}
