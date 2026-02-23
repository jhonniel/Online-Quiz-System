<?php

namespace App\Console\Commands;

use App\Mail\TestEmail;
use App\Services\MailConfigService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mailer\Event\SentMessageEvent;
use Symfony\Component\Mailer\Event\FailedMessageEvent;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;

class TestMailCommand extends Command
{
    protected $signature = 'mail:test {to : Email address to send test to} {--debug : Show SMTP debug output}';

    protected $description = 'Send a test email and show any error (uses DB mail settings)';

    private $smtpLog = [];

    public function handle(): int
    {
        $to = $this->argument('to');
        $verbose = $this->option('debug');

        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error("Invalid email: {$to}");
            return 1;
        }

        $this->info('Loading mail config from database...');
        MailConfigService::configure();

        $mailer = config('mail.default');
        $this->info('Mailer: ' . $mailer);

        if ($mailer === 'smtp') {
            $host = config('mail.mailers.smtp.host');
            $port = config('mail.mailers.smtp.port');
            $username = config('mail.mailers.smtp.username');
            $from = config('mail.from.address');
            
            $this->info('SMTP host: ' . $host);
            $this->info('SMTP port: ' . $port);
            $this->info('SMTP username: ' . ($username ?: '(empty)'));
            $this->info('From: ' . $from);
            
            if ($verbose) {
                $this->warn('Verbose mode: Will show SMTP conversation');
            }
        }

        // Purge cached mailer so we use fresh config
        try {
            app('mail.manager')->purge($mailer);
        } catch (\Throwable $e) {
            // ignore
        }

        // Enable SMTP debug if verbose
        if ($verbose && $mailer === 'smtp') {
            $this->enableSmtpDebug();
        }

        $this->info('Sending test email to ' . $to . '...');
        $startTime = microtime(true);

        try {
            Mail::to($to)->send(new TestEmail());
            $duration = round(microtime(true) - $startTime, 2);
            
            $this->info("Done in {$duration}s. No exception thrown.");
            
            if ($verbose && !empty($this->smtpLog)) {
                $this->line('');
                $this->info('SMTP conversation:');
                foreach ($this->smtpLog as $line) {
                    $this->line('  ' . $line);
                }
            }
            
            $this->warn('If email not received, check:');
            $this->line('  1. Spam/junk folder');
            $this->line('  2. SMTP server logs (mail.infosoftstudio.com)');
            $this->line('  3. SPF/DKIM records for infosoftstudio.com');
            $this->line('  4. Gmail may block if sender reputation is low');
            
            return 0;
        } catch (\Throwable $e) {
            $duration = round(microtime(true) - $startTime, 2);
            $this->error("Send failed after {$duration}s: " . $e->getMessage());
            $this->line('');
            $this->line('Exception: ' . get_class($e));
            $this->line('File: ' . $e->getFile() . ':' . $e->getLine());
            
            if ($verbose && !empty($this->smtpLog)) {
                $this->line('');
                $this->error('SMTP conversation before error:');
                foreach ($this->smtpLog as $line) {
                    $this->line('  ' . $line);
                }
            }
            
            $this->line('');
            $this->line('Full trace:');
            $this->line($e->getTraceAsString());
            
            Log::error('mail:test failed', [
                'to' => $to,
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
            return 1;
        }
    }

    protected function enableSmtpDebug(): void
    {
        try {
            $mailManager = app('mail.manager');
            $mailer = $mailManager->mailer();
            
            // Get the transport
            $transport = $mailer->getSymfonyTransport();
            
            if ($transport instanceof SmtpTransport) {
                // Try to enable debug output
                $stream = $transport->getStream();
                if (method_exists($stream, 'setDebug')) {
                    $stream->setDebug(true);
                }
                
                // Log SMTP events via EventDispatcher
                $dispatcher = new EventDispatcher();
                $dispatcher->addListener(MessageEvent::class, function (MessageEvent $event) {
                    $this->smtpLog[] = '[MessageEvent] Preparing to send';
                });
                $dispatcher->addListener(SentMessageEvent::class, function (SentMessageEvent $event) {
                    $this->smtpLog[] = '[SentMessageEvent] Message accepted by server';
                });
                $dispatcher->addListener(FailedMessageEvent::class, function (FailedMessageEvent $event) {
                    $this->smtpLog[] = '[FailedMessageEvent] ' . $event->getError()->getMessage();
                });
            }
        } catch (\Throwable $e) {
            $this->warn('Could not enable SMTP debug: ' . $e->getMessage());
        }
    }
}
