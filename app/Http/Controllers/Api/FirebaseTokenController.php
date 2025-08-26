<?php
namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use App\Models\FirebaseTokens;
use App\Models\User;
use App\Services\PushService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FirebaseTokenController extends Controller
{

    public function test()
    {
        $user = User::find(auth('api')->user()->id);
        if ($user && $user->firebaseTokens) {
            $notifyData = ['title' => "Payment Failed", 'body' => "test body", 'icon' => config('settings.logo')];
            foreach ($user->firebaseTokens as $firebaseToken) {
                Helper::sendNotifyMobile($firebaseToken->token, $notifyData);
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Token saved successfully',
            'data'    => $user->firebaseTokens,
            'code'    => 200,
        ], 200);
    }

    /**
     * News Serve For Frontend
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token'     => 'required|string',
            'device_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        //first delete existing token
        $firebase = FirebaseTokens::where('user_id', auth('api')->user()->id)->where('device_id', $request->device_id);
        if ($firebase) {
            $firebase->delete();
        }

        try {
            $data            = new FirebaseTokens();
            $data->user_id   = auth('api')->user()->id;
            $data->token     = $request->token;
            $data->device_id = $request->device_id;
            $data->status    = "active";
            $data->save();

            return response()->json([
                'status'  => true,
                'message' => 'Token saved successfully',
                'data'    => $data,
                'code'    => 200,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'No records found',
                'code'    => 418,
                'data'    => [],
            ], 418);
        }
    }

    /**
     * Get Single Record
     * @param $token, $device_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }
        $user_id   = auth('api')->user()->id;
        $device_id = $request->device_id;
        $data      = FirebaseTokens::where('user_id', $user_id)->where('device_id', $device_id)->first();
        if (! $data) {
            return response()->json([
                'status'  => false,
                'message' => 'No records found',
                'code'    => 404,
                'data'    => [],
            ], 404);
        }
        return response()->json([
            'status'  => true,
            'message' => 'Token fetched successfully',
            'data'    => $data,
            'code'    => 200,
        ], 200);
    }

    /**
     * Delete Token Single Record
     * @param $token, $device_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $user = FirebaseTokens::where('user_id', auth('api')->user()->id)->where('device_id', $request->device_id);
        if ($user) {
            $user->delete();
            return response()->json([
                'status'  => true,
                'message' => 'Token deleted successfully',
                'code'    => 200,
            ], 200);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'No records found',
                'code'    => 404,
            ], 404);
        }
    }

    // test token store
    public function test_token_store(Request $request)
    {
        $request->validate([
            'user_id'   => 'required|string',
            'fcm_token' => 'required|string',
        ]);

        FcmToken::updateOrCreate(
            ['user_id' => $request->user_id],
            ['fcm_token' => $request->fcm_token]
        );

        return response()->json(['message' => 'Token saved']);
    }

    public function sendCall(Request $request, PushService $push)
    {
        $request->validate([
            'receiver_id' => 'required',
            'caller_id'   => 'required',
            'caller_name' => 'required',
            'call_type'   => 'required|in:video,voice',
        ]);

        $tokenRow = FcmToken::where('user_id', $request->receiver_id)->first();
        if (! $tokenRow) {
            return response()->json(['message' => 'Receiver FCM token not found'], 404);
        }


        $callId     = (string) Str::uuid();

        $data = [
            'zego'        => 'true',
            'call_id'     => $callId,
            'caller_id'   => $request->caller_id,
            'caller_name' => $request->caller_name,
            'call_type'   => $request->call_type,
            'resource_id' => 'lorenzobook',
        ];

        try {
            $resp = $push->toToken(
                $tokenRow->fcm_token,
                $data,
                'Incoming Call',
                "{$request->caller_name} is calling"
            );

            return response()->json([
                'status'      => true,
                'call_id'     => $callId,

                'result'      => $resp,
            ]);
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'UNREGISTERED')) {
                $tokenRow->delete();
            }
            return response()->json([
                'status' => false,
                'error'  => $e->getMessage(),
            ], 500);
        }
    }

}
