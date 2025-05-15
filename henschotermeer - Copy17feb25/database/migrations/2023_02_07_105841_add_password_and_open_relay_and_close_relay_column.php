<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPasswordAndOpenRelayAndCloseRelayColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('location_devices', function (Blueprint $table) {
            //
            $table->integer('password')->nullable();
            $table->integer('open_relay')->nullable();
            $table->integer('close_relay')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('location_devices', function (Blueprint $table) {
            //
            $table->dropColumn(['password', 'open_relay','close_relay']);
        });
    }
}
