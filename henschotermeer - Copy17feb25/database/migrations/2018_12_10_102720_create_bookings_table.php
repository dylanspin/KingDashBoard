<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('live_id')->default(0);
            $table->integer('customer_id')->unsigned()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->integer('customer_vehicle_info_id')->unsigned()->nullable();
            $table->foreign('customer_vehicle_info_id')->references('id')->on('customer_vehicle_infos')->onDelete('cascade');
            $table->timestamp('checkin_time')->nullable();
            $table->timestamp('checkout_time')->nullable();
            $table->tinyInteger('type')->default(0);
            $table->tinyInteger('is_user_logged_in')->default(0);
            $table->string('vehicle_num')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('sender_name')->nullable();
            $table->text('message')->nullable();
            $table->bigInteger('rating_id')->nullable();
            $table->tinyInteger('is_cancelled')->default(0);
            $table->tinyInteger('is_customer_left')->default(0);
            $table->tinyInteger('customer_left_status')->default(0);
            $table->tinyInteger('is_user_ballance_adjustment')->default(0);
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
        Schema::dropIfExists('bookings');
    }

}
