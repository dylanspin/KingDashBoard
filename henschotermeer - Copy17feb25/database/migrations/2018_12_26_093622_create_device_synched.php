<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeviceSynched extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('device_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('device_id')->unsigned()->nullable();
            $table->foreign('device_id')->references('id')->on('location_devices')->onDelete('cascade');
            $table->integer('location_settings')->default(0);
            $table->integer('location_timings_settings')->default(0);
            $table->integer('location_whitelist_timings_settings')->default(0);
            $table->integer('whitelist_settings')->default(0);
            $table->integer('userlist_settings')->default(0);
            $table->integer('device_settings')->default(0);
            $table->string('location_settings_details')->nullable();
            $table->string('location_timings_settings_details')->nullable();
            $table->string('location_whitelist_timings_settings_details')->nullable();
            $table->string('whitelist_settings_details')->nullable();
            $table->string('userlist_settings_details')->nullable();
            $table->string('device_settings_details')->nullable();
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
        Schema::dropIfExists('device_settings');
    }

}
