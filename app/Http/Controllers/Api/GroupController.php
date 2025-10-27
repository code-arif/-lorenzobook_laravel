<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Group;
use App\Helpers\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GroupController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    /**
     * List all groups the authenticated user belongs to.
     */
    public function list(Request $request)
    {
        $user = auth('api')->user();

        $groups = $user->groups()->with('members')->get();

        return $this->success($groups, 'Groups retrieved successfully');
    }

    /**
     * Create a new group.
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'image_url'   => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'group_type'  => 'in:private,public',
            'member_ids'  => 'array',
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
            'name'       => $request->name,
            'image_url'  => $photoPath,
            'group_type' => $request->get('group_type', 'private'),
            'created_by' => auth()->id(),
        ]);

        // Attach creator as admin
        $group->members()->attach(auth()->id(), [
            'role'      => 'admin',
            'joined_at' => now(),
        ]);

        // Attach additional members
        if ($request->filled('member_ids')) {
            foreach ($request->member_ids as $memberId) {
                $group->members()->attach($memberId, [
                    'role'      => 'member',
                    'joined_at' => now(),
                ]);
            }
        }

        return $this->success($group->load('members'), 'Group created successfully', 201);
    }



    // group show
    public function show($group_id)
    {

        $group = Group::with('members')->find($group_id);

        if (!$group) {
            return $this->error([], 'Group not found.', 404);
        }

        return $this->success($group, 'Group retrieved successfully.', 200);
    }

    /**
     * Update an existing group.
     */

    public function update(Request $request, $group_id)
    {
        $group = Group::find($group_id);

        if (!$group) {
            return $this->error([], 'Group not found.', 404);
        }

        if (Gate::denies('update', $group)) {
            return $this->error([], 'You are not authorized to update this group.', 403);
        }

        $validator = Validator::make($request->all(), [
            'name'       => 'sometimes|required|string|max:255',
            'image_url'  => 'sometimes|required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'group_type' => 'sometimes|in:private,public',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        if ($request->hasFile('image_url')) {
            $photoPath = Helper::fileUpload(
                $request->file('image_url'),
                'group',
                time() . '_' . getFileName($request->file('image_url'))
            );
            $group->image_url = $photoPath;
        }

        if ($request->filled('name')) {
            $group->name = $request->name;
        }

        if ($request->filled('group_type')) {
            $group->group_type = $request->group_type;
        }

        $group->save();

        return $this->success($group, 'Group updated successfully.', 200);
    }













    /**
     * Add members to an existing group.
     */
    public function addMember(Request $request, $group_id)
    {
        $group = Group::with('members')->find($group_id);

        if (!$group) {
            return $this->error([], 'Group not found.', 404);
        }

        if (Gate::denies('manage-members', $group)) {
            return $this->error([], 'You are not authorized to add members to this group.', 403);
        }

        $validator = Validator::make($request->all(), [
            'member_ids'   => 'required|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $alreadyExists = [];
        $added = [];

        foreach ($request->member_ids as $memberId) {
            if ($group->members->contains('id', $memberId)) {
                $alreadyExists[] = $memberId;
            } else {
                $group->members()->attach($memberId, [
                    'role'      => 'member',
                    'joined_at' => now(),
                ]);
                $added[] = $memberId;
            }
        }

        return $this->success([
            'added_members'     => $added,
            'already_members'   => $alreadyExists,
            'group'             => $group->load('members')
        ], 'Add member process completed.');
    }


    public function removeMember(Request $request, $group_id)
    {
        $group = Group::findOrFail($group_id);

        if (!$group) {
            return response()->json([
                'status' => false,
                'message' => 'Group not found.',
            ], 404);
        }

        // Check if the user is authorized to remove members
        if (Gate::denies('manage-members', $group)) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to remove members from this group.',
            ], 403);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'member_id' => 'required',

            ]
        );

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        // check member_ids not exists in group members
        // $notExists = [];
        // foreach ($request->member_ids as $memberId) {
        //     if (!$group->members->contains('id', $memberId)) {
        //         $notExists[] = $memberId;
        //     }
        // }
        // if (count($notExists) > 0) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Member(s) not found in group members.',
        //         'data' => $notExists,

        //     ], 422);
        // }


        // foreach ($request->member_ids as $memberId) {
        //     $group->members()->detach($memberId);
        // }



        return response()->json([
            'status' => true,
            'message' => 'Member removed successfully.',
            'data' => $group->load('members')
        ]);
    }


    // promote member
    public function promoteMember($group_id, $user_id)
    {
        $group = Group::findOrFail($group_id);
        $user = User::findOrFail($user_id);

        if (Gate::denies('manage-members', $group)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized to promote members.',
            ], 403);
        }

        $isMember = $group->members()->where('user_id', $user->id)->exists();
        if (!$isMember) {
            return response()->json([
                'status' => false,
                'message' => 'User is not a member of the group.',
            ], 404);
        }

        $currentRole = $group->members()->where('user_id', $user->id)->first()->pivot->role;
        if ($currentRole === 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'User is already an admin.',
                'data' => $group->load('members'),
            ], 409);
        }

        $group->members()->updateExistingPivot($user->id, ['role' => 'admin']);

        return response()->json([
            'status' => true,
            'message' => 'User promoted to admin.',
            'data' => $group->load('members'),

        ]);
    }





    /**
     * Delete a group.
     */

    public function destroy($group_id)
    {
        $group = Group::find($group_id);

        if (!$group) {
            return $this->error([], 'Group not found.', 404);
        }

        if (Gate::denies('delete', $group)) {
            return $this->error([], 'You are not authorized to delete this group.', 403);
        }

        $group->delete();

        return $this->success([], 'Group deleted successfully.', 200);
    }



    // leave member

    public function leaveMember(Request $request, $group_id)
    {
        $user = User::find($request->member_id);


        $group = Group::findOrFail($group_id);

        if (!$group) {
            return response()->json([
                'status' => false,
                'message' => 'Group not found.',
            ], 404);
        }

        if ($group->created_by === $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Group owner cannot leave the group.',
            ], 403);
        }

        $group->members()->detach($user->id);

        return response()->json([
            'status' => true,
            'message' => 'You have left the group.',
            'data' => $group->load('members'),
        ]);
    }


    // ban users

}
