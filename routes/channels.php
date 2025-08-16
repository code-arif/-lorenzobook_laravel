<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Room;
use App\Models\Group;

Broadcast::channel('test-notify.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('notify.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
# chat
*/

Broadcast::channel('chat-room.{room_id}', function ($user, $room_id) {
    // Check if the user is a member of the group
    $group = Group::find($room_id);

    if ($group) {
        return $group->members()->where('user_id', $user->id)->exists();
    }
    // Fallback for 1-to-1 chat rooms
    $room = Room::find($room_id);
    return $room && ((int) $user->id === (int) $room->user_one_id || (int) $user->id === (int) $room->user_two_id);
});

Broadcast::channel('chat-receiver.{receiver_id}', function ($user, $receiver_id) {
    return (int) $user->id === (int) $receiver_id;
});

Broadcast::channel('chat-sender.{sender_id}', function ($user, $sender_id) {
    return (int) $user->id === (int) $sender_id;
});
