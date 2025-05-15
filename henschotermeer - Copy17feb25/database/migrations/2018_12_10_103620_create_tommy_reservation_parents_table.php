<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTommyReservationParentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tommy_reservation_parents', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('live_id')->default(0);
            $table->integer('total_members')->default(0);
            $table->string('email')->nullable();
            $table->timestamp('date_of_arrival')->nullable();
            $table->timestamp('date_of_departure')->nullable();
            $table->string('license_plate')->nullable();
            $table->string('other_license_plate')->nullable();
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
        Schema::dropIfExists('tommy_reservation_parents');
    }
}
