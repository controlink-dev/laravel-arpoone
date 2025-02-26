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
                    $table->string('email_sender_name')->nullable()->after('sms_sender');
                    $table->string('email_sender_email')->nullable()->after('email_sender_name');
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
                    $table->dropColumn('email_sender_name');
                    $table->dropColumn('email_sender_email');
                });
            }
        }
    }
};
