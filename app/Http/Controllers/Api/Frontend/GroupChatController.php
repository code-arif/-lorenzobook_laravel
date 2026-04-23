<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Models\Chat;
use App\Models\Group;
use App\Helpers\Helper;
use App\Events\GroupMessageSentEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class GroupChatController extends Controller
{
    /**
     * Send a message to a group.
     * Fixed: saves to group_id (not room_id), receiver_id is null for group messages.
     */
    public function sendGroupMessage(int $group_id, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'nullable|string|max:1000',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,wmv,pdf,doc,docx,zip,txt|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        if (! $request->filled('text') && ! $request->hasFile('file')) {
            return response()->json(['success' => false, 'message' => 'Message or file is required.'], 422);
        }

        $senderId = auth('api')->id();
        $group    = Group::forUser($senderId)->find($group_id);

        if (! $group) {
            return response()->json(['success' => false, 'message' => 'Group not found or you are not a member.'], 404);
        }

        // Check if sender is banned
        $member = $group->members()->where('user_id', $senderId)->first();
        if ($member && $member->pivot->is_banned) {
            return response()->json(['success' => false, 'message' => 'You are banned from this group.'], 403);
        }

        $file = null;
        if ($request->hasFile('file')) {
            $file = Helper::fileUpload(
                $request->file('file'),
                'chat',
                time() . '_' . getFileName($request->file('file'))
            );
        }

        $chat = Chat::create([
            'sender_id'   => $senderId,
            'receiver_id' => null,       // No receiver for group messages
            'group_id'    => $group->id, // FIX: was 'room_id' previously
            'text'        => $request->text,
            'file'        => $file,
            'status'      => 'sent',
        ]);

        $group->update(['last_activity_at' => now()]);

        $chat->load(['sender:id,first_name,last_name,cover,last_activity_at', 'group:id,name,image_url']);

        broadcast(new GroupMessageSentEvent($chat))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully.',
            'data'    => ['chat' => $chat],
        ]);
    }

    /**
     * Get all messages for a group (newest first, paginated).
     */
    public function getGroupMessages(int $group_id, Request $request): JsonResponse
    {
        $userId = auth('api')->id();
        $group  = Group::forUser($userId)->find($group_id);

        if (! $group) {
            return response()->json(['success' => false, 'message' => 'Group not found or you are not a member.'], 404);
        }

        $messages = Chat::where('group_id', $group_id)
            ->with(['sender:id,first_name,last_name,cover,last_activity_at'])
            ->latest()
            ->paginate($request->integer('per_page', 30));

        // Mark messages as read
        Chat::where('group_id', $group_id)
            ->where('sender_id', '!=', $userId)
            ->where('status', 'sent')
            ->update(['status' => 'read']);

        return response()->json([
            'success' => true,
            'message' => 'Messages retrieved successfully.',
            'data'    => [
                'group' => [
                    'id'    => $group->id,
                    'name'  => $group->name,
                    'cover' => $group->image_url ? url($group->image_url) : null,
                ],
                'messages'   => $messages->items(),
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'last_page'    => $messages->lastPage(),
                    'per_page'     => $messages->perPage(),
                    'total'        => $messages->total(),
                ],
            ],
        ]);
    }
}
