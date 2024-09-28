<?php

namespace Controlink\LaravelArpoone\Models;

use Illuminate\Database\Eloquent\Model;

class ArpooneSmsLog extends Model
{
    protected $table = 'arpoone_sms_logs';

    protected $fillable = [
        'recipient_number',
        'message',
        'status',
        'sent_at',
        'tenant_id'
    ];

    public function tenant()
    {
        if (config('arpoone.use_tenant_column', false)) {
            $tenantModel = config('arpoone.tenant_model', null);
            $tenantColumn = config('arpoone.tenant_column_name', 'tenant_id');

            return $tenantModel ? $this->belongsTo($tenantModel, $tenantColumn) : null;
        }

        throw new \Exception('Multi-tenant mode is not enabled in the Arpoone configuration.');
    }
}