<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInGoingAndOutGoingDeviceId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('attendant_transactions')) {
        }
        Schema::table('attendant_transactions', function (Blueprint $table) {
            //
            $table->integer('in_going_device_id')->nullable();
            $table->integer('out_going_device_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendant_transactions', function (Blueprint $table) {
            //
            $table->dropColumn(['in_going_device_id', 'out_going_device_id']);
        });
    }
}
