<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserlistUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('userlist_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('live_id')->default(0);
            $table->integer('customer_id')->unsigned()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->integer('group_id')->unsigned()->nullable();
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->string('email')->nullable();
            $table->longText('notation')->nullable();
            $table->mediumInteger('bike_range_start')->nullable();
            $table->mediumInteger('bike_range_end')->nullable();
            $table->mediumInteger('door_range_start')->nullable();
            $table->mediumInteger('door_range_end')->nullable();
            $table->mediumInteger('ev_charger_range_start')->nullable();
            $table->mediumInteger('ev_charger_range_end')->nullable();
            $table->mediumInteger('language_id')->default(0)->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_phone')->nullable();
            $table->string('user_vehicle')->nullable();
            $table->tinyInteger('is_blocked')->default(0);
            $table->string('profile_image')->nullable();
            $table->string('energy_limit')->nullable();
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
        Schema::dropIfExists('userlist_users');
    }

}
