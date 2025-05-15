<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsOperatorAndReasonToDeviceBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('device_bookings', function (Blueprint $table) {
            $table->tinyInteger('is_operator')->default(0);
            $table->string('reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('device_bookings', function (Blueprint $table) {
            $table->dropColumn(['is_operator', 'reason']);
        });
    }
}
