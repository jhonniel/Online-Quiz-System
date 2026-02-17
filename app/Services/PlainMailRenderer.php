<?php

namespace App\Services;

use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;

class PlainMailRenderer
{
    /**
     * Render the email view without CSS inlining
     */
    public static function render($view, $data = [])
    {
        // Get the view content directly without CSS inlining
        $html = view($view, $data)->render();
        
        return $html;
    }
}
