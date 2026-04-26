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

    /**
     * Clear all chat history for a group (deletes messages + media files).
     * This affects ALL members of the group.
     */
    public function clearGroupChatHistory(int $group_id): JsonResponse
    {
        $userId = auth('api')->id();
        $group  = Group::forUser($userId)->find($group_id);

        if (! $group) {
            return response()->json(['success' => false, 'message' => 'Group not found or you are not a member.'], 404);
        }

        // Fetch all chats in this group (including soft-deleted)
        $chats = Chat::withTrashed()->where('group_id', $group_id)->get();

        // Delete media files from storage
        foreach ($chats as $chat) {
            if ($chat->file) {
                $rawFile = $chat->getRawOriginal('file');
                if ($rawFile) {
                    Helper::fileDelete(public_path($rawFile));
                }
            }
        }

        // Force delete all chats in this group
        Chat::withTrashed()->where('group_id', $group_id)->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Group chat history cleared successfully.',
        ]);
    }

    /**
     * Delete a specific message (sender only).
     */
    public function deleteGroupMessage(int $message_id): JsonResponse
    {
        $userId = auth('api')->id();
        $chat   = Chat::where('id', $message_id)->where('sender_id', $userId)->first();

        if (! $chat) {
            return response()->json(['success' => false, 'message' => 'Message not found or you are not authorized.'], 404);
        }

        if ($chat->file) {
            $rawFile = $chat->getRawOriginal('file');
            if ($rawFile) {
                Helper::fileDelete(public_path($rawFile));
            }
        }

        $chat->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully.',
        ]);
    }

    /**
     * Delete multiple messages (sender only).
     */
    public function deleteMultipleGroupMessages(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message_ids'   => 'required|array',
            'message_ids.*' => 'exists:chats,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $userId = auth('api')->id();
        $chats  = Chat::whereIn('id', $request->message_ids)->where('sender_id', $userId)->get();

        if ($chats->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No authorized messages found to delete.'], 404);
        }

        foreach ($chats as $chat) {
            if ($chat->file) {
                $rawFile = $chat->getRawOriginal('file');
                if ($rawFile) {
                    Helper::fileDelete(public_path($rawFile));
                }
            }
            $chat->forceDelete();
        }

        return response()->json([
            'success' => true,
            'message' => count($chats) . ' messages deleted successfully.',
        ]);
    }

    /**
     * Get media shared in a group.
     * GET /api/group/chat/messages/{group_id}/media?type=media|files|voice|links|gifs
     */
    public function getGroupMedia(int $group_id, Request $request): JsonResponse
    {
        $userId = auth('api')->id();
        $group  = Group::forUser($userId)->find($group_id);

        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Group not found or you are not a member.'], 404);
        }

        $type = $request->get('type', 'media');
        $query = Chat::where('group_id', $group_id);

        // Apply filters based on type
        switch ($type) {
            case 'media':
                $query->where(function ($q) {
                    $q->where('file', 'LIKE', '%.jpeg')
                        ->orWhere('file', 'LIKE', '%.png')
                        ->orWhere('file', 'LIKE', '%.jpg')
                        ->orWhere('file', 'LIKE', '%.mp4')
                        ->orWhere('file', 'LIKE', '%.mov')
                        ->orWhere('file', 'LIKE', '%.avi')
                        ->orWhere('file', 'LIKE', '%.wmv');
                });
                break;

            case 'files':
                $query->where(function ($q) {
                    $q->where('file', 'LIKE', '%.pdf')
                        ->orWhere('file', 'LIKE', '%.doc')
                        ->orWhere('file', 'LIKE', '%.docx')
                        ->orWhere('file', 'LIKE', '%.zip')
                        ->orWhere('file', 'LIKE', '%.txt');
                });
                break;

            case 'voice':
                $query->where(function ($q) {
                    $q->where('file', 'LIKE', '%.mp3')
                        ->orWhere('file', 'LIKE', '%.wav')
                        ->orWhere('file', 'LIKE', '%.ogg')
                        ->orWhere('file', 'LIKE', '%.m4a')
                        ->orWhere('file', 'LIKE', '%.webm')
                        ->orWhere('file', 'LIKE', '%.aac')
                        ->orWhere('file', 'LIKE', '%.amr');
                });
                break;

            case 'gifs':
                $query->where('file', 'LIKE', '%.gif');
                break;

            case 'links':
                $query->where(function ($q) {
                    $q->where('text', 'LIKE', '%http://%')
                        ->orWhere('text', 'LIKE', '%https://%');
                });
                break;

            case 'posts':
                $query->where('text', 'LIKE', '%/post/show/%');
                break;

            default:
                return response()->json(['success' => false, 'message' => 'Invalid type'], 422);
        }

        $media = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => ucfirst($type) . ' retrieved successfully',
            'data'    => [
                'type'       => $type,
                'media'      => $media->items(),
                'pagination' => [
                    'current_page' => $media->currentPage(),
                    'last_page'    => $media->lastPage(),
                    'per_page'     => $media->perPage(),
                    'total'        => $media->total(),
                ],
            ],
        ]);
    }

    /**
     * Search messages within a specific group.
     * GET /api/group/chat/search/{group_id}?keyword=hello
     */
    public function searchGroupMessages(int $group_id, Request $request): JsonResponse
    {
        $userId = auth('api')->id();
        $group  = Group::forUser($userId)->find($group_id);

        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Group not found or you are not a member.'], 404);
        }

        $keyword = $request->get('keyword');

        if (empty($keyword)) {
            return response()->json(['success' => false, 'message' => 'Keyword is required'], 422);
        }

        $chats = Chat::where('group_id', $group_id)
            ->where('text', 'LIKE', "%{$keyword}%")
            ->with(['sender:id,first_name,last_name,cover'])
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
}
