<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OpenGateTransaction extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('open_gate_manual_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_payment_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->text('reason');
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
