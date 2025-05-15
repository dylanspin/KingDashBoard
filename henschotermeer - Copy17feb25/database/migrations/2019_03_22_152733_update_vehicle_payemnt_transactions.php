<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateVehiclePayemntTransactions extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('open_gate_manual_transactions', function (Blueprint $table) {
            $table->integer('transaction_images_id')->nullable();
            $table->integer('attendant_transaction_id')->nullable();
        });
        Schema::table('transaction_payment_vehicles', function (Blueprint $table) {
            $table->integer('attendant_transaction_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }

}
