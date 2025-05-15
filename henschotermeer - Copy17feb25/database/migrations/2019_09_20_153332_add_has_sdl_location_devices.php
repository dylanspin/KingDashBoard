<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHasSdlLocationDevices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('location_devices', function (Blueprint $table) {
            $table->tinyInteger('has_sdl')->default(0);
            $table->tinyInteger('gate_close_transaction_enabled')->default(0);
            $table->tinyInteger('has_pdl')->default(0);
            $table->tinyInteger('plate_correction_enabled')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
