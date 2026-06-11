<?php

namespace App\Events\Chat;

use App\Models\AnonymousChatMessage;
use App\Models\AnonymousChatRoom;
use App\Models\ChatMessageMedia;
use App\Support\ChatChannels;
use App\Support\ChatMediaService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnonymousChatMessageSent implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public AnonymousChatMessage $message,
        public AnonymousChatRoom $room,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(ChatChannels::anonymous((int) $this->room->id)),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'chat_type' => 'anonymous',
            'id' => $this->message->id,
            'room_id' => $this->room->id,
            'sender_alias' => $this->room->senderAlias((int) $this->message->sender_id),
            'message' => $this->message->message,
            'created_at' => $this->message->created_at?->toIso8601String(),
            'is_own' => false,
            'media' => ChatMediaService::serializeForBroadcast(
                ChatMediaService::findForMessage(ChatMessageMedia::TYPE_ANONYMOUS, (int) $this->message->id)
            ),
        ];
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }
}
