<?php

namespace App\Events\Chat;

use App\Models\UserChatMessage;
use App\Support\ChatChannels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DirectChatMessageSent implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public UserChatMessage $message) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(ChatChannels::direct(
                (int) $this->message->sender_id,
                (int) $this->message->receiver_id
            )),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        $this->message->loadMissing('sender:id,name');

        return [
            'chat_type' => 'friend',
            'id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'receiver_id' => $this->message->receiver_id,
            'message' => $this->message->message,
            'created_at' => $this->message->created_at?->toIso8601String(),
            'sender' => [
                'id' => $this->message->sender?->id,
                'name' => $this->message->sender?->name,
            ],
        ];
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }
}
