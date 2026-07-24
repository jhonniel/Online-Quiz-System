<?php

namespace App\Events;

use App\Models\SayItGameSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SayItGameUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public SayItGameSession $session) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('sayit.game.'.$this->session->code),
        ];
    }

    public function broadcastAs(): string
    {
        return 'game.updated';
    }

    public function broadcastWith(): array
    {
        return $this->session->toSyncPayload();
    }
}
