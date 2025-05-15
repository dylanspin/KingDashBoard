<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParkingAccessRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parking_access_rules', function (Blueprint $table) {
            $table->increments('id');
             $table->integer('rule_id')->unsigned()->nullable();
            $table->boolean('enable')->default(0);
            $table->integer('match_distance')->nullable();
            $table->string('device_direction')->nullable();
            $table->string('barcode_type')->nullable();
            $table->string('plate_match_mode')->nullable();
            $table->foreign('rule_id')->references('id')->on('parking_rules_names')->onDelete('cascade'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parking_access_rules');
    }
}
