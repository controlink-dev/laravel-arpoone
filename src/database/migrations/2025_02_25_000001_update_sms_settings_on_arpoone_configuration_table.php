<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
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

            if(Schema::hasTable($tableName)){
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('sender', 'sms_sender');
                });
            }
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

            if(Schema::hasTable($tableName)){
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('sms_sender', 'sender');
                });
            }
        }
    }
};
