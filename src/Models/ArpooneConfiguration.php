<?php

namespace Controlink\LaravelArpoone\Models;

use Illuminate\Database\Eloquent\Model;

class ArpooneConfiguration extends Model
{
    protected $table;
    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        // Set the table name dynamically from the config
        $this->table = config('arpoone.table_name', 'arpoone_configuration');

        $this->fillable = [
            'url',
            'api_key',
            'organization_id',
            'sms_sender',
            'email_sender',
            'email_sender_name',
            'verify_ssl',
            config('arpoone.sms_log_tenant_column_name', 'tenant_id')
        ];

        // Call the parent constructor
        parent::__construct($attributes);
    }

    protected $casts = [
        'verify_ssl' => 'boolean'
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
