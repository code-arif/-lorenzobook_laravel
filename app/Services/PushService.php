<?php

namespace App\Services;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class PushService
{
    public function __construct(private Messaging $messaging) {}

    public function toToken(string $token, array $data = [], ?string $title = null, ?string $body = null)
    {
        $payload = [
            'token' => $token,
            'notification' => [
                'title' => $title ?? '',
                'body'  => $body ?? '',
            ],
            'data' => array_map('strval', $data),
            'android' => [
                'priority' => 'high',
                'ttl'      => '4500s',
                'notification' => [
                    'sound' => 'default',
                    'channel_id' => 'incoming_calls',
                ],
            ],
            'apns' => [
                'headers' => ['apns-priority' => '10'],
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                    ],
                ],
            ],
        ];

        $message = CloudMessage::fromArray($payload);

        return $this->messaging->send($message);
    }
}
