<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOnlineParkingCloseEnableIntoLocationOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('location_options', function (Blueprint $table) {
            //
            $table->boolean('online_parking_closed_enable')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('location_options', function (Blueprint $table) {
            //
            $table->dropColumn(['online_parking_closed_enable']);
        });
    }
}
