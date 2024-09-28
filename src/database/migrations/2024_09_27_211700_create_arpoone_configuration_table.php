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
            $tableName = config('arpoone.table_name', 'arpoone_configuration');
            $useTenantColumn = config('arpoone.use_tenant_column', false);
            $tenantColumnName = config('arpoone.tenant_column_name', 'tenant_id');

            Schema::create($tableName, function (Blueprint $table) use ($useTenantColumn, $tenantColumnName) {
                $table->id();

                // Armazena a URL da API
                $table->string('url')->default('https://api.arpoone.com/v1.1/');

                // Chave API da Arpoone
                $table->string('api_key')->nullable();

                // ID da organização Arpoone
                $table->string('organization_id')->nullable();

                // Nome ou número do remetente
                $table->string('sender')->nullable();

                // Verificar SSL
                $table->boolean('verify_ssl')->default(true);

                // Adiciona coluna de tenant, se aplicável
                if ($useTenantColumn) {
                    $table->string($tenantColumnName)->nullable();
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