<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReplyToReplyEmailColoumnInLocationOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('location_options', function (Blueprint $table) {
            //
            $table->string('reply_to_name')->nullable();
            $table->string('reply_to_email')->nullable();
            $table->string('location_phone_num')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('loc_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('location_options', function (Blueprint $table) {
            //
           $table->dropColumn('reply_to_name');
           $table->dropColumn('reply_to_email');
           $table->dropColumn('location_phone_num');
           $table->dropColumn('logo_path');
           $table->dropColumn('loc_path');
        });
    }
}
