<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;

class MailConfigService
{
    /**
     * Configure mail settings from database, falling back to .env when DB values are missing.
     * You can configure mail either in Admin → Settings (database) or in .env (MAIL_* variables).
     */
    public static function configure()
    {
        // Mailer: DB first, then .env (default smtp)
        $mailer = static::normalizeString(Setting::get('mail_mailer', ''), '');
        if ($mailer === '') {
            $mailer = (string) env('MAIL_MAILER', 'smtp');
        }

        // SMTP settings: DB first, then .env
        $host = static::normalizeString(Setting::get('mail_host', ''), '');
        if ($host === '' && $mailer === 'smtp') {
            $host = (string) env('MAIL_HOST', '');
        }
        $port = Setting::get('mail_port', null);
        if ($port === null || $port === '') {
            $port = env('MAIL_PORT', 587);
        }
        $port = is_numeric($port) ? (int) $port : 587;
        if ($port === 0) {
            $port = 587;
        }

        $username = static::normalizeString(Setting::get('mail_username', ''), '');
        if ($username === '' && $mailer === 'smtp') {
            $username = (string) env('MAIL_USERNAME', '');
        }
        $password = static::normalizeString(Setting::get('mail_password', ''), '');
        if ($password === '' && $mailer === 'smtp') {
            $password = (string) env('MAIL_PASSWORD', '');
        }
        $encryption = static::normalizeString(Setting::get('mail_encryption', ''), '');
        if ($encryption === '' && $mailer === 'smtp') {
            $encryption = (string) env('MAIL_ENCRYPTION', 'tls');
        }
        if ($encryption === '' || $encryption === 'null') {
            $encryption = 'tls';
        }

        $fromAddress = static::normalizeString(Setting::get('mail_from_address', ''), '');
        if ($fromAddress === '' && $mailer === 'smtp') {
            $fromAddress = (string) env('MAIL_FROM_ADDRESS', '');
        }
        $fromName = static::normalizeString(Setting::get('mail_from_name', ''), '');
        if ($fromName === '' && $mailer === 'smtp') {
            $fromName = (string) env('MAIL_FROM_NAME', config('app.name', ''));
        }

        // Set default mailer
        Config::set('mail.default', $mailer);

        // Configure SMTP if mailer is SMTP
        if ($mailer === 'smtp') {
            Config::set('mail.mailers.smtp.host', $host);
            Config::set('mail.mailers.smtp.port', $port);
            Config::set('mail.mailers.smtp.username', $username);
            Config::set('mail.mailers.smtp.password', $password);
            Config::set('mail.mailers.smtp.timeout', 30);
            // Port 465 = implicit SSL (smtps). Use 'ssl' encryption and 'smtps' scheme so the connection is correct.
            if ((int) $port === 465) {
                Config::set('mail.mailers.smtp.encryption', 'ssl');
                Config::set('mail.mailers.smtp.scheme', 'smtps');
            } else {
                Config::set('mail.mailers.smtp.encryption', $encryption === 'null' ? null : $encryption);
            }
        }
        // Configure Mailgun if mailer is Mailgun
        if ($mailer === 'mailgun') {
            $mailgunDomain = static::normalizeString(Setting::get('mailgun_domain', ''), '');
            $mailgunSecret = static::normalizeString(Setting::get('mailgun_secret', ''), '');
            $mailgunEndpoint = static::normalizeString(Setting::get('mailgun_endpoint', 'api.mailgun.net'), 'api.mailgun.net');
            
            if ($mailgunDomain && $mailgunSecret) {
                Config::set('services.mailgun.domain', $mailgunDomain);
                Config::set('services.mailgun.secret', $mailgunSecret);
                Config::set('services.mailgun.endpoint', $mailgunEndpoint);
            }
        }

        // Set from address and name (required for valid envelope; use safe defaults if missing)
        $fromAddress = $fromAddress ?: 'noreply@' . (parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST) ?: 'localhost');
        $fromName = $fromName ?: config('app.name', 'Laravel');
        Config::set('mail.from.address', $fromAddress);
        Config::set('mail.from.name', $fromName);
    }

    /**
     * Normalize a setting value to string (Setting model uses 'array' cast which can return null for non-JSON values).
     */
    protected static function normalizeString($value, string $default): string
    {
        if (is_string($value)) {
            $value = trim($value);

            return $value !== '' ? $value : $default;
        }
        if (is_scalar($value)) {
            $trimmed = trim((string) $value);

            return $trimmed !== '' ? $trimmed : $default;
        }

        return $default;
    }
}

