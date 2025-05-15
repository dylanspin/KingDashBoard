<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserlistUserIdToCustomerVehicleInfos extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('customer_vehicle_infos', function (Blueprint $table) {
            $table->integer('userlist_user_id')->nullable()->unsigned()->index();
            $table->foreign('userlist_user_id')->references('id')->on('userlist_users')->onDelete('cascade');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('customer_vehicle_infos', function (Blueprint $table) {
            $table->dropForeign(['userlist_user_id']);
            $table->dropColumn(['userlist_user_id']);
        });
    }

}
