<?php

namespace App\Events\Chat;

use App\Support\ChatChannels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DirectChatMessagesRead implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  list<int>  $messageIds
     */
    public function __construct(
        public int $readerId,
        public int $senderId,
        public array $messageIds,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(ChatChannels::direct($this->readerId, $this->senderId)),
        ];
    }

    public function broadcastAs(): string
    {
        return 'messages.read';
    }

    public function broadcastWith(): array
    {
        return [
            'chat_type' => 'friend',
            'reader_id' => $this->readerId,
            'sender_id' => $this->senderId,
            'message_ids' => array_values($this->messageIds),
            'read_at' => now()->toIso8601String(),
        ];
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }
}
