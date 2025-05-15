<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationDevicesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('location_devices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('live_id')->default(0);
            $table->string('device_name')->nullable();
            $table->integer('available_device_id')->unsigned()->nullable();
            $table->foreign('available_device_id')->references('id')->on('available_devices')->onDelete('cascade');
            $table->string('device_direction')->nullable();
            $table->string('device_ip')->nullable();
            $table->string('device_port')->nullable();
            $table->string('anti_passback')->nullable();
            $table->string('time_passback')->nullable();
            $table->integer('is_synched')->default(0);
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
        Schema::dropIfExists('location_devices');
    }

}
