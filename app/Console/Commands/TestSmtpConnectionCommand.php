<?php

namespace App\Console\Commands;

use App\Services\MailConfigService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestSmtpConnectionCommand extends Command
{
    protected $signature = 'mail:test-smtp {--to=devjry@gmail.com : Email to test sending to}';

    protected $description = 'Test SMTP connection and authentication directly';

    public function handle(): int
    {
        MailConfigService::configure();
        
        $host = config('mail.mailers.smtp.host');
        $port = (int) config('mail.mailers.smtp.port');
        $username = config('mail.mailers.smtp.username');
        $password = config('mail.mailers.smtp.password');
        $encryption = config('mail.mailers.smtp.encryption');
        $from = config('mail.from.address');
        $to = $this->option('to');

        $this->info("Testing SMTP connection to {$host}:{$port}");
        $this->line("Username: {$username}");
        $this->line("From: {$from}");
        $this->line("To: {$to}");
        $this->line("Encryption: " . ($encryption ?: 'none'));
        $this->line('');

        // Test connection
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ]);

        $scheme = '';
        if ($port === 465) {
            $scheme = 'ssl://';
        } elseif ($encryption === 'tls') {
            $scheme = 'tcp://';
        } else {
            $scheme = 'tcp://';
        }

        $this->info("Connecting to {$scheme}{$host}:{$port}...");
        
        $socket = @stream_socket_client(
            "{$scheme}{$host}:{$port}",
            $errno,
            $errstr,
            10,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$socket) {
            $this->error("Connection failed: {$errstr} ({$errno})");
            return 1;
        }

        $this->info("✓ Connected");
        
        // Read greeting
        $response = fgets($socket, 515);
        $this->line("Server: " . trim($response));
        
        if (!preg_match('/^2\d{2}/', $response)) {
            $this->error("Server did not send greeting");
            fclose($socket);
            return 1;
        }

        // EHLO
        fwrite($socket, "EHLO " . parse_url(config('app.url', 'localhost'), PHP_URL_HOST) . "\r\n");
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (preg_match('/^\d{3} /', $line)) {
                break;
            }
        }
        $this->line("EHLO response: " . trim($response));

        // STARTTLS if needed (port 587 with tls)
        if ($port === 587 && $encryption === 'tls') {
            fwrite($socket, "STARTTLS\r\n");
            $response = fgets($socket, 515);
            $this->line("STARTTLS: " . trim($response));
            
            if (preg_match('/^2\d{2}/', $response)) {
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->info("✓ TLS enabled");
                
                // EHLO again after TLS
                fwrite($socket, "EHLO " . parse_url(config('app.url', 'localhost'), PHP_URL_HOST) . "\r\n");
                $response = '';
                while ($line = fgets($socket, 515)) {
                    $response .= $line;
                    if (preg_match('/^\d{3} /', $line)) {
                        break;
                    }
                }
                $this->line("EHLO after TLS: " . trim($response));
            }
        }

        // AUTH LOGIN
        fwrite($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket, 515);
        $this->line("AUTH LOGIN: " . trim($response));

        fwrite($socket, base64_encode($username) . "\r\n");
        $response = fgets($socket, 515);
        $this->line("Username sent: " . trim($response));

        fwrite($socket, base64_encode($password) . "\r\n");
        $response = fgets($socket, 515);
        $this->line("Password sent: " . trim($response));

        if (!preg_match('/^2\d{2}/', $response)) {
            $this->error("✗ Authentication failed");
            fclose($socket);
            return 1;
        }

        $this->info("✓ Authentication successful");

        // MAIL FROM
        fwrite($socket, "MAIL FROM:<{$from}>\r\n");
        $response = fgets($socket, 515);
        $this->line("MAIL FROM: " . trim($response));

        if (!preg_match('/^2\d{2}/', $response)) {
            $this->error("✗ MAIL FROM rejected");
            fclose($socket);
            return 1;
        }

        // RCPT TO
        fwrite($socket, "RCPT TO:<{$to}>\r\n");
        $response = fgets($socket, 515);
        $this->line("RCPT TO: " . trim($response));

        if (!preg_match('/^2\d{2}/', $response)) {
            $this->error("✗ RCPT TO rejected (server may not accept this recipient)");
            fclose($socket);
            return 1;
        }

        $this->info("✓ Recipient accepted");

        // DATA
        fwrite($socket, "DATA\r\n");
        $response = fgets($socket, 515);
        $this->line("DATA: " . trim($response));

        // Send minimal email
        $message = "From: {$from}\r\n";
        $message .= "To: {$to}\r\n";
        $message .= "Subject: SMTP Test\r\n";
        $message .= "\r\n";
        $message .= "This is a test email from SMTP connection test.\r\n";
        $message .= ".\r\n";

        fwrite($socket, $message);
        $response = fgets($socket, 515);
        $this->line("Message sent: " . trim($response));

        if (preg_match('/^2\d{2}/', $response)) {
            $this->info("✓ Message accepted by server");
        } else {
            $this->error("✗ Message rejected by server");
            fclose($socket);
            return 1;
        }

        // QUIT
        fwrite($socket, "QUIT\r\n");
        fclose($socket);

        $this->line('');
        $this->info('SMTP test completed successfully.');
        $this->warn('If email still not received, the issue is likely:');
        $this->line('  - Server accepted but not relaying (check server logs)');
        $this->line('  - SPF/DKIM/DMARC issues causing recipient to reject');
        $this->line('  - Recipient server blocking sender domain/IP');

        return 0;
    }
}
