<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimeNowToThermostat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('thermostat', function (Blueprint $table) {
            $table->timestamp('last_temp_update')->after('updated_at')->nullable();
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
            $table->dropColumn('last_temp_update');
        });
    }
}
