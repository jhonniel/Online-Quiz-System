<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
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

            // Helper to build URL with DigitalOcean disk first, fallback to public/local
            $buildUrl = function ($path) {
                if (!$path) {
                    return null;
                }

                // Try digitalocean disk only if configured
                try {
                    $doConfig = config('filesystems.disks.digitalocean', []);
                    if (!empty($doConfig['bucket']) && !empty($doConfig['key']) && !empty($doConfig['secret'])) {
                        return Storage::disk('digitalocean')->url($path);
                    }
                } catch (\Throwable $e) {
                    // Ignore and fallback
                }

                // Fallback to public disk if file exists
                try {
                    if (Storage::disk('public')->exists($path)) {
                        return Storage::disk('public')->url($path);
                    }
                } catch (\Throwable $e) {
                    // Ignore and fallback
                }

                // Last resort: Storage::url (may use default disk)
                try {
                    return Storage::url($path);
                } catch (\Throwable $e) {
                    return null;
                }
            };

            // Base settings that should be available in all views
            $baseSettings = [
                'system_name' => Setting::get('system_name', 'System'),
                'system_logo' => Setting::get('system_logo'),
                'system_icon' => Setting::get('system_icon'),
                'system_logo_url' => $buildUrl(Setting::get('system_logo')),
                'system_icon_url' => $buildUrl(Setting::get('system_icon')),
                'system_description' => Setting::get('system_description', 'Online Management System'),
                'primary_color' => Setting::get('primary_color', '#4F46E5'),
                'secondary_color' => Setting::get('secondary_color', '#6B7280'),
                'hiring_application_public_access' => Setting::get('hiring_application_public_access', 'disabled'),
                'hiring_application_url' => Setting::get('hiring_application_url', 'hiring/apply'),
                // Contact Information
                'contact_email' => Setting::get('contact_email', 'support@system.com'),
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
                // Social Media Links
                'social_facebook' => Setting::get('social_facebook', ''),
                'social_twitter' => Setting::get('social_twitter', ''),
                'social_linkedin' => Setting::get('social_linkedin', ''),
                'social_instagram' => Setting::get('social_instagram', ''),
                'social_youtube' => Setting::get('social_youtube', ''),
                'contact_live_chat_days' => Setting::get('contact_live_chat_days', 'Monday - Friday'),
                'contact_live_chat_time' => Setting::get('contact_live_chat_time', '10:00 AM - 5:00 PM EST'),
            ];

            // Merge existing settings (from controller) with base settings
            // Existing settings take precedence to preserve controller values
            $mergedSettings = array_merge($baseSettings, $existingSettings);

            $view->with('settings', $mergedSettings);
        });

        // Ensure adminPermission relationship is loaded for authenticated users in admin views
        View::composer('layouts.admin', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                if (!$user->relationLoaded('adminPermission')) {
                    $user->load('adminPermission');
                }
            }
        });
    }
}
