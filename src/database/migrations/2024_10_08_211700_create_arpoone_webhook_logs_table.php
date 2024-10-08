<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArpooneConfigurationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Verifique se multi_tenant está habilitado
        if (config('arpoone.multi_tenant', false)) {
            $tableName = config('arpoone.webhook_table_name', 'arpoone_webhook_logs');
            $useTenantColumn = config('arpoone.use_tenant_column', false);
            $tenantColumnName = config('arpoone.tenant_column_name', 'tenant_id');

            Schema::create($tableName, function (Blueprint $table) use ($useTenantColumn, $tenantColumnName) {
                $table->id();

                // Adiciona colunas para armazenar informações do webhook
                $table->longText('headers')->nullable();
                $table->longText('payload')->nullable();
                $table->longText('ip_address')->nullable();

                // Adiciona coluna de tenant, se aplicável
                if ($useTenantColumn) {
                    if(!config('arpoone.tenant_model')){
                        throw new \Exception('The tenant model is not set in the Arpoone configuration.');
                    }

                    $table->foreignIdFor(config('arpoone.tenant_model'))->constrained()->cascadeOnDelete();
                }

                // Timestamps para controle de criação e atualização
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Verifique se multi_tenant está habilitado antes de remover a tabela
        if (config('arpoone.multi_tenant', false)) {
            $tableName = config('arpoone.table_name', 'arpoone_configuration');
            Schema::dropIfExists($tableName);
        }
    }
}