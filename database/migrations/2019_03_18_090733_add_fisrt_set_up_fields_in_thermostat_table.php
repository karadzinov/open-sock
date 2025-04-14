<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFisrtSetUpFieldsInThermostatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('thermostat', function (Blueprint $table) {
            $table->float('room_area', 7,2)->after('fan_speed')->default(1);
            $table->integer('mat_power')->after('room_area')->default(1);
            $table->integer('thermostat_type_id')->after('mat_power')->default(0);
            $table->integer('temp_limitation')->after('thermostat_type_id')->default(22);
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
            $table->dropColumn(['room_area', 'mat_power', 'thermostat_type_id', 'temp_limitation']);
        });
    }
}
