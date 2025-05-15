<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TransactionTables extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('transaction_payment_vehicles', function (Blueprint $table) {
            $table->increments('id');
            $table->Integer('device_id')->nullable();
            $table->Integer('booking_id')->nullable();
            $table->Integer('status')->nullable();
            $table->Integer('amount')->nullable();
            $table->string('transaction')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('transaction_payment_persons', function (Blueprint $table) {
            $table->increments('id');
            $table->Integer('device_id')->nullable();
            $table->Integer('quantity')->nullable();
            $table->Integer('status')->nullable();
            $table->Integer('amount')->nullable();
            $table->string('transaction')->nullable();
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
        //
    }

}
