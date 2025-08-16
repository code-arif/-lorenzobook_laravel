<?php

namespace App\Events;

use App\Models\Chat;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class GroupMessageSentEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat;

    public function __construct(Chat $chat)
    {
        $this->chat = $chat;

        // dd($chat);
    }

    public function broadcastOn()
    {
        // Use the group room ID as the channel name
        return new PrivateChannel('chat-room.' . $this->chat->room_id);
    }

    public function broadcastWith()
    {
        return [
            'data' => [
                'id' => $this->chat->id,
                'sender_id' => $this->chat->sender_id,
                'text' => $this->chat->text,
                'file' => $this->chat->file,
                'created_at' => $this->chat->created_at,
                'sender' => $this->chat->sender,
            ]
        ];
    }
}
