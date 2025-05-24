<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Models\Channel;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ChannelManageController extends Controller
{

    use ApiResponse;





    /**
     * List all channels the authenticated user belongs to.
     */
    public function list(Request $request)
    {
        $user = auth('api')->user();

        $channels = $user->channels()->with('members')->get();

        return response()->json([
            'success' => true,
            'data' => $channels,
            'message' => 'Channels retrieved successfully',
        ]);
    }
    /**
     * Create a new channel.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'image_url' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $photoPath = Helper::fileUpload(
            $request->file('image_url'),
            'channel',
            time() . '_' . getFileName($request->file('image_url'))
        );

        $slug = Str::slug($request->name) . '-' . Str::random(6);

        $channel = auth('api')->user()->channels()->create([
            'name' => $request->name,
            'slug' => $slug,
            'image_url' => $photoPath,
            'description' => $request->description,
            'created_by' => auth('api')->id(),
            'channel_type' => $request->get('channel_type', 'private'),
            'invite_token' => null,
        ]);

        if (!$channel) {
            return $this->error([], 'Channel creation failed', 500);
        }

        return $this->success($channel, 'Channel created successfully', 201);
    }

    // channel type
    public function setType(Request $request, $channelId)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:public,private',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $channel = Channel::where('id', $channelId)
            ->where('created_by', auth('api')->id())
            ->first();

        if (!$channel) {
            return $this->error([], 'Channel not found or unauthorized', 404);
        }

        $channel->channel_type = $request->type;

        if ($request->type === 'private') {
            $channel->invite_token = Str::random(32);
        }

        $channel->save();

        $link = $channel->channel_type === 'public'
            ? url("/channels/join/{$channel->slug}")
            : url("/invite/{$channel->invite_token}");

        return $this->success([
            'channel' => $channel,
            'join_link' => $link
        ], 'Channel type set successfully');
    }
}
