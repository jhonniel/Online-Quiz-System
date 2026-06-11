<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

final class ChatBroadcast
{
    public static function dispatch(object $event): void
    {
        try {
            dispatch($event)->afterResponse();
        } catch (\Throwable $exception) {
            Log::warning('Chat broadcast dispatch failed.', [
                'event' => $event::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
