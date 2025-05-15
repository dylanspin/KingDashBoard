<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTommyReservationChildrensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tommy_reservation_childrens', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('live_id')->default(0);
            $table->integer('tommy_reservation_parent_id')->unsigned()->nullable();
            $table->foreign('tommy_reservation_parent_id')->references('id')->on('tommy_reservation_parents')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('family_status')->nullable();
            $table->date('dob')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
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
        Schema::dropIfExists('tommy_reservation_childrens');
    }
}
