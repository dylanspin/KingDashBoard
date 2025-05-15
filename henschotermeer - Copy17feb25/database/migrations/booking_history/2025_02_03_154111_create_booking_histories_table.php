<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('live_id')->default(0);
            $table->unsignedInteger('customer_id')->nullable();
            $table->unsignedInteger('customer_vehicle_info_id')->nullable();
            $table->timestamp('checkin_time')->nullable();
            $table->timestamp('checkout_time')->nullable();
            $table->tinyInteger('type')->default(0);
            $table->tinyInteger('is_user_logged_in')->default(0);
            $table->string('vehicle_num', 191)->nullable();
            $table->string('phone_number', 191)->nullable();
            $table->string('first_name', 191)->nullable();
            $table->string('last_name', 191)->nullable();
            $table->string('email', 191)->nullable();
            $table->string('sender_name', 191)->nullable();
            $table->text('message')->nullable();
            $table->bigInteger('rating_id')->nullable();
            $table->tinyInteger('is_cancelled')->default(0);
            $table->tinyInteger('is_customer_left')->default(0);
            $table->tinyInteger('customer_left_status')->default(0);
            $table->tinyInteger('is_user_ballance_adjustment')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->string('image_path', 191)->nullable();
            $table->string('ticket_type', 191)->nullable();
            $table->string('barcode', 191)->nullable();
            $table->integer('tommy_parent_id')->nullable();
            $table->integer('tommy_children_id')->nullable();
            $table->string('tommy_children_dob', 191)->nullable();
            $table->tinyInteger('is_paid')->default(0);
            $table->string('confidence', 191)->nullable();
            $table->string('country_code', 191)->nullable();
            $table->tinyInteger('low_confidence')->default(0);
            $table->string('promo_code', 191)->nullable();
            $table->tinyInteger('is_local_updated')->default(1);
            $table->tinyInteger('is_live_updated')->default(0);
            $table->tinyInteger('user_arrival_notification')->nullable()->default(0);
            $table->tinyInteger('is_tommy_online')->default(0);
            $table->integer('group_invitation_id')->nullable();
            $table->integer('ref_booking_id')->nullable();
            $table->string('pos_barcode', 191)->nullable();
            $table->string('pos_type', 191)->nullable();
            $table->integer('product_id')->nullable();
            $table->boolean('is_blocked')->default(0);
            $table->string('booking_type', 50)->default('normal_booking'); // Changed from boolean to string
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings_history');
    }
}
