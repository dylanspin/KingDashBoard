<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccessRightsToUserlistUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('userlist_users', function (Blueprint $table) {
            $table->integer('group_access_id')->nullable()->unsigned()->index();
            $table->foreign('group_access_id')->references('id')->on('group_accesses')->onDelete('cascade');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('userlist_users', function (Blueprint $table) {
            Schema::table('userlist_users', function (Blueprint $table) {
            $table->dropColumn(['group_access_id']);
			});
        });
    }
}
