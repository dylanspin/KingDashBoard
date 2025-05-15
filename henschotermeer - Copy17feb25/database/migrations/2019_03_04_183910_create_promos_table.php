<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePromosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('promo_type_id')->unsigned()->nullable();
            $table->string('code')->nullable();
            $table->string('price')->nullable();
            $table->string('percentage')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->integer('promo_number_limit')->nullable();
            $table->integer('promo_used')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('promo_type_id')->references('id')->on('promo_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promos');
    }
}
