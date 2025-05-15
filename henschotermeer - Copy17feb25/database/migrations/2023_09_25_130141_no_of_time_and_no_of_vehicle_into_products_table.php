<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NoOfTimeAndNoOfVehicleIntoProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('products')) {

        }
        Schema::table('products', function (Blueprint $table) {
            //
            $table->string('pos_type')->nullable();
            $table->boolean('is_active')->nullable();
            $table->integer('valid_until')->nullable();
            $table->integer('no_of_time')->nullable();
            $table->integer('no_of_vehicle')->nullable();
            $table->longText('how_to_use_en')->nullable();
            $table->longText('how_to_use_nl')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            //
            $table->dropColumn(['pos_type','is_active','valid_until','no_of_time', 'no_of_vehicle', 'how_to_use_en', 'how_to_use_nl']);
        });
    }
}
