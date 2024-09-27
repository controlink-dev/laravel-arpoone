<?php

namespace Controlink\LaravelArpoone\Channels;

use Illuminate\Notifications\Notification;

class Arpoone
{
    public function send(object $notifiable,  Notification $notification)
    {
        //Check if the $notification has the toArpoone method
        if (!method_exists($notification, 'toArpoone')) {
            throw new \Exception('The notification must have a toArpoone method');
        }

        $message = $notification->toArpoone($notifiable);

        //Check if the $message has the parameters required by the Arpoone API
        if (!isset($message['to']) || !isset($message['content'])) {
            throw new \Exception('The message must have a to and content parameters');
        }

        // Send notification to the $notifiable instance
        // $message['to'] contains the phone number
        // $message['content'] contains the message

        $url = config('arpoone.url');
        $apiKey = config('arpoone.api_key');
        $organizationId = config('arpoone.organization_id');
        $sender = config('arpoone.sender');

        $messages = [
            'text' => $message['content'],
            'to' => $message['to'],
            'from' => $sender,
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $client = new \GuzzleHttp\Client();
        $response = $client->post($url . 'sms/send', [
            'headers' => $headers,
            'json' => [
                'organization_id' => $organizationId,
                'messages' => [$messages],
            ],
        ]);

        return $response->getBody();
    }
}