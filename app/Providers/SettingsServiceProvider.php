<?php

namespace App\Providers;

use App\Models\News;
use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer('*', function ($view) {
            static $baseSettings = null;
            static $teacherUnreadAnnouncementsCount = null;

            if ($baseSettings === null) {
                $baseSettings = self::buildBaseSettings();
            }

            $existingSettings = $view->getData()['settings'] ?? [];
            $view->with('settings', array_merge($baseSettings, $existingSettings));

            if ($teacherUnreadAnnouncementsCount === null) {
                $teacherUnreadAnnouncementsCount = self::resolveTeacherUnreadAnnouncementsCount();
            }

            $view->with('teacherUnreadAnnouncementsCount', $teacherUnreadAnnouncementsCount);
        });

        View::composer('layouts.admin', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                if (! $user->relationLoaded('adminPermission')) {
                    $user->load('adminPermission');
                }
            }
        });
    }

    /** @return array<string, mixed> */
    private static function buildBaseSettings(): array
    {
        $parsed = Setting::parsedSettings();

        $get = static function (string $key, mixed $default = null) use ($parsed): mixed {
            if (! array_key_exists($key, $parsed) || $parsed[$key] === null) {
                return $default;
            }

            return $parsed[$key];
        };

        $buildUrl = static function (?string $path): ?string {
            if (empty($path) || $path === 'not found' || trim($path) === '' || trim($path) === '/') {
                return null;
            }

            $imagePath = trim($path);

            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                return $imagePath;
            }

            if (strlen($imagePath) < 2 || $imagePath === '/' || $imagePath === '\\') {
                return null;
            }

            $encodedPath = base64_encode($imagePath);
            $urlEncodedPath = str_replace(['+', '/', '='], ['%2B', '%2F', '%3D'], $encodedPath);

            return url('/image-proxy/'.$urlEncodedPath);
        };

        $systemLogo = $get('system_logo');

        return [
            'system_name' => $get('system_name', 'System'),
            'system_logo' => $systemLogo,
            'system_icon' => $get('system_icon'),
            'system_logo_url' => $buildUrl(is_string($systemLogo) ? $systemLogo : null),
            'system_icon_url' => $buildUrl(is_string($get('system_icon')) ? $get('system_icon') : null),
            'system_description' => $get('system_description', 'Online Management System'),
            'primary_color' => $get('primary_color', '#4F46E5'),
            'secondary_color' => $get('secondary_color', '#6B7280'),
            'hiring_application_public_access' => $get('hiring_application_public_access', 'enabled'),
            'hiring_application_url' => $get('hiring_application_url', 'hiring/apply'),
            'contact_email' => $get('contact_email', 'support@system.com'),
            'contact_phone' => $get('contact_phone', '+1 (555) 123-4567'),
            'contact_phone_hours' => $get('contact_phone_hours', 'Monday - Friday, 9 AM - 6 PM EST'),
            'contact_email_response_time' => $get('contact_email_response_time', 'We typically respond within 24 hours'),
            'contact_live_chat_description' => $get('contact_live_chat_description', 'Available on our platform'),
            'contact_live_chat_hours' => $get('contact_live_chat_hours', 'Get instant help while using the system'),
            'contact_faq_url' => $get('contact_faq_url', '#'),
            'contact_faq_text' => $get('contact_faq_text', 'View FAQ →'),
            'contact_email_support_hours' => $get('contact_email_support_hours', '24/7 Available'),
            'contact_email_support_response' => $get('contact_email_support_response', 'Response within 24 hours'),
            'contact_phone_support_days' => $get('contact_phone_support_days', 'Monday - Friday'),
            'contact_phone_support_time' => $get('contact_phone_support_time', '9:00 AM - 6:00 PM EST'),
            'social_facebook' => $get('social_facebook', ''),
            'social_twitter' => $get('social_twitter', ''),
            'social_linkedin' => $get('social_linkedin', ''),
            'social_instagram' => $get('social_instagram', ''),
            'social_youtube' => $get('social_youtube', ''),
            'contact_live_chat_days' => $get('contact_live_chat_days', 'Monday - Friday'),
            'contact_live_chat_time' => $get('contact_live_chat_time', '10:00 AM - 5:00 PM EST'),
            'student_rules_regulations_html' => $get('student_rules_regulations_html', ''),
        ];
    }

    private static function resolveTeacherUnreadAnnouncementsCount(): int
    {
        if (! auth()->check() || auth()->user()->role !== 'teacher') {
            return 0;
        }

        $lastSeenAt = auth()->user()->teacher_announcements_seen_at;

        $query = News::query()
            ->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });

        if ($lastSeenAt) {
            $query->where(function ($q) use ($lastSeenAt) {
                $q->where(function ($withPublishedAt) use ($lastSeenAt) {
                    $withPublishedAt->whereNotNull('published_at')
                        ->where('published_at', '>', $lastSeenAt);
                })->orWhere(function ($withoutPublishedAt) use ($lastSeenAt) {
                    $withoutPublishedAt->whereNull('published_at')
                        ->where('created_at', '>', $lastSeenAt);
                });
            });
        }

        return (int) $query->count();
    }
}
