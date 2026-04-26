<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Friend;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FriendController extends Controller
{
    use ApiResponse;

    /**
     * Get friend list
     */
    public function list(){
        $friends = Friend::with('friend')->where('user_id', auth('api')->user()->id)->get() ;
        return $this->success($friends, 'Friend list fetched successfully');
    }

    /**
     * Get friend list with group check (excludes friends already in the group)
     */
    public function listWithGroupCheck(Request $request)
    {
        $userId = auth('api')->id();
        $groupId = $request->group_id;

        $query = Friend::with('friend')->where('user_id', $userId);

        if ($groupId) {
            $memberIds = DB::table('group_members')
                ->where('group_id', $groupId)
                ->pluck('user_id')
                ->toArray();

            $query->whereNotIn('friend_id', $memberIds);
        }

        $friends = $query->get();

        return $this->success($friends, 'Friend list fetched successfully');
    }

    /**
     * Send friend request
     */
    public function send(Request $request)
    {
        $user_id = auth('api')->id();
        $receiverId = $request->receiver_id;

        if ($user_id == $receiverId) {

            return $this->error([], 'You cannot add yourself', 422);
        }

        // Prevent duplicate or spammy requests
        $existing = Friend::where(function ($q) use ($user_id, $receiverId) {
            $q->where('user_id', $user_id)->where('friend_id', $receiverId);
        })->orWhere(function ($q) use ($user_id, $receiverId) {
            $q->where('user_id', $receiverId)->where('friend_id', $user_id);
        })->first();

        if ($existing) {
            return $this->error($existing, 'Friend request already exists or already friends', 422);
        }

        $friend =  Friend::create([
            'user_id' => $user_id,
            'friend_id' => $receiverId,
            'status' => 'accepted'
        ]);

        return $this->success($friend, 'Friend add to contacts', 201);
    }

    /**
     * Get friend details
     */
    public function details($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error([], 'User  not found', 404);
        }

        $data = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'cover' => $user->cover,
            'last_activity_at' => $user->last_activity_at,
            'is_friend' => Friend::where('user_id', auth('api')->id())
                ->where('friend_id', $id)
                ->exists(),
        ];

        return $this->success($data, 'Friend details fetched successfully');
    }
}
