<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArpooneSmsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Verifique se log_sms está habilitado
        if (config('arpoone.log_sms', false)) {
            $logTableName = config('arpoone.sms_log_table_name', 'arpoone_sms_logs');
            $useTenantColumn = config('arpoone.use_tenant_column', false);
            $tenantColumnName = config('arpoone.sms_log_tenant_column_name', 'tenant_id');

            Schema::create($logTableName, function (Blueprint $table) use ($useTenantColumn, $tenantColumnName) {
                $table->id();

                // ID da mensagem
                $table->longText('message_id');

                // Número do destinatário
                $table->string('recipient_number');

                // Conteúdo do SMS
                $table->text('message');

                // Status do envio
                $table->string('status')->nullable();

                // Data e hora do envio
                $table->timestamp('sent_at')->nullable();

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
        // Verifique se log_sms está habilitado antes de remover a tabela
        if (config('arpoone.log_sms', false)) {
            $logTableName = config('arpoone.sms_log_table', 'arpoone_sms_logs');
            Schema::dropIfExists($logTableName);
        }
    }
}
