<?php

namespace Controlink\LaravelArpoone\Http\Controllers;

use Controlink\LaravelArpoone\Models\ArpooneEmailsLog;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ArpooneEmailLogController extends Controller
{
    use ValidatesRequests;

    public function updateStatus($status, Request $request)
    {
        // Handle the webhook request
        // You can use the $status variable to determine the status of the SMS message
        // For example, if $status is 'bounced', update the status of the SMS message in your database
        // You can also log the webhook request for auditing purposes
        // The status can be 'bounced', 'clicked', 'spam' or 'unsubscribed'
        // "pending" is an intermediate status
        // The payload of the webhook request will contain this:
        // {
        //     "EventType": "string",
        //     "Email": "string",
        //     "MessageId": "uuid",
        //     "Time": "long",
        //     "EventDetails": "object",
        // }

        // Update the status of the email message in the database
        // For example, you can find the emails message by the MessageId and update its status
        // However, you should validate the webhook request to ensure that the organizationId exists and is valid
        // You can also log the webhook request for auditing purposes

        // Log the webhook request
        // You can log the request body, headers, and other relevant information
        $request->validate([
            '*.EventType' => 'required|string',
            '*.Status' => 'required|string',
            '*.MessageId' => 'required|uuid',
            '*.EventDetails' => 'required|array',
        ]);

        // Loop through each webhook event in the array
        foreach ($request->all() as $event) {
            // Update SMS status
            $sms = ArpooneEmailsLog::where('message_id', $event['MessageId'])->firstOrFail();
            $sms->status = $status;
            $sms->save();
        }

        return response()->json(['message' => 'Webhook processed successfully.'],200);
    }
}