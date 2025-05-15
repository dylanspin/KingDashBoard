<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_options', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('live_id')->default(0);
            $table->text('address')->nullable();
            $table->integer('avaialable_spots')->nullable();
            $table->tinyInteger('is_covered')->default(0);
            $table->tinyInteger('is_gated')->default(0);
            $table->text('other_specs')->nullable();
            $table->longText('description')->nullable();
            $table->string('total_spots')->nullable();
            $table->string('title')->nullable();
            $table->string('city_country')->nullable();
            $table->tinyInteger('is_external_link')->default(0);
            $table->string('external_link')->nullable();
            $table->string('postal_code')->nullable();
            $table->longText('extra_features')->nullable();
            $table->tinyInteger('is_approved')->default(0);
            $table->tinyInteger('is_completed')->default(0);
            $table->tinyInteger('is_active')->default(0);
            $table->text('disapproved_message')->nullable();
            $table->string('height_restriction_value')->default(0);
            $table->string('access_point')->nullable();
            $table->tinyInteger('ev_charger')->default(0);
            $table->string('owner_phone_num')->nullable();
            $table->string('location_type')->nullable();
            $table->string('owner_operator_name')->nullable();
            $table->bigInteger('max_stay')->default(0);
            $table->bigInteger('advance_booking_limit')->default(0);
            $table->string('barcode_series')->nullable();
            $table->tinyInteger('is_whitelist')->default(0);
            $table->mediumInteger('bike_range_start')->default(0);
            $table->mediumInteger('bike_range_end')->default(0);
            $table->mediumInteger('door_range_start')->default(0);
            $table->mediumInteger('door_range_end')->default(0);
            $table->mediumInteger('ev_charger_range_start')->default(0);
            $table->mediumInteger('ev_charger_range_end')->default(0);
            $table->bigInteger('ev_charger_energy')->default(0);
            $table->mediumInteger('language_id')->default(0);
            $table->tinyInteger('is_doors')->default(0);
            $table->tinyInteger('is_bikes')->default(0);
            $table->tinyInteger('is_parkingshop_location')->default(1);
            $table->integer('online_booking_spots')->nullable();
            $table->tinyInteger('is_send_reservation_email')->default(0);
            $table->string('reservation_email')->nullable();
            $table->float('price_per_hour', 8, 2)->nullable();
            $table->float('price_per_day', 8, 2)->nullable();
            $table->integer('star_rank')->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
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
        Schema::dropIfExists('location_options');
    }
}
