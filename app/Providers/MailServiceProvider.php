<?php

namespace App\Providers;

use Illuminate\Mail\MailServiceProvider as BaseMailServiceProvider;
use Illuminate\Mail\Mailer;
use Illuminate\View\Factory as ViewFactory;
use Swift_Mailer;

class MailServiceProvider extends BaseMailServiceProvider
{
    /**
     * Register the mailer instance.
     */
    protected function registerIlluminateMailer()
    {
        $this->app->singleton('mail.manager', function ($app) {
            return new \Illuminate\Mail\MailManager($app);
        });

        $this->app->bind('mailer', function ($app) {
            return $app->make('mail.manager')->mailer();
        });
    }
}
