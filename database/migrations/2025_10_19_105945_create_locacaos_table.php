<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocacaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locacaos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->date('data_abertura');
            $table->decimal('valor', 22, 4);
            $table->string('status')->default('aberta');
            $table->integer('dias_em_locacao')->default(0);
            $table->decimal('valor_total', 22, 4)->nullable();
            $table->integer('dias_excedentes')->default(0);
            $table->integer('dias_total')->default(0);
            $table->unsignedBigInteger('produto_id')->nullable();
            $table->timestamps();

            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('produto_id')->references('id')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locacaos');
    }
}
