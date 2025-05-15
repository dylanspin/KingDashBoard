<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BookingTemporaryTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('confidence')->nullable();
        });
        Schema::create('device_bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->Integer('device_id')->nullable();
            $table->Integer('confidence')->nullable();
            $table->Integer('file_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
