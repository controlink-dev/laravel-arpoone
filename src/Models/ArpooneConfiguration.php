<?php

namespace Controlink\LaravelArpoone\Models;

use Illuminate\Database\Eloquent\Model;

class ArpooneConfiguration extends Model
{
    protected $table = 'arpoone_configuration';

    protected $fillable = [
        'url',
        'api_key',
        'organization_id',
        'sender',
        'verify_ssl',
        'tenant_id'  // Se a tabela for multi-tenant
    ];

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
