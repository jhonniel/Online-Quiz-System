<?php

namespace App\Events\Chat;

use App\Models\ChatMessageMedia;
use App\Models\GroupChatMessage;
use App\Support\ChatChannels;
use App\Support\ChatMediaService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupChatMessageSent implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public GroupChatMessage $message) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(ChatChannels::group((int) $this->message->group_chat_id)),
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
            'chat_type' => 'group',
            'id' => $this->message->id,
            'group_chat_id' => $this->message->group_chat_id,
            'sender_id' => $this->message->sender_id,
            'message' => $this->message->message,
            'created_at' => $this->message->created_at?->toIso8601String(),
            'sender' => [
                'id' => $this->message->sender?->id,
                'name' => $this->message->sender?->name,
            ],
            'media' => ChatMediaService::serializeForBroadcast(
                ChatMediaService::findForMessage(ChatMessageMedia::TYPE_GROUP, (int) $this->message->id)
            ),
        ];
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }
}
