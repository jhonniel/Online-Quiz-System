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
        // Get mail settings from database
        $mailer = Setting::get('mail_mailer', 'log');
        $host = Setting::get('mail_host', '');
        $port = Setting::get('mail_port', 587);
        $username = Setting::get('mail_username', '');
        $password = Setting::get('mail_password', '');
        $encryption = Setting::get('mail_encryption', 'tls');
        $fromAddress = Setting::get('mail_from_address', '');
        $fromName = Setting::get('mail_from_name', '');

        // Set default mailer
        Config::set('mail.default', $mailer);

        // Configure SMTP if mailer is SMTP
        if ($mailer === 'smtp') {
            Config::set('mail.mailers.smtp.host', $host);
            Config::set('mail.mailers.smtp.port', $port);
            Config::set('mail.mailers.smtp.username', $username);
            Config::set('mail.mailers.smtp.password', $password);
            Config::set('mail.mailers.smtp.encryption', $encryption === 'null' ? null : $encryption);
        }

        // Set from address and name
        if ($fromAddress) {
            Config::set('mail.from.address', $fromAddress);
        }
        if ($fromName) {
            Config::set('mail.from.name', $fromName);
        }
    }
}

