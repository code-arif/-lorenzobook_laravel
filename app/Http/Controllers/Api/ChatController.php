<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSendEvent;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function index() {}

    /**
     * Get the list of users the authenticated user has chatted with, along with groups and channels
     */
    // public function list(): JsonResponse
    // {
    //     $user = auth('api')->user();

    //     // Get all chats with sender and receiver
    //     $chats = $user->chats()
    //         ->with(['sender:id,first_name,last_name', 'receiver:id,first_name,last_name'])
    //         ->orderBy('created_at', 'desc')
    //         // ->where('sender_id', '!=', $user->id)
    //         ->get();

    //     // Collect unique users from chat
    //     $chatUsers = collect();
    //     foreach ($chats as $chat) {
    //         if ($chat->sender && $chat->sender->id !== $user->id) {
    //             $chatUsers->push($chat->sender);
    //         }

    //         if ($chat->receiver && $chat->receiver->id !== $user->id) {
    //             $chatUsers->push($chat->receiver);
    //         }
    //     }
    //     $uniqueUsers = $chatUsers->unique('id')->values()->map(function ($u) {
    //         return [
    //             'id'               => $u->id,
    //             'name'             => trim($u->first_name . ' ' . $u->last_name),
    //             'mobile_number'    => $u->mobile_number,
    //             'cover'            => $u->cover ?  url($u->cover) : null,
    //             'last_activity_at' => $u->last_activity_at,
    //             'room_id'          => $u->rooms()->first()->id ?? null,
    //             'is_online'        => $u->is_online,
    //             'status'           => $u->status ?? 'offline',
    //             'last_message'     => $u->chats()->latest()->first()->text ?? '',
    //             'humanize_date'    => $u->chats()->latest()->first()->created_at->diffForHumans() ?? '',
    //             'created_by'       => $u->first_name . ' ' . $u->last_name,
    //             'type'             => 'single_chat_user',
    //         ];
    //     });

    //     // Collect groups
    //     $groups = $user->groups->map(function ($group) {
    //         return [
    //             'id'               => $group->id,
    //             'room_id'          => null,

    //             'name'             => $group->name,
    //             'cover'        =>   $group->image_url ? url($group->image_url) : null,
    //             'created_by'       => $group->createdBy->first_name . ' ' . $group->createdBy->last_name,
    //             'is_active'        => $group->is_active,
    //             'is_archived'      => $group->is_archived,
    //             'archived_at'      => $group->archived_at,
    //             'last_activity_at' => $group->last_activity_at,

    //             'last_message'     => null,
    //             'humanize_date'    => null,
    //             'type'             => 'group',
    //         ];
    //     });

    //     // Collect channels
    //     $channels = $user->channels->map(function ($channel) {
    //         return [
    //             'id'               => $channel->id,
    //             'room_id'          => null,
    //             'name'             => $channel->name,
    //             'cover'        =>   $channel->image_url ? url($channel->image_url) : null,
    //             'channel_type'     => $channel->channel_type,
    //             'description'      => $channel->description,
    //             'is_active'        => $channel->is_active,
    //             'last_activity_at' => $channel->last_activity_at,
    //             'created_by'       => $channel->createdBy->first_name,
    //             'last_message'     => null,
    //             'humanize_date'    => null,
    //             'type'             => 'channel',
    //         ];
    //     });

    //     // Merge all into one list
    //     $mergedList = $uniqueUsers
    //         ->merge($groups)
    //         ->merge($channels)
    //         ->values();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'User chat list retrieved successfully',
    //         'data'    => $mergedList,
    //     ]);
    // }


    public function list(): JsonResponse
    {
        $user = auth('api')->user();

        $chats = $user->chats()
            ->with(['sender:id,first_name,last_name', 'receiver:id,first_name,last_name'])
            ->orderBy('created_at', 'desc')
            ->get();

        $chatUsers = collect();
        foreach ($chats as $chat) {
            if ($chat->sender && $chat->sender->id !== $user->id) {
                $chatUsers->push($chat->sender);
            }
            if ($chat->receiver && $chat->receiver->id !== $user->id) {
                $chatUsers->push($chat->receiver);
            }
        }

        $uniqueUsers = $chatUsers->unique('id')->values()->map(function ($u) use ($user) {
            // Find the room between these two users
            $room = Room::where(function ($q) use ($user, $u) {
                $q->where('user_one_id', $user->id)->where('user_two_id', $u->id);
            })->orWhere(function ($q) use ($user, $u) {
                $q->where('user_one_id', $u->id)->where('user_two_id', $user->id);
            })->first();

            // ── NEW: Skip if current user has "deleted" this conversation ──
            if ($room) {
                $deletedAt = $this->getUserDeletedAt($room, $user->id);
                if ($deletedAt !== null) {
                    return null; // Will be filtered out below
                }
            }

            // ── NEW: Check mute status for current user ──
            $muteStatus = $room ? $this->getMuteStatus($room, $user->id) : ['is_muted' => false, 'muted_until' => null];

            return [
                'id'               => $u->id,
                'name'             => trim($u->first_name . ' ' . $u->last_name),
                'mobile_number'    => $u->mobile_number,
                'cover'            => $u->cover ? url($u->cover) : null,
                'last_activity_at' => $u->last_activity_at,
                'room_id'          => $room->id ?? null,
                'is_online'        => $u->is_online,
                'status'           => $u->status ?? 'offline',
                'last_message'     => $u->chats()->latest()->first()->text ?? '',
                'humanize_date'    => $u->chats()->latest()->first()?->created_at->diffForHumans() ?? '',
                'created_by'       => $u->first_name . ' ' . $u->last_name,
                'type'             => 'single_chat_user',
                'is_muted'         => $muteStatus['is_muted'],           // NEW
                'muted_until'      => $muteStatus['muted_until'],        // NEW (null = forever if is_muted=true)
            ];
        })->filter()->values(); // filter() removes null (deleted conversations)

        $groups = $user->groups->map(function ($group) {
            return [
                'id'               => $group->id,
                'room_id'          => null,
                'name'             => $group->name,
                'cover'            => $group->image_url ? url($group->image_url) : null,
                'created_by'       => $group->createdBy->first_name . ' ' . $group->createdBy->last_name,
                'is_active'        => $group->is_active,
                'is_archived'      => $group->is_archived,
                'archived_at'      => $group->archived_at,
                'last_activity_at' => $group->last_activity_at,
                'last_message'     => null,
                'humanize_date'    => null,
                'type'             => 'group',
            ];
        });

        $channels = $user->channels->map(function ($channel) {
            return [
                'id'               => $channel->id,
                'room_id'          => null,
                'name'             => $channel->name,
                'cover'            => $channel->image_url ? url($channel->image_url) : null,
                'channel_type'     => $channel->channel_type,
                'description'      => $channel->description,
                'is_active'        => $channel->is_active,
                'last_activity_at' => $channel->last_activity_at,
                'created_by'       => $channel->createdBy->first_name,
                'last_message'     => null,
                'humanize_date'    => null,
                'type'             => 'channel',
            ];
        });

        $mergedList = $uniqueUsers->merge($groups)->merge($channels)->values();

        return response()->json([
            'success' => true,
            'message' => 'User chat list retrieved successfully',
            'data'    => $mergedList,
        ]);
    }


    /**
     * Chat list user search
     */
    public function search(Request $request): JsonResponse
    {
        $user_id = Auth::guard('api')->id();

        $keyword = $request->get('keyword');
        $users   = User::select('id', 'first_name', 'last_name', 'mobile_number', 'cover', 'last_activity_at')
            ->where('id', '!=', $user_id)
            ->where('first_name', 'LIKE', "%{$keyword}%")->orWhere('mobile_number', 'LIKE', "%{$keyword}%")
            ->get();

        $data = [
            'users' => $users,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Chat retrieved successfully',
            'data'    => $data,
        ], 200);
    }

    /**
     ** Get messages between the authenticated user and another user
     */
    // public function conversation($receiver_id): JsonResponse
    // {
    //     $sender_id = Auth::guard('api')->id();

    //     Chat::where('receiver_id', $sender_id)->where('sender_id', $receiver_id)->update(['status' => 'read']);

    //     $chat = Chat::query()
    //         ->where(function ($query) use ($receiver_id, $sender_id) {
    //             $query->where('sender_id', $sender_id)->where('receiver_id', $receiver_id);
    //         })
    //         ->orWhere(function ($query) use ($receiver_id, $sender_id) {
    //             $query->where('sender_id', $receiver_id)->where('receiver_id', $sender_id);
    //         })
    //         ->with([
    //             'sender:id,first_name,last_name,mobile_number,cover,last_activity_at',
    //             'receiver:id,first_name,last_name,mobile_number,cover,last_activity_at',
    //             'room:id,user_one_id,user_two_id',
    //         ])
    //         ->orderBy('created_at')
    //         ->limit(50)
    //         ->get();

    //     $room = Room::where(function ($query) use ($receiver_id, $sender_id) {
    //         $query->where('user_one_id', $receiver_id)->where('user_two_id', $sender_id);
    //     })->orWhere(function ($query) use ($receiver_id, $sender_id) {
    //         $query->where('user_one_id', $sender_id)->where('user_two_id', $receiver_id);
    //     })->first();

    //     if (! $room) {
    //         $room = Room::create([
    //             'user_one_id' => $sender_id,
    //             'user_two_id' => $receiver_id,
    //         ]);
    //     }

    //     $data = [
    //         'receiver' => User::select('id', 'first_name', 'last_name', 'mobile_number', 'cover', 'last_activity_at')->where('id', $receiver_id)->first(),
    //         'sender'   => User::select('id', 'first_name', 'last_name', 'mobile_number', 'cover', 'last_activity_at')->where('id', $sender_id)->first(),
    //         'room'     => $room,
    //         'chat'     => $chat,
    //     ];

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Messages retrieved successfully',
    //         'data'    => $data,
    //         'code'    => 200,
    //     ]);
    // }

    public function conversation($receiver_id): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();

        Chat::where('receiver_id', $sender_id)->where('sender_id', $receiver_id)->update(['status' => 'read']);

        $query = Chat::query()
            ->where(function ($query) use ($receiver_id, $sender_id) {
                $query->where('sender_id', $sender_id)->where('receiver_id', $receiver_id);
            })
            ->orWhere(function ($query) use ($receiver_id, $sender_id) {
                $query->where('sender_id', $receiver_id)->where('receiver_id', $sender_id);
            })
            ->with([
                'sender:id,first_name,last_name,mobile_number,cover,last_activity_at',
                'receiver:id,first_name,last_name,mobile_number,cover,last_activity_at',
                'room:id,user_one_id,user_two_id',
            ])
            ->orderBy('created_at');

        // ── NEW: If user has "deleted" the conversation, only show messages after deletion ──
        $room = Room::where(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $receiver_id)->where('user_two_id', $sender_id);
        })->orWhere(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $sender_id)->where('user_two_id', $receiver_id);
        })->first();

        if ($room) {
            $deletedAt = $this->getUserDeletedAt($room, $sender_id);
            if ($deletedAt) {
                $query->where('created_at', '>', $deletedAt);
            }
        }

        $chat = $query->limit(50)->get();

        if (! $room) {
            $room = Room::create([
                'user_one_id' => $sender_id,
                'user_two_id' => $receiver_id,
            ]);
        }

        // ── NEW: Include mute status in response ──
        $muteStatus = $this->getMuteStatus($room, $sender_id);

        $data = [
            'receiver'    => User::select('id', 'first_name', 'last_name', 'mobile_number', 'cover', 'last_activity_at')->find($receiver_id),
            'sender'      => User::select('id', 'first_name', 'last_name', 'mobile_number', 'cover', 'last_activity_at')->find($sender_id),
            'room'        => $room,
            'chat'        => $chat,
            'mute_status' => $muteStatus, // NEW
        ];

        return response()->json([
            'success' => true,
            'message' => 'Messages retrieved successfully',
            'data'    => $data,
            'code'    => 200,
        ]);
    }

    /**
     * Send a message to another user
     */
    public function send($receiver_id, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'nullable|string|max:5000',  // Increase to 5000 chars
            'file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // Max file size of 10MB
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $sender_id = Auth::guard('api')->id();

        $receiver_exist = User::where('id', $receiver_id)->first();
        if (! $receiver_exist || $receiver_id == $sender_id) {
            return response()->json(['success' => false, 'message' => 'User not found or cannot chat with yourself', 'data' => [], 'code' => 200]);
        }

        $room = Room::where(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $receiver_id)->where('user_two_id', $sender_id);
        })->orWhere(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $sender_id)->where('user_two_id', $receiver_id);
        })->first();

        if (! $room) {
            $room = Room::create([
                'user_one_id' => $sender_id,
                'user_two_id' => $receiver_id,
            ]);
        }

        $file = null;
        if ($request->hasFile('file')) {
            $file = Helper::fileUpload($request->file('file'), 'chat', time() . '_' . getFileName($request->file('file')));
        }

        $chat = Chat::create([
            'sender_id'   => $sender_id,
            'receiver_id' => $receiver_id,
            'text'        => $request->text,
            'file'        => $file,
            'room_id'     => $room->id,
            'status'      => 'sent',
        ]);

        //* Load the sender's information
        $chat->load([
            'sender:id,first_name,last_name,mobile_number,cover,last_activity_at',
            'receiver:id,first_name,last_name,mobile_number,cover,last_activity_at',
            'room:id,user_one_id,user_two_id',
        ]);

        broadcast(new MessageSendEvent($chat))->toOthers();

        $data = [
            'chat' => $chat,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data'    => $data,
            'code'    => 200,
        ]);
    }

    /**
     * Mark all messages as read between the authenticated user and another user
     */
    public function seenAll($receiver_id): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();

        $receiver_exist = User::where('id', $receiver_id)->first();
        if (! $receiver_exist || $receiver_id == $sender_id) {
            return response()->json(['success' => false, 'message' => 'User not found or cannot chat with yourself', 'data' => [], 'code' => 200]);
        }

        $chat = Chat::where('receiver_id', $sender_id)->where('sender_id', $receiver_id)->update(['status' => 'read']);

        $data = [
            'chat' => $chat,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Message seen successfully',
            'data'    => $data,
            'code'    => 200,
        ]);
    }

    /**
     * Mark all messages as read between the authenticated user and another user
     */
    public function seenSingle($chat_id): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();

        $chat = Chat::where('id', $chat_id)->where('receiver_id', $sender_id)->update(['status' => 'read']);

        $data = [
            'chat' => $chat,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Message seen successfully',
            'data'    => $data,
            'code'    => 200,
        ]);
    }

    /**
     * Get the room between the authenticated user and another user
     */
    public function room($receiver_id)
    {
        $sender_id = Auth::guard('api')->id();

        $receiver_exist = User::where('id', $receiver_id)->first();
        if (! $receiver_exist || $receiver_id == $sender_id) {
            return response()->json(['success' => false, 'message' => 'User not found or cannot chat with yourself', 'data' => [], 'code' => 200]);
        }

        $room = Room::with(['userOne:id,first_name,email,cover,last_activity_at', 'userTwo:id,first_name,email,cover,last_activity_at'])
            ->where(function ($query) use ($receiver_id, $sender_id) {
                $query->where('user_one_id', $receiver_id)->where('user_two_id', $sender_id);
            })->orWhere(function ($query) use ($receiver_id, $sender_id) {
                $query->where('user_one_id', $sender_id)->where('user_two_id', $receiver_id);
            })->first();

        if (! $room) {
            $room = Room::create([
                'user_one_id' => $sender_id,
                'user_two_id' => $receiver_id,
            ]);
        }

        $data = [
            'room' => $room,
        ];

        return response()->json(['success' => true, 'message' => 'Group retrieved successfully', 'data' => $data, 'code' => 200]);
    }

    /**
     * Search messages within a specific conversation.
     * GET /auth/chat/conversation/{receiver_id}/search?keyword=hello
     */
    public function searchConversation($receiver_id, Request $request): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();
        $keyword   = $request->get('keyword');

        if (empty($keyword)) {
            return response()->json([
                'success' => false,
                'message' => 'Keyword is required',
                'data'    => [],
            ], 422);
        }

        $chats = Chat::where(function ($query) use ($receiver_id, $sender_id) {
            $query->where(function ($q) use ($receiver_id, $sender_id) {
                $q->where('sender_id', $sender_id)
                    ->where('receiver_id', $receiver_id);
            })->orWhere(function ($q) use ($receiver_id, $sender_id) {
                $q->where('sender_id', $receiver_id)
                    ->where('receiver_id', $sender_id);
            });
        })
            ->where('text', 'LIKE', "%{$keyword}%")
            ->with([
                'sender:id,first_name,last_name,cover',
                'receiver:id,first_name,last_name,cover',
            ])
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Search results retrieved successfully',
            'data'    => [
                'keyword' => $keyword,
                'count'   => $chats->count(),
                'chats'   => $chats,
            ],
        ]);
    }


    /**
     * Clear all chat history between two users (deletes messages + media files).
     * This affects BOTH sides of the conversation.
     * DELETE /auth/chat/conversation/{receiver_id}/clear-history
     */
    public function clearHistory($receiver_id): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();

        $receiver = User::find($receiver_id);
        if (! $receiver || $receiver_id == $sender_id) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or cannot clear history with yourself',
            ], 404);
        }

        // Fetch all chats between these two users (including soft-deleted)
        $chats = Chat::withTrashed()
            ->where(function ($query) use ($receiver_id, $sender_id) {
                $query->where('sender_id', $sender_id)->where('receiver_id', $receiver_id);
            })->orWhere(function ($query) use ($receiver_id, $sender_id) {
                $query->where('sender_id', $receiver_id)->where('receiver_id', $sender_id);
            })->get();

        // Delete media files from storage
        foreach ($chats as $chat) {
            if ($chat->file) {
                $filePath = public_path($chat->file);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                // If you use Laravel Storage:
                // Storage::delete($chat->file);
            }
        }

        // Force delete all chats (bypass soft delete)
        Chat::withTrashed()
            ->where(function ($query) use ($receiver_id, $sender_id) {
                $query->where('sender_id', $sender_id)->where('receiver_id', $receiver_id);
            })->orWhere(function ($query) use ($receiver_id, $sender_id) {
                $query->where('sender_id', $receiver_id)->where('receiver_id', $sender_id);
            })->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Chat history cleared successfully',
            'data'    => [],
        ]);
    }

    /**
     * Delete conversation from the current user's chat list.
     * The other user's conversation remains intact.
     * Messages sent AFTER this point will re-appear in the list.
     * DELETE /auth/chat/conversation/{receiver_id}/delete
     */
    public function deleteConversation($receiver_id): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();

        $receiver = User::find($receiver_id);
        if (! $receiver || $receiver_id == $sender_id) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or cannot delete conversation with yourself',
            ], 404);
        }

        $room = Room::where(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $receiver_id)->where('user_two_id', $sender_id);
        })->orWhere(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $sender_id)->where('user_two_id', $receiver_id);
        })->first();

        if (! $room) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
            ], 404);
        }

        // Mark this user's side as deleted with current timestamp
        if ($room->user_one_id == $sender_id) {
            $room->update(['user_one_deleted_at' => now()]);
        } else {
            $room->update(['user_two_deleted_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Conversation deleted successfully',
            'data'    => [],
        ]);
    }


    /**
     * Mute a conversation for the current user.
     *
     * Request body:
     *  - type: 'disable_sound' | 'mute_for' | 'mute_forever'
     *  - duration: (required when type=mute_for) '1_hour' | '8_hours' | '24_hours' | '1_week'
     *
     * POST /auth/chat/mute/{receiver_id}
     */
    public function muteConversation($receiver_id, Request $request): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();

        $validator = Validator::make($request->all(), [
            'type'     => 'required|in:disable_sound,mute_for,mute_forever',
            'duration' => 'required_if:type,mute_for|in:1_hour,8_hours,24_hours,1_week',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $receiver = User::find($receiver_id);
        if (! $receiver || $receiver_id == $sender_id) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or cannot mute conversation with yourself',
            ], 404);
        }

        $room = $this->findOrCreateRoom($sender_id, $receiver_id);

        // Calculate mute expiry
        $muteUntil = match ($request->type) {
            'disable_sound' => Carbon::create(9999, 12, 31, 23, 59, 59), // Sound only — treated as forever
            'mute_forever'  => Carbon::create(9999, 12, 31, 23, 59, 59), // Forever
            'mute_for'      => match ($request->duration) {
                '1_hour'   => now()->addHour(),
                '8_hours'  => now()->addHours(8),
                '24_hours' => now()->addDay(),
                '1_week'   => now()->addWeek(),
                default    => now()->addHour(),
            },
        };

        // Update the correct user's mute column
        if ($room->user_one_id == $sender_id) {
            $room->update(['user_one_muted_until' => $muteUntil]);
        } else {
            $room->update(['user_two_muted_until' => $muteUntil]);
        }

        $isMuteForever = $request->type !== 'mute_for';

        return response()->json([
            'success' => true,
            'message' => 'Conversation muted successfully',
            'data'    => [
                'type'          => $request->type,
                'is_muted'      => true,
                'mute_forever'  => $isMuteForever,
                'muted_until'   => $isMuteForever ? null : $muteUntil->toDateTimeString(),
            ],
        ]);
    }


    /**
     * Unmute a conversation for the current user.
     * POST /auth/chat/unmute/{receiver_id}
     */
    public function unmuteConversation($receiver_id): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();

        $receiver = User::find($receiver_id);
        if (! $receiver || $receiver_id == $sender_id) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $room = Room::where(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $receiver_id)->where('user_two_id', $sender_id);
        })->orWhere(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $sender_id)->where('user_two_id', $receiver_id);
        })->first();

        if (! $room) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
            ], 404);
        }

        // Clear the mute column for this user
        if ($room->user_one_id == $sender_id) {
            $room->update(['user_one_muted_until' => null]);
        } else {
            $room->update(['user_two_muted_until' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Conversation unmuted successfully',
            'data'    => [
                'is_muted'    => false,
                'muted_until' => null,
            ],
        ]);
    }

    /**
     * Find or create a room between two users.
     */
    private function findOrCreateRoom(int $senderId, int $receiverId): Room
    {
        $room = Room::where(function ($query) use ($receiverId, $senderId) {
            $query->where('user_one_id', $receiverId)->where('user_two_id', $senderId);
        })->orWhere(function ($query) use ($receiverId, $senderId) {
            $query->where('user_one_id', $senderId)->where('user_two_id', $receiverId);
        })->first();

        if (! $room) {
            $room = Room::create([
                'user_one_id' => $senderId,
                'user_two_id' => $receiverId,
            ]);
        }

        return $room;
    }

    /**
     * Get the timestamp when a user deleted the conversation (or null if not deleted).
     */
    private function getUserDeletedAt(Room $room, int $userId): ?string
    {
        if ($room->user_one_id == $userId) {
            return $room->user_one_deleted_at;
        }

        return $room->user_two_deleted_at;
    }

    /**
     * Get the mute status for a specific user in a room.
     * Returns ['is_muted' => bool, 'muted_until' => string|null]
     */
    private function getMuteStatus(Room $room, int $userId): array
    {
        $muteColumn = $room->user_one_id == $userId
            ? 'user_one_muted_until'
            : 'user_two_muted_until';

        $muteUntil = $room->{$muteColumn};

        if (! $muteUntil) {
            return ['is_muted' => false, 'muted_until' => null];
        }

        $muteUntilCarbon = Carbon::parse($muteUntil);

        // If mute has expired, treat as not muted
        if ($muteUntilCarbon->isPast()) {
            return ['is_muted' => false, 'muted_until' => null];
        }

        // If muted until year 9999, it's "forever"
        $isForever = $muteUntilCarbon->year >= 9999;

        return [
            'is_muted'    => true,
            'muted_until' => $isForever ? null : $muteUntilCarbon->toDateTimeString(),
            'is_forever'  => $isForever,
        ];
    }
}
