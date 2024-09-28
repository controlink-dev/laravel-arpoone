<?php

namespace Controlink\LaravelArpoone\Channels;

use Illuminate\Notifications\Notification;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Arpoone
{
    protected $client;

    public function __construct()
    {
        // Initialize Guzzle client
        $this->client = new Client();
    }

    public function send(object $notifiable, Notification $notification)
    {
        // Check if the $notification has the toArpoone method
        if (!method_exists($notification, 'toArpoone')) {
            throw new \Exception('The notification must have a toArpoone method.');
        }

        // Retrieve message data from notification
        $message = $notification->toArpoone($notifiable);

        // Check if the message has the required parameters
        if (!isset($message['to']) || !isset($message['content'])) {
            throw new \Exception('The message must have "to" and "content" parameters.');
        }

        // Retrieve configuration settings
        $url = config('arpoone.url');
        $apiKey = config('arpoone.api_key');
        $organizationId = config('arpoone.organization_id');
        $sender = config('arpoone.sender');
        $verifySsl = config('arpoone.verify_ssl', true); // Default is true

        // Ensure required config values are set
        if (empty($apiKey) || empty($organizationId) || empty($sender)) {
            throw new \Exception('Arpoone configuration values are missing. Ensure API key, organization ID, and sender are set, check config/arpoone.php file for more information.');
        }

        // Prepare the message payload
        $messages = [
            'text' => $message['content'],
            'to' => $message['to'],
            'from' => $sender,
        ];

        // Prepare request headers
        $headers = [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $body = [
                'organization_id' => $organizationId,
                'messages' => [$messages],
        ];

        //Check if webhooks are enabled
        if(config('arpoone.webhooks', false)){
            foreach($messages as $message){
                $message['smsWebhooks'] = [
                    "defaultUrl" => route('arpoone.webhook.sms.status'),
                    "delivered" => [
                        "url" => route('arpoone.webhook.sms.status', ['status' => 'delivered']),
                        "enabled" => true
                    ],
                    "not_Delivered" => [
                        "url" => route('arpoone.webhook.sms.status', ['status' => 'not_delivered']),
                        "enabled" => true
                    ],
                    "pending" => [
                        "url" => route('arpoone.webhook.sms.status', ['status' => 'pending']),
                        "enabled" => true
                    ],
                ];
            }
        }


        try {
            // Send the request to Arpoone API
            $response = $this->client->post($url . 'sms/send', [
                'headers' => $headers,
                'json' => $body,
                'verify' => $verifySsl,
            ]);

            // Return the response body
            return json_decode($response->getBody()->getContents(), true);

        } catch (RequestException $e) {
            // Catch and handle HTTP request errors
            throw new \Exception('Failed to send SMS: ' . $e->getMessage());
        }
    }
}