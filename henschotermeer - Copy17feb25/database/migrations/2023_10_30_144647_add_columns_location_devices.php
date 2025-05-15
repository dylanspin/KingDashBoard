<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsLocationDevices extends Migration
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
                $table->integer('plate_length')->nullable();
                $table->integer('character_height')->nullable();
            $table->string('exposure_mode')->nullable();
                $table->boolean('disable_night_mode')->default(0);
            $table->boolean('light_condition')->default(0);
            $table->boolean('emergency_entry_exit')->default(0);
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
            $table->dropColumn(['plate_length', 'character_height', 'triple_exposure', 'disable_night_mode', 'light_condition', 'emergency_entry_exit']);
        });
    }
}
