<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Models\User;
use App\Models\Friend;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FriendController extends Controller
{
    use ApiResponse;


    public function list(){

        $friends = Friend::with('user')->where('user_id', auth('api')->user()->id)->get() ;

        return $this->success($friends, 'Friend list fetched successfully');

    }


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

            // return response()->json(['message' => 'Friend request already exists or already friends.'], 409);
        }

        $friend =  Friend::create([
            'user_id' => $user_id,
            'friend_id' => $receiverId,
            'status' => 'accepted'
        ]);


        return $this->success($friend, 'Friend add to contacts', 201);
    }
}
