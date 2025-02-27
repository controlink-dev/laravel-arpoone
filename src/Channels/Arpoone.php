<?php

namespace Controlink\LaravelArpoone\Channels;

use Controlink\LaravelArpoone\Models\ArpooneConfiguration;
use Controlink\LaravelArpoone\Models\ArpooneEmailsLog;
use Controlink\LaravelArpoone\Models\ArpooneSmsLog;
use Dflydev\DotAccessData\Data;
use Illuminate\Notifications\Notification;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;

class Arpoone
{
    protected $client;

    public function __construct($tenant_id = null)
    {
        // Initialize Guzzle client
        $this->client = new Client();
        $this->tenant_id = $tenant_id;
    }

    /**
     * Send the given notification.
     *
     * @throws \Exception
     */
    public function send(object $notifiable, Notification $notification)
    {
        // Check if the $notification has the toArpoone method
        if (!method_exists($notification, 'toArpoone')) {
            throw new \Exception('The notification must have a toArpoone method.');
        }

        // Retrieve message data from notification
        $message = $notification->toArpoone($notifiable);

        if(!is_array($message)){
            throw new \Exception('The toArpoone method must return an array.');
        }

        if(isset($message['type']) && $message['type'] == 'sms'){
            return $this->sendSms($notifiable, $message, $this->getAPIConfiguration());
        }

        if(isset($message['type']) && $message['type'] == 'email'){
            return $this->sendEmail($notifiable, $message, $this->getAPIConfiguration());
        }

        throw new \Exception('The message type must be either "sms" or "email".');
    }

    /**
     * Send a sms notification using the Arpoone API.
     *
     * @param $notifiable
     * @param $message
     * @param $configuration
     * @return mixed
     * @throws \Exception
     */
    protected function sendSms($notifiable, $message, $configuration)
    {
        //Check if routeNotificationForArpoone is implemented in the notifiable
        if (!method_exists($notifiable, 'routeNotificationForArpooneSMS')) {
            throw new \Exception('The notifiable entity must implement the routeNotificationForArpoone method.');
        }

        // Get the phone number from the notifiable using routeNotificationForArpooneSMS
        $recipientPhoneNumber = $notifiable->routeNotificationForArpooneSMS();

        // Ensure the recipient phone number exists
        if (!$recipientPhoneNumber) {
            throw new \Exception('The notifiable entity does not have a valid phone number.');
        }

        // Validate the recipient phone number
        $recipientPhoneNumber = $this->validatePhoneNumber($recipientPhoneNumber);

        // Check if the message has the required content parameter
        if (!isset($message['content'])) {
            throw new \Exception('The message must have a "content" parameter.');
        }

        // Prepare the message payload
        $messages = [
            'text' => $message['content'],
            'to' => $recipientPhoneNumber,
            'from' => $configuration->sms_sender,
        ];

        //Check if webhooks are enabled
        if (config('arpoone.sms_webhooks', false)) {
            $messages['smsWebhooks'] = [
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
                    "enabled" => false
                ],
            ];
        }

        $payload = [
            'organizationId' => $configuration->organization_id,
            'messages' => [$messages],
        ];

        return $this->sendRequest($configuration->url . 'sms/send', $configuration->api_key, $payload, $configuration->verify_ssl, 'sms');
    }

    /**
     * Send an email notification using the Arpoone API.
     *
     * @param $notifiable
     * @param $message
     * @param $configuration
     * @return mixed
     * @throws \Exception
     */
    protected function sendEmail($notifiable, $message, $configuration)
    {
        //Check if routeNotificationForArpoone is implemented in the notifiable
        if (!method_exists($notifiable, 'routeNotificationForArpooneEmail')) {
            throw new \Exception('The notifiable entity must implement the routeNotificationForArpoone method.');
        }

        // Get the email address from the notifiable using routeNotificationForArpooneEmail
        $recipientEmail = $notifiable->routeNotificationForArpooneEmail();

        // Ensure the recipient email exists
        if (!$recipientEmail) {
            throw new \Exception('The notifiable entity does not have a valid email address.');
        }

        // Check if the message has the required content parameter
        if (!isset($message['htmlContent'])) {
            throw new \Exception('The message must have a "htmlContent" parameter.');
        }

        $textContent = isset($message['textContent']) ? $message['textContent'] : strip_tags($message['htmlContent']);

        // Prepare the message payload
        $messages = [
            'to' => $recipientEmail,
            'from' => $configuration->email_sender,
            'displayName' => $configuration->email_sender_name,
            'subject' => $message['subject'],
            'textContent' => $textContent,
            'htmlContent' => $message['htmlContent'],
        ];

        //Check if existing attachments
        if(isset($message['attachments'])){
            //The attachment should contain mimeType, name and base64Content
            //It should not bypass 10MB
            if(!is_array($message['attachments'])){
                throw new \Exception('The attachments parameter must be an array.');
            }

            $attachments = [];
            foreach($message['attachments'] as $attachment){
                if(!isset($attachment['mimeType']) || !isset($attachment['name']) || !isset($attachment['base64Content'])){
                    throw new \Exception('The attachment must contain mimeType, name and base64Content.');
                }

                if(!$this->checkAttachmentSize($attachment['base64Content'])){
                    throw new \Exception('The attachment size should not exceed 5MB.');
                }


                $attachments[] = [
                    'mimeType' => $attachment['mimeType'],
                    'name' => $attachment['name'],
                    'base64Content' => $attachment['base64Content']
                ];
            }

            $messages['attachments'] = $attachments;
        }

        //Check if webhooks are enabled
        if(config('arpoone.email_webhooks', false)){
            foreach($messages as $message){
                $message['emailWebhooks'] = [
                    "blocked" => [
                        "url" => route('arpoone.webhook.email.status', ['status' => 'blocked']),
                        "enabled" => true
                    ],
                    "bounced" => [
                        "url" => route('arpoone.webhook.email.status', ['status' => 'bounced']),
                        "enabled" => true
                    ],
                    "clicked" => [
                        "url" => route('arpoone.webhook.email.status', ['status' => 'clicked']),
                        "enabled" => true
                    ],
                    "opened" => [
                        "url" => route('arpoone.webhook.email.status', ['status' => 'opened']),
                        "enabled" => true
                    ],
                    "spam" => [
                        "url" => route('arpoone.webhook.email.status', ['status' => 'spam']),
                        "enabled" => true
                    ],
                    "unsubscribed" => [
                        "url" => route('arpoone.webhook.email.status', ['status' => 'unsubscribed']),
                        "enabled" => true
                    ],
                ];
            }
        }

        // Prepare the message payload
        $payload = [
            'organizationId' => $configuration->organization_id,
            'messages' => [$messages],
        ];

        return $this->sendRequest($configuration->url . 'email/send', $configuration->api_key, $payload, $configuration->verify_ssl, 'email');
    }

    /**
     * Check if the phone number is a valid mobile number and contains the country code without the "+".
     *
     * @param string $phoneNumber
     * @return string
     * @throws \Exception
     */
    protected function validatePhoneNumber(string $phoneNumber)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            // Suponha que o código do país seja incluído no número, mas sem o "+"
            $parsedPhoneNumber = $phoneUtil->parse($phoneNumber, null);

            // Verifica se o número é válido
            if (!$phoneUtil->isValidNumber($parsedPhoneNumber)) {
                throw new \Exception('Invalid phone number format.');
            }

            // Verifica se o número é móvel e não uma linha fixa
            if ($phoneUtil->getNumberType($parsedPhoneNumber) != PhoneNumberType::MOBILE) {
                throw new \Exception('The phone number is not a mobile number.');
            }

            // Retorna o número formatado internacionalmente (sem o "+")
            return str_replace('+', '', $phoneUtil->format($parsedPhoneNumber, PhoneNumberFormat::E164));
        } catch (NumberParseException $e) {
            throw new \Exception('Failed to parse phone number: ' . $e->getMessage());
        }
    }

    /**
     * Obtain the Arpoone API configuration settings.
     *
     * @return object
     * @throws \Exception
     */
    protected function getAPIConfiguration()
    {
        if(config('arpoone.multi_tenant', false)){
            if(!$this->tenant_id){
                throw new \Exception('Tenant ID is required for multi-tenant applications.');
            }

            $configuration = ArpooneConfiguration::where(config('arpoone.tenant_column_name','tenant_id'), $this->tenant_id)->first();

            if(!$configuration){
                throw new \Exception('Tenant configuration for arpoone not found.');
            }

            return $configuration;
        }

        return (object) [
            'url' => config('arpoone.url'),
            'api_key' => config('arpoone.api_key'),
            'organization_id' => config('arpoone.organization_id'),
            'sms_sender' => config('arpoone.sms_sender'),
            'email_sender' => config('arpoone.email_sender'),
            'email_sender_name' => config('arpoone.email_sender_name'),
            'verify_ssl' => config('arpoone.verify_ssl', true),
        ];
    }

    /**
     * Send a request to the Arpoone API.
     *
     * @param string $url
     * @param $api_key
     * @param array $payload
     * @param $verify_ssl
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendRequest(string $url, $api_key, array $payload, $verify_ssl, $type)
    {
        try {
            // Send the request to Arpoone API
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $payload,
                'verify' => $verify_ssl,
            ]);

            $responseJson = json_decode($response->getBody()->getContents(), true);

            if($type == 'sms' && config('arpoone.log_sms', false)){
                // Log the SMS in the database
                $sms = new ArpooneSmsLog();
                $sms->fill([
                    'message_id' => $responseJson["messages"][0]["messageId"],
                    'recipient_number' => $payload['messages'][0]['to'],
                    'message' => $payload['messages'][0]['text'],
                    'status' => 'pending',
                    'cost' => $responseJson["messages"][0]["cost"],
                    'sent_at' => now()
                ]);

                if (config('arpoone.multi_tenant', false)) {
                    $sms->{config('arpoone.sms_log_tenant_column_name', 'tenant_id')} = $this->tenant_id;
                }
                $sms->save();

                return $sms;
            }

            if($type == 'email' && config('arpoone.log_email', false)){
                // Log the Email in the database
                $email = new ArpooneEmailsLog();
                $email->fill([
                    'message_id' => $responseJson["messages"][0]["messageId"],
                    'to' => $payload['messages'][0]['to'],
                    'html_content' => $payload['messages'][0]['htmlContent'],
                    'status' => 'pending',
                    'sent_at' => now()
                ]);

                if (config('arpoone.multi_tenant', false)) {
                    $email->{config('arpoone.tenant_column_name', 'tenant_id')} = $this->tenant_id;
                }
                $email->save();

                return $email;
            }



            // Return the response body
            return $responseJson;

        } catch (RequestException $e) {
            // Converte o conteúdo da resposta para um objeto PHP
            $responseBody = $e->getResponse()->getBody()->getContents();
            $decodedResponse = json_decode($responseBody);
            // Verifica se o JSON foi decodificado corretamente
            if (json_last_error() === JSON_ERROR_NONE && isset($decodedResponse->messages[0]->error->code)) {
                $errorCode = $decodedResponse->messages[0]->error->code;
                throw new \Exception('Failed to send '. $type .': ' . $errorCode);
            } else {
                // Caso algo esteja errado no JSON ou na estrutura esperada
                throw new \Exception('Failed to send ' . $type . ': Unexpected response format');
            }
        }
    }

    private function checkAttachmentSize(mixed $base64Content)
    {
        // Define the maximum allowed size in bytes (5 MB)
        $maxSize = 5 * 1024 * 1024; // 5,242,880 bytes

        // Calculate the length of the Base64 string
        $base64Length = strlen($base64Content);

        // Calculate the number of padding characters ('=')
        $paddingLength = substr_count(substr($base64Content, -2), '=');

        // Calculate the original size in bytes
        $originalSize = ($base64Length * 3 / 4) - $paddingLength;

        // Check if the original size exceeds the maximum allowed size
        if ($originalSize > $maxSize) {
            return false;
        }

        return true;
    }
}
