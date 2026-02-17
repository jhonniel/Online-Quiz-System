<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailService
{
    /**
     * Send email with error handling for CSS inlining issues
     */
    public static function send($mailable, $to = null)
    {
        try {
            if ($to) {
                return Mail::to($to)->send($mailable);
            } else {
                return Mail::send($mailable);
            }
        } catch (\Error $e) {
            // Catch the CssSelectorConverter error specifically
            if (str_contains($e->getMessage(), 'CssSelectorConverter')) {
                Log::warning('Email CSS inlining failed, attempting to send without CSS processing', [
                    'error' => $e->getMessage(),
                    'mailable' => get_class($mailable)
                ]);
                
                // Try to send using raw HTML rendering
                try {
                    // Get the view content directly
                    $view = $mailable->buildView();
                    if (isset($view['html'])) {
                        $html = view($view['html'], $mailable->buildViewData())->render();
                        
                        // Send raw HTML email
                        return Mail::raw($html, function ($message) use ($mailable) {
                            $envelope = $mailable->envelope();
                            $message->subject($envelope->subject);
                            if ($envelope->from) {
                                $message->from($envelope->from[0]['address'], $envelope->from[0]['name'] ?? null);
                            }
                            if ($envelope->replyTo) {
                                foreach ($envelope->replyTo as $replyTo) {
                                    $message->replyTo($replyTo['address'], $replyTo['name'] ?? null);
                                }
                            }
                        });
                    }
                } catch (\Exception $retryException) {
                    Log::error('Failed to send email even with workaround', [
                        'original_error' => $e->getMessage(),
                        'retry_error' => $retryException->getMessage()
                    ]);
                    throw $e; // Re-throw original error
                }
            }
            
            // Re-throw if it's a different error
            throw $e;
        }
    }
}
