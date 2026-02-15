<?php

namespace App\Mail;

use Illuminate\Mail\Mailer as BaseMailer;
use Illuminate\Mail\Message;
use Swift_Message;

class CustomMailer extends BaseMailer
{
    /**
     * Render the given view.
     *
     * @param  string|array  $view
     * @param  array  $data
     * @return string
     */
    protected function renderView($view, $data)
    {
        // Skip CSS inlining - just render the view directly
        if (is_string($view)) {
            return view($view, $data)->render();
        }

        // If it's an array with html/text keys, render them without CSS processing
        if (is_array($view)) {
            $rendered = [];
            
            if (isset($view['html'])) {
                $rendered['html'] = view($view['html'], $data)->render();
            }
            
            if (isset($view['text'])) {
                $rendered['text'] = view($view['text'], $data)->render();
            }
            
            return $rendered;
        }

        return parent::renderView($view, $data);
    }

    /**
     * Add the content to a given message.
     *
     * @param  \Illuminate\Mail\Message  $message
     * @param  string|array  $view
     * @param  string  $plain
     * @param  array  $data
     * @param  string|null  $callback
     * @return void
     */
    public function addContent($message, $view, $plain, $data, $callback = null)
    {
        // Render views without CSS inlining
        $html = null;
        $text = null;

        if (isset($view)) {
            $html = is_string($view) ? view($view, $data)->render() : $view;
        }

        if (isset($plain)) {
            $text = is_string($plain) ? view($plain, $data)->render() : $plain;
        }

        // Add content directly without CSS processing
        if ($html) {
            $message->setBody($html, 'text/html');
        }

        if ($text) {
            $message->addPart($text, 'text/plain');
        }

        if ($callback) {
            call_user_func($callback, $message);
        }
    }
}
