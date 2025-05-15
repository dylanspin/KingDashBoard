<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeviceAuthorizationTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_authorization_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('device_id')->nullable();
            $table->string('identifire');
            $table->string('token')->nullable();
            $table->boolean('allow_transaction')->default(1);
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
        Schema::dropIfExists('device_authorization_tokens');
    }
}