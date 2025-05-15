<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeviceAlertUpdate extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('device_alerts', function (Blueprint $table) {
            $table->dropForeign('device_alerts_error_log_id_foreign');
            $table->dropColumn('error_log_id');
            $table->string('device_error_id')->nullable();
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
