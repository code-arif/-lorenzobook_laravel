<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $twilio;

    public function __construct()
    {
        $this->twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
    }

    public function sendOtp($phone, $otp)
    {
        return $this->twilio->messages->create($phone, [
            'from' => config('services.twilio.from'),
            'body' => "Your ChatX OTP is: $otp"
        ]);
    }
}
