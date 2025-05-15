<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationExtraSpotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_extra_spots', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date')->nullable();
            $table->integer('avaialable_spots')->nullable();
            $table->integer('person_avaialable_spots')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('location_extra_spots');
    }
}
