<?php

namespace Controlink\LaravelArpoone\Http\Controllers;

use Controlink\LaravelArpoone\Models\ArpooneConfiguration;
use Controlink\LaravelArpoone\Models\ArpooneSmsLog;
use Controlink\LaravelArpoone\Models\ArpooneWebhookLog;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ArpooneSmsLogController extends Controller
{
    use ValidatesRequests;

    public function updateStatus($status, Request $request)
    {
        // Handle the webhook request
        // You can use the $status variable to determine the status of the SMS message
        // For example, if $status is 'delivered', update the status of the SMS message in your database
        // You can also log the webhook request for auditing purposes
        // The status can be 'delivered', 'not_delivered', or 'pending'
        // "delivered" and "not_delivered" are final statuses
        // "pending" is an intermediate status
        // The payload of the webhook request will contain this:
        // {
        //     "msisdn": "string",
        //     "status": "string",
        //     "messageId": "uuid",
        //     "organizationId": "uuid",
        // }

        // Update the status of the SMS message in the database
        // For example, you can find the SMS message by the messageId and update its status
        // However, you should validate the webhook request to ensure that the organizationId exists and is valid
        // You can also log the webhook request for auditing purposes

        // Log the webhook request
        // You can log the request body, headers, and other relevant information
        $request->validate([
            'msisdn' => 'required|string',
            'status' => 'required|string',
            'messageId' => 'required|uuid',
            'organizationId' => 'required|uuid',
        ]);

        //Check if multi_tenant is enabled
        if(config('arpoone.multi_tenant', false)) {
            $configuration = ArpooneConfiguration::where('organization_id', $request->organizationId)->firstOrFail();
            if ($configuration) {
                $useTenantColumn = config('arpoone.use_tenant_column', false);
                $tenantColumnName = config('arpoone.tenant_column_name', 'tenant_id');
                $tenantId = $useTenantColumn ? $configuration->$tenantColumnName : null;
            }

            if ($useTenantColumn && !$tenantId) {
                throw new \Exception('Tenant ID is required for multi-tenant applications.');
            }

            $webhookLog = new ArpooneWebhookLog();
            $webhookLog->headers = json_encode($request->header());
            $webhookLog->payload = json_encode($request->all());
            $webhookLog->ip_address = $request->ip();
            if ($useTenantColumn) {
                $webhookLog->$tenantColumnName = $tenantId;
            }
            $webhookLog->save();
        }else{
            $webhookLog = new ArpooneWebhookLog();
            $webhookLog->headers = json_encode($request->header());
            $webhookLog->payload = json_encode($request->all());
            $webhookLog->ip_address = $request->ip();
            $webhookLog->save();
        }

        $sms = ArpooneSmsLog::where('message_id', $request->messageId)->firstOrFail();
        $sms->status = $request->status;
        $sms->save();

        return response()->json(['message' => 'Webhook processed successfully.'], 200);
    }
}