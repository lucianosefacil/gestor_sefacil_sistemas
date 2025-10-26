<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTefFieldsToTransactionPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_payments', function (Blueprint $table) {
            $table->string('tef_nsu')->nullable()->after('account_id');
            $table->string('tef_codigo_autorizacao')->nullable()->after('tef_nsu');
            $table->string('tef_adquirente')->nullable()->after('tef_codigo_autorizacao');
            $table->string('tef_bandeira')->nullable()->after('tef_adquirente');
            $table->string('tef_tipo_transacao')->nullable()->after('tef_bandeira');
            $table->string('tef_controle')->nullable()->after('tef_tipo_transacao');
            $table->timestamp('tef_data_hora')->nullable()->after('tef_controle');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_payments', function (Blueprint $table) {
            $table->dropColumn([
                'tef_nsu',
                'tef_codigo_autorizacao', 
                'tef_adquirente',
                'tef_bandeira',
                'tef_tipo_transacao',
                'tef_controle',
                'tef_data_hora'
            ]);
        });
    }
}
