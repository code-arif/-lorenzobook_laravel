<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Chat;
use App\Models\User;
use App\Models\Group;
use App\Helpers\Helper;
use App\Events\GroupMessageSentEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class GroupChatController extends Controller
{
    // ─── Views ────────────────────────────────────────────────────────────────

    public function index(): View
    {
        return view('backend.layouts.chat.group');
    }

    // ─── Group Management (Admin) ─────────────────────────────────────────────

    /** List all groups with member count and last message info */
    public function list(): JsonResponse
    {
        $authUser = Auth::user();

        $groups = Group::whereHas('members', fn($q) => $q->where('user_id', $authUser->id))
            ->with(['createdBy:id,first_name,last_name'])
            ->withCount('members')
            ->latest('last_activity_at')
            ->get()
            ->map(fn($group) => [
                'id'               => $group->id,
                'name'             => $group->name,
                'cover'            => $group->image_url ? url($group->image_url) : null,
                'group_type'       => $group->group_type,
                'created_by'       => $group->createdBy?->first_name . ' ' . $group->createdBy?->last_name,
                'is_owner'         => $group->created_by === $authUser->id,
                'is_active'        => $group->is_active,
                'last_activity_at' => $group->last_activity_at,
                'members_count'    => $group->members_count,
                'type'             => 'group',
            ]);

        return response()->json(['success' => true, 'data' => ['groups' => $groups]]);
    }

    /** Get all users (for add-member dropdown) */
    public function userList(): JsonResponse
    {
        $users = User::select('id', 'first_name', 'last_name', 'email', 'cover')
            ->latest()
            ->get()
            ->map(fn($u) => [
                'id'     => $u->id,
                'name'   => $u->first_name . ' ' . $u->last_name,
                'email'  => $u->email,
                'avatar' => $u->cover ? url($u->cover) : null,
            ]);

        return response()->json(['success' => true, 'data' => ['users' => $users]]);
    }

    /** Create a new group */
    public function createGroup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'image_url'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'group_type'   => 'in:private,public',
            'member_ids'   => 'array',
            'member_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $photoPath = null;
        if ($request->hasFile('image_url')) {
            $photoPath = Helper::fileUpload(
                $request->file('image_url'),
                'group',
                time() . '_' . getFileName($request->file('image_url'))
            );
        }

        $authId = Auth::id();

        $group = Group::create([
            'name'             => $request->name,
            'image_url'        => $photoPath,
            'group_type'       => $request->get('group_type', 'private'),
            'created_by'       => $authId,
            'last_activity_at' => now(),
        ]);

        $group->members()->attach($authId, ['role' => 'admin', 'joined_at' => now()]);

        if ($request->filled('member_ids')) {
            $members = collect($request->member_ids)
                ->unique()
                ->reject(fn($id) => $id == $authId)
                ->mapWithKeys(fn($id) => [$id => ['role' => 'member', 'joined_at' => now()]]);

            $group->members()->attach($members);
        }

        return response()->json([
            'success' => true,
            'message' => 'Group created successfully.',
            'data'    => ['group' => $group->load('members')],
        ], 201);
    }

    /** Get group details with members */
    public function groupDetails(int $group_id): JsonResponse
    {
        $authUser = Auth::user();

        $group = Group::with([
            'createdBy:id,first_name,last_name,cover',
            'members' => fn($q) => $q->select('users.id', 'first_name', 'last_name', 'email', 'cover', 'last_activity_at')
                ->withPivot(['role', 'is_muted', 'is_banned', 'joined_at']),
        ])
            ->withCount('members')
            ->find($group_id);

        if (! $group) {
            return response()->json(['success' => false, 'message' => 'Group not found.'], 404);
        }

        $isAdmin = $group->members->contains(fn($m) => $m->id === $authUser->id && $m->pivot->role === 'admin');

        return response()->json([
            'success' => true,
            'data'    => [
                'group'    => [
                    'id'               => $group->id,
                    'name'             => $group->name,
                    'cover'            => $group->image_url ? url($group->image_url) : null,
                    'group_type'       => $group->group_type,
                    'is_active'        => $group->is_active,
                    'created_by'       => $group->createdBy?->first_name . ' ' . $group->createdBy?->last_name,
                    'is_owner'         => $group->created_by === $authUser->id,
                    'is_admin'         => $isAdmin,
                    'members_count'    => $group->members_count,
                    'last_activity_at' => $group->last_activity_at,
                ],
                'members' => $group->members->map(fn($m) => [
                    'id'        => $m->id,
                    'name'      => $m->first_name . ' ' . $m->last_name,
                    'email'     => $m->email,
                    'avatar'    => $m->cover ? url($m->cover) : null,
                    'role'      => $m->pivot->role,
                    'is_muted'  => $m->pivot->is_muted,
                    'is_banned' => $m->pivot->is_banned,
                    'joined_at' => $m->pivot->joined_at,
                    'is_me'     => $m->id === $authUser->id,
                ]),
            ],
        ]);
    }

    /** Update group name/image/type */
    public function updateGroup(Request $request, int $group_id): JsonResponse
    {
        $group = Group::find($group_id);

        if (! $group) {
            return response()->json(['success' => false, 'message' => 'Group not found.'], 404);
        }

        if ($group->created_by !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Only the group owner can update group info.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'       => 'sometimes|required|string|max:255',
            'image_url'  => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'group_type' => 'sometimes|in:private,public',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        if ($request->hasFile('image_url')) {
            $group->image_url = Helper::fileUpload(
                $request->file('image_url'),
                'group',
                time() . '_' . getFileName($request->file('image_url'))
            );
        }

        $group->fill($request->only(['name', 'group_type']))->save();

        return response()->json(['success' => true, 'message' => 'Group updated.', 'data' => $group]);
    }

    /** Delete a group (owner only) */
    public function deleteGroup(int $group_id): JsonResponse
    {
        $group = Group::find($group_id);

        if (! $group) {
            return response()->json(['success' => false, 'message' => 'Group not found.'], 404);
        }

        if ($group->created_by !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Only the group owner can delete this group.'], 403);
        }

        $group->delete();

        return response()->json(['success' => true, 'message' => 'Group deleted successfully.']);
    }

    /** Add a member to the group */
    public function addMember(Request $request, int $group_id): JsonResponse
    {
        $group = Group::with('members:id')->find($group_id);

        if (! $group) {
            return response()->json(['success' => false, 'message' => 'Group not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        if ($group->members->contains('id', $request->user_id)) {
            return response()->json(['success' => false, 'message' => 'User is already a member.'], 409);
        }

        $group->members()->attach($request->user_id, ['role' => 'member', 'joined_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Member added successfully.']);
    }

    /** Remove a member from the group */
    public function removeMember(Request $request, int $group_id): JsonResponse
    {
        $group = Group::find($group_id);

        if (! $group) {
            return response()->json(['success' => false, 'message' => 'Group not found.'], 404);
        }

        $authId = Auth::id();
        $isAdmin = $group->members()->where('user_id', $authId)->where('role', 'admin')->exists();

        if (! $isAdmin && $group->created_by !== $authId) {
            return response()->json(['success' => false, 'message' => 'Not authorized.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $group->members()->detach($request->user_id);

        return response()->json(['success' => true, 'message' => 'Member removed successfully.']);
    }

    /** Toggle ban/mute on a member */
    public function toggleMemberStatus(Request $request, int $group_id): JsonResponse
    {
        $group = Group::find($group_id);

        if (! $group) {
            return response()->json(['success' => false, 'message' => 'Group not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'action'  => 'required|in:ban,unban,mute,unmute,promote,demote',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $pivotUpdate = match ($request->action) {
            'ban'     => ['is_banned' => true],
            'unban'   => ['is_banned' => false],
            'mute'    => ['is_muted'  => true],
            'unmute'  => ['is_muted'  => false],
            'promote' => ['role'      => 'admin'],
            'demote'  => ['role'      => 'member'],
        };

        $group->members()->updateExistingPivot($request->user_id, $pivotUpdate);

        return response()->json(['success' => true, 'message' => ucfirst($request->action) . ' successful.']);
    }

    // ─── Chat ─────────────────────────────────────────────────────────────────

    /** Search groups by name */
    public function search(Request $request): JsonResponse
    {
        $authUser = Auth::user();
        $keyword  = $request->get('keyword', '');

        $groups = Group::whereHas('members', fn($q) => $q->where('user_id', $authUser->id))
            ->when($keyword, fn($q) => $q->where('name', 'LIKE', "%{$keyword}%"))
            ->with(['createdBy:id,first_name'])
            ->withCount('members')
            ->latest('last_activity_at')
            ->get()
            ->map(fn($g) => [
                'id'            => $g->id,
                'name'          => $g->name,
                'cover'         => $g->image_url ? url($g->image_url) : null,
                'members_count' => $g->members_count,
                'type'          => 'group',
            ]);

        return response()->json(['success' => true, 'data' => ['groups' => $groups]]);
    }

    /** Get paginated messages for a group */
    public function messages(int $group_id, Request $request): JsonResponse
    {
        $authUser = Auth::user();
        $group    = Group::whereHas('members', fn($q) => $q->where('user_id', $authUser->id))->find($group_id);

        if (! $group) {
            return response()->json(['success' => false, 'message' => 'Group not found or you are not a member.'], 404);
        }

        $messages = Chat::where('group_id', $group_id)
            ->with(['sender:id,first_name,last_name,email,cover,last_activity_at'])
            ->oldest()
            ->paginate($request->integer('per_page', 50));

        // Mark messages as read
        Chat::where('group_id', $group_id)
            ->where('sender_id', '!=', $authUser->id)
            ->where('status', 'sent')
            ->update(['status' => 'read']);

        return response()->json([
            'success' => true,
            'data'    => [
                'group' => [
                    'id'            => $group->id,
                    'name'          => $group->name,
                    'cover'         => $group->image_url ? url($group->image_url) : null,
                    'last_activity_at' => $group->last_activity_at,
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
     * Send a message to a group.
     * KEY FIX: was saving to room_id — now correctly saves to group_id.
     * This was the root cause of messages not appearing for other members.
     */
    public function sendMessage(int $group_id, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'nullable|string|max:1000',
            'file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        if (! $request->filled('text') && ! $request->hasFile('file')) {
            return response()->json(['success' => false, 'message' => 'Message or file is required.'], 422);
        }

        $sender = Auth::user();
        $group  = Group::whereHas('members', fn($q) => $q->where('user_id', $sender->id))->find($group_id);

        if (! $group) {
            return response()->json(['success' => false, 'message' => 'Group not found or you are not a member.'], 404);
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
            'sender_id'   => $sender->id,
            'receiver_id' => null,        // No receiver for group chat
            'group_id'    => $group->id,  // ← THE FIX (was 'room_id' before)
            'text'        => $request->text,
            'file'        => $file,
            'status'      => 'sent',
        ]);

        $group->update(['last_activity_at' => now()]);

        $chat->load(['sender:id,first_name,last_name,email,cover,last_activity_at']);

        broadcast(new GroupMessageSentEvent($chat))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully.',
            'data'    => ['chat' => $chat],
        ]);
    }
}
