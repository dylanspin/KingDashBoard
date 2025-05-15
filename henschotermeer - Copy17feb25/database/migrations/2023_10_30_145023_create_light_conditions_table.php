<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLightConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('light_conditions')) {
        Schema::create('light_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_id')->nullable();
                $table->string('level');
            $table->foreign('device_id')
                ->references('id')
                ->on('location_devices')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->integer('gain')->nullable();
            $table->integer('exposure_time')->nullable();
            $table->timestamps();
        });}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(Blueprint $table)
    {
        $table->dropColumn(['exposure_time', 'gain']);
    }
}
