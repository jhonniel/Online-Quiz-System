<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailExceptionHandler
{
    /**
     * Handle mail sending with fallback for CSS inlining errors
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
            if (str_contains($e->getMessage(), 'CssSelectorConverter')) {
                Log::warning('CSS inlining failed, sending email without CSS processing', [
                    'error' => $e->getMessage()
                ]);
                
                // Try to send using raw HTML
                try {
                    $view = $mailable->buildView();
                    $envelope = $mailable->envelope();
                    
                    if (isset($view['html'])) {
                        $html = view($view['html'], $mailable->buildViewData())->render();
                        
                        if ($to) {
                            Mail::to($to)->html($html, function ($message) use ($envelope) {
                                $message->subject($envelope->subject);
                                if ($envelope->from) {
                                    $message->from($envelope->from[0]['address'], $envelope->from[0]['name'] ?? null);
                                }
                            });
                        }
                    }
                } catch (\Exception $retryException) {
                    Log::error('Failed to send email even with fallback', [
                        'original' => $e->getMessage(),
                        'retry' => $retryException->getMessage()
                    ]);
                    throw $e;
                }
            } else {
                throw $e;
            }
        }
    }
}
