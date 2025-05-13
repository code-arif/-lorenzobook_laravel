<?php

namespace App\Models;

use Exception;


use Twilio\Rest\Client;
use Illuminate\Database\Eloquent\Model;

class UserOtp extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function sendSMS($receiverNumber)
    {
        $message = "Login OTP is " . $this->otp;

        try {

            $account_sid = getenv("TWILIO_SID");
            $auth_token = getenv("TWILIO_TOKEN");
            $twilio_number = getenv("TWILIO_FROM");


            $client = new Client($account_sid, $auth_token);

            $client->messages->create($receiverNumber, [
                'from' => $twilio_number,
                'body' => $message
            ]);

            info('SMS Sent Successfully.');

            

        } catch (Exception $e) {

            info("Error: " . $e->getMessage());
        }
    }
}
