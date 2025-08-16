<?php
namespace App\Http\Controllers\Api;

use App\Events\MessageSendEvent;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function index()
    {}

    public function list(): JsonResponse
    {
        $user = auth('api')->user();

        // Get all chats with sender and receiver
        $chats = $user->chats()
            ->with(['sender:id,first_name,last_name', 'receiver:id,first_name,last_name'])
            ->orderBy('created_at', 'desc')
        // ->where('sender_id', '!=', $user->id)
            ->get();

        // Collect unique users from chat
        $chatUsers = collect();
        foreach ($chats as $chat) {
            if ($chat->sender && $chat->sender->id !== $user->id) {
                $chatUsers->push($chat->sender);
            }

            if ($chat->receiver && $chat->receiver->id !== $user->id) {
                $chatUsers->push($chat->receiver);
            }
        }
        $uniqueUsers = $chatUsers->unique('id')->values()->map(function ($u) {
            return [
                'id'               => $u->id,
                'name'             => trim($u->first_name . ' ' . $u->last_name),
                'mobile_number'    => $u->mobile_number,
                'cover'            => $u->cover ?  url($u->cover) : null,
                'last_activity_at' => $u->last_activity_at,
                'room_id'          => $u->rooms()->first()->id ?? null,
                'is_online'        => $u->is_online,
                'status'           => $u->status ?? 'offline',
                'last_message'     => $u->chats()->latest()->first()->text ?? '',
                'humanize_date'    => $u->chats()->latest()->first()->created_at->diffForHumans() ?? '',
                'created_by'       => $u->first_name . ' ' . $u->last_name,
                'type'             => 'single_chat_user',
            ];
        });

        // Collect groups
        $groups = $user->groups->map(function ($group) {
            return [
                'id'               => $group->id,
                'room_id'          => null,

                'name'             => $group->name,
                'cover'        =>   $group->image_url ? url($group->image_url) : null,
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

        // Collect channels
        $channels = $user->channels->map(function ($channel) {
            return [
                'id'               => $channel->id,
                'room_id'          => null,
                'name'             => $channel->name,
                'cover'        =>   $channel->image_url ? url($channel->image_url) : null,
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

        // Merge all into one list
        $mergedList = $uniqueUsers
            ->merge($groups)
            ->merge($channels)
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'User chat list retrieved successfully',
            'data'    => $mergedList,
        ]);
    }

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
     *
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     */
    public function conversation($receiver_id): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();

        Chat::where('receiver_id', $sender_id)->where('sender_id', $receiver_id)->update(['status' => 'read']);

        $chat = Chat::query()
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
            ->orderBy('created_at')
            ->limit(50)
            ->get();

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

        $data = [
            'receiver' => User::select('id', 'first_name', 'last_name', 'mobile_number', 'cover', 'last_activity_at')->where('id', $receiver_id)->first(),
            'sender'   => User::select('id', 'first_name', 'last_name', 'mobile_number', 'cover', 'last_activity_at')->where('id', $sender_id)->first(),
            'room'     => $room,
            'chat'     => $chat,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Messages retrieved successfully',
            'data'    => $data,
            'code'    => 200,
        ]);
    }

    /**
     *! Send a message to another user
     *
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     */
    public function send($receiver_id, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'nullable|string|max:255',
            'file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
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
}
