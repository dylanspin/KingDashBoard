<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnEnableMultipleAndValidTill extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('barcodes')) {
        }
        Schema::table('barcodes', function (Blueprint $table) {
            //
            $table->string('valid_till')->nullable();
            $table->boolean('use_barcode_multiple_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('barcodes', function (Blueprint $table) {
            //
            $table->dropColumn(['valid_till', 'use_barcode_multiple_time']);
        });
    }
}
