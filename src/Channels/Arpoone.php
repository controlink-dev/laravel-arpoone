<?php

namespace Controlink\LaravelArpoone\Channels;

use Controlink\LaravelArpoone\Models\ArpooneConfiguration;
use Controlink\LaravelArpoone\Models\ArpooneSmsLog;
use Illuminate\Notifications\Notification;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
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

    public function send(object $notifiable, Notification $notification)
    {
        // Check if the $notification has the toArpoone method
        if (!method_exists($notification, 'toArpoone')) {
            throw new \Exception('The notification must have a toArpoone method.');
        }

        // Retrieve message data from notification
        $message = $notification->toArpoone($notifiable);

        // Get the phone number from the notifiable using routeNotificationForArpoone
        $recipientPhoneNumber = $notifiable->routeNotificationForArpoone() ?? $notifiable->phone_number;

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

        // Retrieve configuration settings
        $configuration = null;
        if(config('arpoone.multi_tenant', false)){
            if(!$this->tenant_id){
                throw new \Exception('Tenant ID is required for multi-tenant applications.');
            }

            $configuration = ArpooneConfiguration::where(config('arpoone.tenant_column_name','tenant_id'), $this->tenant_id)->first();
        }

        $url = $configuration ? $configuration->url : config('arpoone.url');
        $apiKey = $configuration ? $configuration->api_key : config('arpoone.api_key');
        $organizationId = $configuration ? $configuration->organization_id : config('arpoone.organization_id');
        $sender = $configuration ? $configuration->sender : config('arpoone.sender');
        $verifySsl = config('arpoone.verify_ssl', true); // Default is true

        // Ensure required config values are set
        if (empty($apiKey) || empty($organizationId) || empty($sender)) {
            throw new \Exception('Arpoone configuration values are missing. Ensure API key, organization ID, and sender are set, check config/arpoone.php file for more information.');
        }

        // Prepare the message payload
        $message = [
            'text' => $message['content'],
            'to' => $recipientPhoneNumber,
            'from' => $sender,
        ];

        //Check if webhooks are enabled
        if(config('arpoone.webhooks', false)){
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

        // Prepare request headers
        $headers = [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $body = [
                'organizationId' => $organizationId,
                'messages' => [$message],
        ];


        try {
            // Send the request to Arpoone API
            $response = $this->client->post($url . 'sms/send', [
                'headers' => $headers,
                'json' => $body,
                'verify' => $verifySsl,
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);

            if(config('arpoone.log_sms', false)){
                // Log the SMS in the database
                $sms = new ArpooneSmsLog();
                $sms->fill([
                    'message_id' => $responseBody["messages"][0]["messageId"],
                    'recipient_number' => $message['to'],
                    'message' => $message['text'],
                    'status' => 'pending',
                    'sent_at' => now()
                ]);

                if(config('arpoone.multi_tenant', false)) {
                    $sms->{config('arpoone.sms_log_tenant_column_name', 'tenant_id')} = $this->tenant_id;
                }
                $sms->save();

                return $sms;
            }

            // Return the response body
            return json_decode($response->getBody()->getContents(), true);

        }catch (RequestException $e) {
            // Converte o conteúdo da resposta para um objeto PHP
            $responseBody = $e->getResponse()->getBody()->getContents();
            $decodedResponse = json_decode($responseBody);
            // Verifica se o JSON foi decodificado corretamente
            if (json_last_error() === JSON_ERROR_NONE && isset($decodedResponse->messages[0]->error->code)) {
                $errorCode = $decodedResponse->messages[0]->error->code;
                throw new \Exception('Failed to send SMS: ' . $errorCode);
            } else {
                // Caso algo esteja errado no JSON ou na estrutura esperada
                throw new \Exception('Failed to send SMS: Unexpected response format');
            }
        }
    }

    /**
     * Valida o número de telemóvel para garantir que é um número móvel válido e contém o código de país sem o "+".
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
            return preg_replace('/[^0-9]/', '', $phoneUtil->format($parsedPhoneNumber, PhoneNumberFormat::E164));
        } catch (NumberParseException $e) {
            throw new \Exception('Failed to parse phone number: ' . $e->getMessage());
        }
    }
}
