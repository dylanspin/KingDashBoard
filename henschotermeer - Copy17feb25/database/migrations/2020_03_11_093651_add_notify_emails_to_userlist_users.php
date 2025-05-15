<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNotifyEmailsToUserlistUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('userlist_users', function (Blueprint $table) {
            $table->string('notify_emails')->nullable();
            $table->tinyInteger('user_arrival_notification')->default(0);
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
            $table->dropColumn(['notify_emails', 'user_arrival_notification']);
        });
    }
}
