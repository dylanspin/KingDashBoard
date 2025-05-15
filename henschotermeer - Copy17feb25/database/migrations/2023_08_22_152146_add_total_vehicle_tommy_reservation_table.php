<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTotalVehicleTommyReservationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tommy_reservation_parents', function (Blueprint $table) {
            //
            $table->integer('total_vehicle')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tommy_reservation_parents', function (Blueprint $table) {
            //
            $table->dropColumn(['total_vehicle']);
        });
    }
}
