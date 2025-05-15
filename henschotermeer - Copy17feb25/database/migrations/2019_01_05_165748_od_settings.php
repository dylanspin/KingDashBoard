<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OdSettings extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('location_devices', function (Blueprint $table) {
            $table->string('message_text_size')->nullable();
            $table->string('time_text_size')->nullable();
            $table->string('date_text_size')->nullable();
            $table->string('bottom_tray_text_size')->nullable();
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
