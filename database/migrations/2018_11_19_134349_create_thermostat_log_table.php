<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThermostatLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thermostatLog', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('set');
            $table->tinyInteger('ambient');
            $table->timestamp('date');
            $table->integer('thermostatId')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('thermostatId')->references('id')->on('thermostat');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('thermostatLog');
    }
}
