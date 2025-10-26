<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToRequestCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_certificates', function (Blueprint $table) {
            $table->unsignedInteger('business_id')->after('id');
            $table->string('status')->nullable()->after('business_id');
            $table->text('payload')->nullable()->after('status');
            $table->text('response')->nullable()->after('payload');
            $table->string('username')->nullable()->after('response');
            $table->string('password')->nullable()->after('username');
            $table->string('anexo')->nullable()->after('password');
            
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_certificates', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropColumn(['business_id', 'status', 'payload', 'response', 'username', 'password', 'anexo']);
        });
    }
}
