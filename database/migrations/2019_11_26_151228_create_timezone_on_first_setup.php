<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimezoneOnFirstSetup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('first_set_up_thermostat', function (Blueprint $table) {
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
        Schema::table('first_set_up_thermostat', function (Blueprint $table) {
            $table->dropColumn('time_zone');
        });
    }
}
