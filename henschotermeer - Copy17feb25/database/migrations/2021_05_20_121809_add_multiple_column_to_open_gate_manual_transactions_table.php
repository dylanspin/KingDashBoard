<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMultipleColumnToOpenGateManualTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('open_gate_manual_transactions', function (Blueprint $table) {
            $table->string('type')->nullable()->default('MO');
            $table->integer('location_device_id')->unsigned()->nullable();
            $table->foreign('location_device_id')->references('id')->on('location_devices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('open_gate_manual_transactions', function (Blueprint $table) {
            $table->dropForeign('open_gate_manual_transactions_location_device_id_foreign');
            $table->dropColumn([
                'type',
                'location_device_id'
            ]);
        });
    }
}
