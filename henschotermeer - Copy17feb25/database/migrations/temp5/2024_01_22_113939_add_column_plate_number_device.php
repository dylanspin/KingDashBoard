<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPlateNumberDevice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('device_download_logs')) {
            Schema::table('device_logs', function (Blueprint $table) {
                $table->string('vehicle_number')->after('device_booking_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('device_logs', function (Blueprint $table) {
            //
            $table->dropColumn(['vehicle_number']);
        });
    }
}
