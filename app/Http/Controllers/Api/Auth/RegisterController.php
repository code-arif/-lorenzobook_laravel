<?php

namespace App\Http\Controllers\Api\Auth;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\TwilioService;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;

class RegisterController extends Controller
{
    use ApiResponse;

    public $select;

    public function __construct()
    {
        $this->select = ['id', 'first_name', 'last_name', 'mobile_number', 'otp', 'cover'];
    }

    /**
     * Register or send OTP if user already exists
     */
    public function register(Request $request, TwilioService $twilio)
    {
        $request->validate([
            'mobile_number' => 'required|string|max:20',
        ]);

        try {
            $otp = rand(100000, 999999);
            $otpExpiresAt = Carbon::now()->addMinutes(10);

            $user = User::where('mobile_number', $request->mobile_number)->first();

            if ($user) {
                // Existing user - update OTP
                $user->update([
                    'otp' => $otp,
                    'otp_expires_at' => $otpExpiresAt,
                ]);

                $data = [
                    'user_id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'cover' => $user->cover,
                    'mobile_number' => $user->mobile_number,
                    'otp' => $user->otp,
                ];


                return $this->success($data, 'Existing User, OTP sent to your phone number.');
            } else {
                // New user - create and assign role
                $user = User::create([
                    'mobile_number' => $request->input('mobile_number'),
                    'otp' => $otp,
                    'otp_expires_at' => $otpExpiresAt,
                ]);
            }

            // Send OTP via Twilio
            // $twilio->sendOtp($user->mobile_number, $otp);


            $data = [
                'user_id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'cover' => $user->cover,
                'mobile_number' => $user->mobile_number,
                'otp' => $user->otp,
            ];


            return $this->success($data, 'New User, OTP sent to your phone number.');
        } catch (Exception $e) {
            return $this->error([$e->getMessage()], 'Registration failed');
        }
    }

    /**
     * Verify the phone number with OTP
     */
    public function verifyPhoneOtp(Request $request)
    {
        $request->validate([
            'mobile_number' => 'required|string|exists:users,mobile_number',
            'otp' => 'required|digits:6',
        ]);

        try {
            $user = User::where('mobile_number', $request->mobile_number)->first();

            if ($user->otp_verified_at) {
                return $this->error([], 'Phone number already verified.', 409);
            }

            if ($user->otp !== $request->otp) {
                return $this->error([], 'Invalid OTP.', 422);
            }

            if (Carbon::parse($user->otp_expires_at)->isPast()) {
                return $this->error([], 'OTP has expired.', 422);
            }

            $user->otp_verified_at = now();
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            $token = JWTAuth::fromUser($user);
            $user->token = $token;
            $user->is_new_user = empty($user->first_name) || empty($user->last_name);

            return $this->success($user, 'Phone verification successful.');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Resend OTP to existing user
     */
    public function resendPhoneOtp(Request $request, TwilioService $twilio)
    {
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|string|exists:users,mobile_number',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        try {
            $user = User::where('mobile_number', $request->input('mobile_number'))->first();

            if ($user->otp_verified_at) {
                return $this->error([], 'Phone number already verified.', 409);
            }

            $otp = rand(100000, 999999);
            $otpExpiresAt = Carbon::now()->addMinutes(10);

            $user->otp = $otp;
            $user->otp_expires_at = $otpExpiresAt;
            $user->save();

            // $twilio->sendOtp($user->mobile_number, $otp);

            return $this->success($user, 'OTP resent successfully.');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
