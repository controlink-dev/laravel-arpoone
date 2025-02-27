<?php

namespace Controlink\LaravelArpoone\Models;

use Illuminate\Database\Eloquent\Model;

class ArpooneSmsLog extends Model
{
    protected $table;
    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        // Set the table name dynamically from the config
        $this->table = config('arpoone.sms_log_table_name', 'arpoone_sms_logs');

        $this->fillable = [
            'message_id',
            'recipient_number',
            'message',
            'status',
            'sent_at',
            'cost',
            config('arpoone.tenant_column_name', 'tenant_id')
        ];

        // Call the parent constructor
        parent::__construct($attributes);
    }

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