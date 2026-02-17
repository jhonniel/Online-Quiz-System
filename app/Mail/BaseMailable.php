<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

abstract class BaseMailable extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Override the build method to skip CSS inlining
     */
    public function build()
    {
        // This prevents Laravel from trying to inline CSS
        return $this;
    }

    /**
     * Override send method to use plain HTML rendering
     */
    public function send($mailer)
    {
        // Get the view content without CSS inlining
        $view = $this->buildView();
        
        if (isset($view['html'])) {
            // Render the view directly without CSS processing
            $html = view($view['html'], $this->buildViewData())->render();
            $view['html'] = $html;
        }
        
        return parent::send($mailer);
    }
}
