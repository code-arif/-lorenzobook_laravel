<?php
namespace App\Http\Controllers\Api\Frontend;

use App\Events\GroupMessageSentEvent;
use App\Models\Chat;
use App\Models\Group;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Events\MessageSendEvent;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GroupChatController extends Controller
{

    /**
     * Send a message to a group
     *
     * @param int $group_id
     * @param Request $request
     * @return JsonResponse
     */

    public function sendGroupMessage($group_id, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'nullable|string|max:255',
            'file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $sender_id = auth('api')->id();

        // check if group exists and user is a member
        $group = Group::where('id', $group_id)
            ->whereHas('members', function ($query) use ($sender_id) {
                $query->where('user_id', $sender_id);
            })->first();
        // dd($group);

        if (! $group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found or you are not a member',
                'data'    => [],
                'code'    => 404,
            ]);
        }

        $file = null;
        if ($request->hasFile('file')) {
            $file = Helper::fileUpload($request->file('file'), 'chat', time() . '_' . getFileName($request->file('file')));
        }

        $chat = Chat::create([
            'sender_id'   => $sender_id,
            'receiver_id' => null,
            'text'        => $request->text,
            'file'        => $file,
            'group_id'     => $group->id,
            'status'      => 'sent',
        ]);

        // Load related data
        $chat->load([
            'sender:id,first_name,last_name,mobile_number,cover,last_activity_at',
            'group:id,name,image_url',
        ]);

        // Broadcast the message to group members
        broadcast(new GroupMessageSentEvent($chat))->toOthers();

        $data = [
            'chat' => $chat,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Message sent to group successfully',
            'data'    => $data,
            'code'    => 200,
        ]);
    }

    public function getGroupMessages($group_id, Request $request): JsonResponse
    {
        // Get the authenticated user
        $user_id = Auth::guard('api')->id();

        // Verify group existence and membership
        $group = Group::where('id', $group_id)
            ->whereHas('members', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })->first();

        if (! $group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found or you are not a member',
                'data'    => [],
                'code'    => 404,
            ]);
        }


        $chats = Chat::where('group_id', $group_id)
            ->with([
                'sender:id,first_name,last_name,mobile_number,cover,last_activity_at',
                'group:id,name,image_url',
            ])
            ->orderBy('created_at', 'desc')->get();


        // Prepare group details
        $groupData = [
            'id'               => $group->id,
            'name'             => $group->name,
            'cover'            => $group->image_url ? url($group->image_url) : null,
            'last_activity_at' => $group->last_activity_at,
        ];

        // Prepare response data
        $data = [
            'group'      => $groupData,
            'messages'   => $chats, // Current page messages

        ];

        return response()->json([
            'success' => true,
            'message' => 'Group messages retrieved successfully',
            'data'    => $data,
            'code'    => 200,
        ]);
    }

}
