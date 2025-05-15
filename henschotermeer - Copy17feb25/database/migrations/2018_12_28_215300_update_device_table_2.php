<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDeviceTable2 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['barrier_close_time', 'qr_code_type', 'enable_log',
                'enable_idle_screen', 'focus_away', 'opacity_input']);
        });
        Schema::table('location_devices', function (Blueprint $table) {
            $table->string('barrier_close_time')->nullable();
            $table->string('qr_code_type')->nullable();
            $table->tinyInteger('enable_log')->default(0);
            $table->tinyInteger('enable_idle_screen')->default(0);
            $table->tinyInteger('focus_away')->default(0);
            $table->tinyInteger('opacity_input')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }

}
