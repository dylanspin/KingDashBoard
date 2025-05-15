<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsIsImportedForOtherLocationDevices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('location_devices', function (Blueprint $table) {
            //
            $table->boolean('is_imported')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('location_devices', function (Blueprint $table) {
            //
            $table->dropColumn(['is_imported']);
        });
    }
}
