<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTefTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tef_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id')->unsigned()->nullable();
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->integer('cash_register_transaction_id')->unsigned()->nullable();
            $table->foreign('cash_register_transaction_id')->references('id')->on('cash_register_transactions')->onDelete('cascade');
            
            // Dados TEF
            $table->string('tef_status')->nullable();
            $table->string('tef_nsu')->nullable();
            $table->string('tef_codigo_autorizacao')->nullable();
            $table->string('tef_adquirente')->nullable();
            $table->string('tef_comando')->nullable();
            $table->string('tef_id_req')->nullable();
            $table->decimal('tef_valor', 22, 4)->nullable();
            $table->integer('tef_parcelas')->nullable();
            $table->string('tef_tipo_transacao')->nullable();
            $table->timestamp('tef_data_hora')->nullable();
            $table->boolean('tef_processado')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tef_transactions');
    }
}