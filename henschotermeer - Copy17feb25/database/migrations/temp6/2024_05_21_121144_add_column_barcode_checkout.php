<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnBarcodeCheckout extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendant_transactions', function (Blueprint $table) {
            //
            $table->boolean('auto_check_out')->default(0);
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
            $table->dropColumn(['auto_check_out']);
        });
    }
}
