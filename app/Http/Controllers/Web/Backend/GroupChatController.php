<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Chat;
use App\Models\User;
use App\Models\Group;
use App\Helpers\Helper;

use Illuminate\Http\Request;
use App\Events\MessageSendEvent;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Events\GroupMessageSentEvent;
use Illuminate\Support\Facades\Validator;

class GroupChatController extends Controller
{
    /**
     * Display the group chat index page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('backend.layouts.chat.group');
    }

    /**
     * Retrieve the list of groups for the authenticated user
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $authUser = Auth::user();

        // Fetch groups where the authenticated user is a member
        $groups = Group::whereHas('members', function ($query) use ($authUser) {
            $query->where('user_id', $authUser->id);
        })
        ->with(['createdBy:id,first_name,email', 'members' => function ($query) use ($authUser) {
            $query->where('user_id', '!=', $authUser->id);
        }])
        ->get()
        ->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'cover' => $group->image_url ? url($group->image_url) : null,
                'created_by' => $group->createdBy->first_name,
                'is_active' => $group->is_active,
                'last_activity_at' => $group->last_activity_at,
                'member_count' => $group->members->count() + 1, // +1 for the authenticated user
                'type' => 'group',
            ];
        });

        $data = [
            'groups' => $groups,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Group list retrieved successfully',
            'data' => $data,
        ], 200);
    }

    /**
     * Search groups based on keyword
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $authUser = Auth::user();
        $keyword = $request->get('keyword');

        $groups = Group::whereHas('members', function ($query) use ($authUser) {
            $query->where('user_id', $authUser->id);
        })
        ->where('name', 'LIKE', "%{$keyword}%")
        ->with(['createdBy:id,first_name,email'])
        ->get()
        ->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'cover' => $group->image_url ? url($group->image_url) : null,
                'created_by' => $group->createdBy->first_name,
                'last_activity_at' => $group->last_activity_at,
                'type' => 'group',
            ];
        });

        $data = [
            'groups' => $groups,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Groups retrieved successfully',
            'data' => $data,
        ], 200);
    }

    /**
     * Retrieve messages for a specific group
     *
     * @param int $group_id
     * @param Request $request
     * @return JsonResponse
     */
    public function messages($group_id, Request $request): JsonResponse
    {
        $authUser = Auth::user();

        // Verify group existence and membership
        $group = Group::where('id', $group_id)
            ->whereHas('members', function ($query) use ($authUser) {
                $query->where('user_id', $authUser->id);
            })->first();

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found or you are not a member',
                'data' => [],
                'code' => 404,
            ]);
        }

        // Pagination parameters
        $perPage = $request->input('per_page', 50);
        $page = $request->input('page', 1);

        // Fetch messages
        $chats = Chat::where('room_id', $group_id)
            ->with(['sender:id,first_name,email,cover,last_activity_at'])
            ->orderBy('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        // Mark unread messages as read for the authenticated user
        Chat::where('room_id', $group_id)
            ->where('sender_id', '!=', $authUser->id)
            ->where('status', 'sent')
            ->update(['status' => 'read']);

        $data = [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'cover' => $group->image_url ? url($group->image_url) : null,
                'last_activity_at' => $group->last_activity_at,
            ],
            'messages' => $chats->items(),
            'pagination' => [
                'current_page' => $chats->currentPage(),
                'last_page' => $chats->lastPage(),
                'per_page' => $chats->perPage(),
                'total' => $chats->total(),
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Group messages retrieved successfully',
            'data' => $data,
            'code' => 200,
        ]);
    }

    /**
     * Send a message to a group
     *
     * @param int $group_id
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMessage($group_id, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'nullable|string|max:255',
            'file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $sender_id = Auth::id();

        // Verify group existence and membership
        $group = Group::where('id', $group_id)
            ->whereHas('members', function ($query) use ($sender_id) {
                $query->where('user_id', $sender_id);
            })->first();

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found or you are not a member',
                'data' => [],
                'code' => 404,
            ]);
        }

        $file = null;
        if ($request->hasFile('file')) {
            $file = Helper::fileUpload($request->file('file'), 'chat', time() . '_' . getFileName($request->file('file')));
        }

        $chat = Chat::create([
            'sender_id' => $sender_id,
            'receiver_id' => null,
            'text' => $request->text,
            'file' => $file,
            'room_id' => $group->id,
            'status' => 'sent',
        ]);

        // Load related data
        $chat->load(['sender:id,first_name,email,cover,last_activity_at']);

        // Update group last activity
        $group->update(['last_activity_at' => now()]);

        // Broadcast the message
        broadcast(new GroupMessageSentEvent($chat))->toOthers();
        // broadcast(new MessageSendEvent($chat))->toOthers();

        $data = [
            'chat' => $chat,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Message sent to group successfully',
            'data' => $data,
            'code' => 200,
        ]);
    }
}
