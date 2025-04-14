<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddThermostatSetTempUser extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('thermostat', function (Blueprint $table) {
            $table->integer('set_temp_user')->nullable()->after('set_temp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('thermostat', function (Blueprint $table) {
            $table->dropColumn('set_temp_user');
        });
    }
}