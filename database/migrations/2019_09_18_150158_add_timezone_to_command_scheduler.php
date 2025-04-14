<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimezoneToCommandScheduler extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scheduler_commands', function (Blueprint $table) {
            $table->string('time_zone')->default('Asia/Jerusalem');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('scheduler_commands', function (Blueprint $table) {
            $table->dropColumn('time_zone');
        });
    }
}
