<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeviceAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_alerts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('location_device_id')->unsigned()->nullable();
            $table->integer('error_log_id')->unsigned()->nullable();
            $table->text('message');
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('location_device_id')->references('id')->on('location_devices')->onDelete('cascade');
            $table->foreign('error_log_id')->references('id')->on('error_logs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_alerts');
    }
}
