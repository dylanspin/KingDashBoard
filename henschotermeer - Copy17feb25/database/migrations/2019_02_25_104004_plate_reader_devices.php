<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PlateReaderDevices extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('location_devices', function (Blueprint $table) {
            $table->string('confidence_level')->nullable();
            $table->string('num_tries')->nullable();
            $table->string('confidence_level_lowest')->default('10');
            $table->string('character_match_limit')->default('4');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }

}
