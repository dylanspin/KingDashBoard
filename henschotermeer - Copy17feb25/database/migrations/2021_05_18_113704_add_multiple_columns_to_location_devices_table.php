<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMultipleColumnsToLocationDevicesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('location_devices', function (Blueprint $table) {
            $table->integer('barrier_status')->default(0);
            $table->tinyInteger('has_always_access')->default(0);
            $table->integer('user_id')->unsigned()->nullable()->after('live_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->tinyInteger('has_enable_person_ticket')->default(0);
            $table->tinyInteger('has_enable_parking_ticket')->default(0);
            $table->string('advert_image_path')->nullable();
			$table->string('idle_screen_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('location_devices', function (Blueprint $table) {
            $table->dropForeign('location_devices_user_id_foreign');
            $table->dropColumn([
                'has_enable_person_ticket',
                'has_enable_parking_ticket',
                'advert_image_path',
                'user_id',
                'barrier_status',
                'has_always_access',
				'idle_screen_image'
            ]);
        });
    }

}
