<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFirstSetUpThermostatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('first_set_up_thermostat', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('property_id');
            $table->integer('user_id');
            $table->char('mac_address', 18);
            $table->string('room_name');
            $table->float('room_area', 7,2);
            $table->integer('mat_power');
            $table->integer('thermostat_type_id');
            $table->integer('temp_limitation');
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
        Schema::dropIfExists('first_set_up_thermostat');
    }
}
