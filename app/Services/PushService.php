<?php

namespace App\Services;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class PushService
{
    public function __construct(private Messaging $messaging)
    {}

    public function toToken(string $token, array $data = [], ?string $title = null, ?string $body = null)
    {
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(Notification::create($title ?? '', $body ?? ''))
            ->withData($data)
            ->withAndroid([
                'priority' => 'high',
                'ttl'      => '4500s',
            ])
            ->withApns([
                'headers' => ['apns-priority' => '10'],
                'payload' => ['aps' => ['sound' => 'default']],
            ]);

        return $this->messaging->send($message);
    }
}
