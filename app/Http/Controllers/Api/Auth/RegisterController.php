<?php

namespace App\Http\Controllers\Api\Auth;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\OtpMail;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Services\TwilioService;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Events\RegistrationNotificationEvent;
use App\Notifications\RegistrationNotification;

class RegisterController extends Controller
{
    public $select;

    public function __construct()
    {
        $this->select = ['id', 'first_name', 'last_name', '
        mobile_number', 'otp', 'cover'];
    }

    public function register(Request $request, TwilioService $twilio)
    {
        $request->validate([
            'mobile_number' => 'required|string|max:20|unique:users',
            // 'role' => 'required|exists:roles,id',
        ]);

        try {

            $otp = rand(100000, 999999);
            $otpExpiresAt = Carbon::now()->addMinutes(10);

            $user = User::create([
                'mobile_number'     => $request->input('mobile_number'),
                'otp'              => $otp,
                'otp_expires_at'   => $otpExpiresAt,
            ]);

            DB::table('model_has_roles')->insert([
                'role_id' => 4,
                'model_type' => 'App\Models\User',
                'model_id' => $user->id
            ]);

            // Send SMS OTP
            // $twilio->sendOtp($user->mobile_number, $otp);

            // return response()->json([
            //     'status'  => true,
            //     'message' => 'OTP sent to your phone number.',
            //     'user_id' => $user->id,
            // ]);

            return Helper::jsonResponse(true, 'OTP sent to your phone number.', 200, $user);
            
        } catch (Exception $e) {
            return Helper::jsonErrorResponse('Registration failed', 500, [$e->getMessage()]);
        }
    }





    public function verifyPhoneOtp(Request $request)
    {
        $request->validate([
            'mobile_number' => 'required|string|exists:users,mobile_number',
            'otp' => 'required|digits:6',
        ]);

        try {
            $user = User::where('mobile_number', $request->mobile_number)->first();

            if ($user->otp_verified_at) {
                return Helper::jsonErrorResponse('Phone number already verified.', 409);
            }

            if ($user->otp !== $request->otp) {
                return Helper::jsonErrorResponse('Invalid OTP.', 422);
            }

            if (Carbon::parse($user->otp_expires_at)->isPast()) {
                return Helper::jsonErrorResponse('OTP has expired.', 422);
            }

            $user->otp_verified_at = now();
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            // Create JWT token
            $token = JWTAuth::fromUser($user);

            // Append token to the user object (if you're returning as an array)
            $user->token = $token;

            $user->is_new_user = empty($user->first_name) || empty($user->last_name);

            return Helper::jsonResponse(true, 'Phone verification successful.', 200, $user);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }


    public function resendPhoneOtp(Request $request, TwilioService $twilio)
    {
        $request->validate([
            'mobile_number' => 'required|string|exists:users,mobile_number',
        ]);

        try {
            $user = User::where('mobile_number', $request->input('mobile_number'))->first();

            if ($user->otp_verified_at) {
                return Helper::jsonErrorResponse('Phone number already verified.', 409);
            }

            $otp = rand(100000, 999999);
            $otpExpiresAt = Carbon::now()->addMinutes(10);

            $user->otp = $otp;
            $user->otp_expires_at = $otpExpiresAt;
            $user->save();

            $twilio->sendOtp($user->mobile_number, $otp);

            return Helper::jsonResponse(true, 'OTP resent successfully.', 200);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
