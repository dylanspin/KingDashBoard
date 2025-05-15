<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegressionTestingLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('regression_testing_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('testing_session_id')->nullable();
            $table->unsignedInteger('rule_id')->nullable();
            $table->foreign('rule_id')
                ->references('id')
                ->on('parking_rules_names')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->integer('device_id')->nullable();
            $table->string('type')->nullable();
            $table->string('message')->nullable();
            $table->string('status')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('regression_testing_logs');
    }
}
