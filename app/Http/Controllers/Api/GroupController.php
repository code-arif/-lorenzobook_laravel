<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Group;
use App\Helpers\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    use ApiResponse;

    // ─── Group CRUD ───────────────────────────────────────────────────────────

    public function list(Request $request): JsonResponse
    {
        $user   = auth('api')->user();
        $groups = $user->groups()
            ->with(['members:id,first_name,last_name,cover'])
            ->withCount('members')
            ->latest('last_activity_at')
            ->get();

        return $this->success($groups, 'Groups retrieved successfully');
    }

    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'image_url'    => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'group_type'   => 'in:private,public',
            'member_ids'   => 'array',
            'member_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $photoPath = Helper::fileUpload(
            $request->file('image_url'),
            'group',
            time() . '_' . getFileName($request->file('image_url'))
        );

        $group = Group::create([
            'name'             => $request->name,
            'image_url'        => $photoPath,
            'group_type'       => $request->get('group_type', 'private'),
            'created_by'       => auth()->id(),
            'last_activity_at' => now(),
        ]);

        // Attach creator as admin
        $group->members()->attach(auth()->id(), ['role' => 'admin', 'joined_at' => now()]);

        // Attach additional members
        if ($request->filled('member_ids')) {
            $members = collect($request->member_ids)
                ->unique()
                ->reject(fn($id) => $id == auth()->id())
                ->mapWithKeys(fn($id) => [$id => ['role' => 'member', 'joined_at' => now()]]);

            $group->members()->attach($members);
        }

        return $this->success($group->load('members'), 'Group created successfully', 201);
    }

    public function show(int $group_id): JsonResponse
    {
        $group = Group::with(['members:id,first_name,last_name,cover,last_activity_at,is_online', 'createdBy:id,first_name,last_name,cover,last_activity_at,is_online'])
            ->withCount('members')
            ->find($group_id);

        if (! $group) {
            return $this->error([], 'Group not found.', 404);
        }

        return $this->success($group, 'Group retrieved successfully.');
    }

    public function update(Request $request, int $group_id): JsonResponse
    {
        $group = Group::find($group_id);

        if (! $group) {
            return $this->error([], 'Group not found.', 404);
        }

        if (Gate::denies('update', $group)) {
            return $this->error([], 'You are not authorized to update this group.', 403);
        }

        $validator = Validator::make($request->all(), [
            'name'       => 'sometimes|required|string|max:255',
            'image_url'  => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'group_type' => 'sometimes|in:private,public',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        if ($request->hasFile('image_url')) {
            $group->image_url = Helper::fileUpload(
                $request->file('image_url'),
                'group',
                time() . '_' . getFileName($request->file('image_url'))
            );
        }

        $group->fill($request->only(['name', 'group_type']))->save();

        return $this->success($group, 'Group updated successfully.');
    }

    public function destroy(int $group_id): JsonResponse
    {
        $group = Group::find($group_id);

        if (! $group) {
            return $this->error([], 'Group not found.', 404);
        }

        if (Gate::denies('delete', $group)) {
            return $this->error([], 'You are not authorized to delete this group.', 403);
        }

        $group->delete();

        return $this->success([], 'Group deleted successfully.');
    }

    // ─── Member Management ────────────────────────────────────────────────────

    public function addMember(Request $request, int $group_id): JsonResponse
    {
        $group = Group::with('members:id')->find($group_id);

        if (! $group) {
            return $this->error([], 'Group not found.', 404);
        }

        if (Gate::denies('manage-members', $group)) {
            return $this->error([], 'You are not authorized to add members.', 403);
        }

        $validator = Validator::make($request->all(), [
            'member_ids'   => 'required|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $existingIds = $group->members->pluck('id')->toArray();
        $added       = [];
        $skipped     = [];

        foreach ($request->member_ids as $memberId) {
            if (in_array($memberId, $existingIds)) {
                $skipped[] = $memberId;
            } else {
                $group->members()->attach($memberId, ['role' => 'member', 'joined_at' => now()]);
                $added[] = $memberId;
            }
        }

        return $this->success([
            'added'   => $added,
            'skipped' => $skipped,
            'group'   => $group->load('members'),
        ], 'Add member process completed.');
    }

    public function removeMember(Request $request, int $group_id): JsonResponse
    {
        $group = Group::find($group_id);

        if (! $group) {
            return $this->error([], 'Group not found.', 404);
        }

        if (Gate::denies('manage-members', $group)) {
            return $this->error([], 'You are not authorized to remove members.', 403);
        }

        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $isMember = $group->members()->where('user_id', $request->member_id)->exists();
        if (! $isMember) {
            return $this->error([], 'User is not a member of this group.', 404);
        }

        $group->members()->detach($request->member_id);

        return $this->success($group->load('members'), 'Member removed successfully.');
    }

    public function leaveMember(Request $request, int $group_id): JsonResponse
    {
        $userId = auth('api')->id();
        $group  = Group::findOrFail($group_id);

        if ($group->created_by === $userId) {
            return $this->error([], 'Group owner cannot leave. Transfer ownership first.', 403);
        }

        $group->members()->detach($userId);

        return $this->success([], 'You have left the group.');
    }

    public function promoteMember(int $group_id, int $user_id): JsonResponse
    {
        return $this->updateMemberRole($group_id, $user_id, 'admin', 'User promoted to admin.');
    }

    public function demoteMember(int $group_id, int $user_id): JsonResponse
    {
        return $this->updateMemberRole($group_id, $user_id, 'member', 'User demoted to member.');
    }

    public function muteMember(Request $request, int $group_id): JsonResponse
    {
        return $this->toggleMemberFlag($group_id, $request->member_id, 'is_muted', true, 'Member muted.');
    }

    public function unmuteMember(Request $request, int $group_id): JsonResponse
    {
        return $this->toggleMemberFlag($group_id, $request->member_id, 'is_muted', false, 'Member unmuted.');
    }

    public function banMember(Request $request, int $group_id): JsonResponse
    {
        return $this->toggleMemberFlag($group_id, $request->member_id, 'is_banned', true, 'Member banned.');
    }

    public function unbanMember(Request $request, int $group_id): JsonResponse
    {
        return $this->toggleMemberFlag($group_id, $request->member_id, 'is_banned', false, 'Member unbanned.');
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function updateMemberRole(int $groupId, int $userId, string $role, string $message): JsonResponse
    {
        $group = Group::find($groupId);

        if (! $group) {
            return $this->error([], 'Group not found.', 404);
        }

        if (Gate::denies('manage-members', $group)) {
            return $this->error([], 'You are not authorized.', 403);
        }

        $member = $group->members()->where('user_id', $userId)->first();

        if (! $member) {
            return $this->error([], 'User is not a member of this group.', 404);
        }

        if ($member->pivot->role === $role) {
            return $this->error([], "User is already a {$role}.", 409);
        }

        $group->members()->updateExistingPivot($userId, ['role' => $role]);

        return $this->success($group->load('members'), $message);
    }

    private function toggleMemberFlag(int $groupId, int $userId, string $flag, bool $value, string $message): JsonResponse
    {
        $group = Group::find($groupId);

        if (! $group) {
            return $this->error([], 'Group not found.', 404);
        }

        if (Gate::denies('manage-members', $group)) {
            return $this->error([], 'You are not authorized.', 403);
        }

        $isMember = $group->members()->where('user_id', $userId)->exists();
        if (! $isMember) {
            return $this->error([], 'User is not a member of this group.', 404);
        }

        $group->members()->updateExistingPivot($userId, [$flag => $value]);

        return $this->success($group->load('members'), $message);
    }
}
