<?php

namespace App\Http\Controllers\Api;

use App\Models\Group;
use App\Helpers\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{

    use ApiResponse;

    // list

    public function index(Request $request)
    {
        $user = auth()->user();
        $groups = $user->groups()->with('members')->get();

        return response()->json([
            'status' => true,
            'message' => 'Groups retrieved successfully',
            'data' => $groups,
        ]);
    }

    // create group
    public function create(Request $request)
    {

        


        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'image_url' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'group_type' => 'in:private,public',
                'member_ids' => 'array',
                'member_ids.*' => 'exists:users,id'
            ]
        );

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        // Handle photo upload
        $photoPath = Helper::fileUpload($request->file('image_url'), 'group', time() . '_' . getFileName($request->file('image_url')));


        $group = Group::create([
            'name' => $request->name,
            'image_url' => $photoPath,
            'group_type' => $request->group_type ?? 'private',
            'created_by' => auth()->id(),
        ]);

        // Add creator as admin
        $group->members()->attach(auth()->id(), [
            'role' => 'admin',
            'joined_at' => now()
        ]);

        // Add other members
        foreach ($request->member_ids as $memberId) {
            $group->members()->attach($memberId, [
                'role' => 'member',
                'joined_at' => now()
            ]);
        }

        return response()->json([
            'message' => 'Group created successfully.',
            'group' => $group->load('members')
        ], 201);
    }
}
