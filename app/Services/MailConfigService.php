<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;

class MailConfigService
{
    /**
     * Configure mail settings dynamically from database
     */
    public static function configure()
    {
        // Get mail settings from database (Setting model may cast value; ensure we get strings)
        $mailer = static::normalizeString(Setting::get('mail_mailer', 'log'), 'log');
        $host = static::normalizeString(Setting::get('mail_host', ''), '');
        $port = Setting::get('mail_port', 587);
        $username = static::normalizeString(Setting::get('mail_username', ''), '');
        $password = static::normalizeString(Setting::get('mail_password', ''), '');
        $encryption = static::normalizeString(Setting::get('mail_encryption', 'tls'), 'tls');
        $fromAddress = static::normalizeString(Setting::get('mail_from_address', ''), '');
        $fromName = static::normalizeString(Setting::get('mail_from_name', ''), '');

        // Port may come from DB as string or number
        $port = is_numeric($port) ? (int) $port : 587;

        // Set default mailer
        Config::set('mail.default', $mailer);

        // Configure SMTP if mailer is SMTP
        if ($mailer === 'smtp') {
            Config::set('mail.mailers.smtp.host', $host);
            Config::set('mail.mailers.smtp.port', $port);
            Config::set('mail.mailers.smtp.username', $username);
            Config::set('mail.mailers.smtp.password', $password);
            Config::set('mail.mailers.smtp.encryption', $encryption === 'null' ? null : $encryption);
            Config::set('mail.mailers.smtp.timeout', 30);
            // Port 465 needs smtps scheme; Laravel only sets it when encryption is 'tls', so set explicitly for ssl
            if ((int) $port === 465 && strtolower((string) $encryption) === 'ssl') {
                Config::set('mail.mailers.smtp.scheme', 'smtps');
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

        // Set from address and name
        if ($fromAddress) {
            Config::set('mail.from.address', $fromAddress);
        }
        if ($fromName) {
            Config::set('mail.from.name', $fromName);
        }
    }

    /**
     * Normalize a setting value to string (Setting model uses 'array' cast which can return null for non-JSON values).
     */
    protected static function normalizeString($value, string $default): string
    {
        if (is_string($value) && $value !== '') {
            return $value;
        }
        if (is_scalar($value)) {
            return (string) $value;
        }
        return $default;
    }
}

