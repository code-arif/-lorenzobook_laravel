<?php

namespace App\Events;

use App\Models\Chat;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupMessageSentEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Chat $chat;

    public function __construct(Chat $chat)
    {
        // Ensure relations are loaded so the frontend gets full data
        $this->chat = $chat->loadMissing([
            'sender:id,first_name,last_name,email,cover,last_activity_at',
            'group:id,name,image_url',
        ]);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("group-chat.{$this->chat->group_id}"),
        ];
    }
}
