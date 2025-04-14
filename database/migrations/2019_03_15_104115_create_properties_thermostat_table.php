<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePropertiesThermostatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('properties_thermostat', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('properties_id')->unsigned();
            $table->integer('thermostat_id')->unsigned();
            $table->foreign('properties_id')->references('id')->on('properties')->onDelete('cascade');
            $table->foreign('thermostat_id')->references('id')->on('thermostat')->onDelete('cascade');
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
        Schema::dropIfExists('properties_thermostat');
    }
}
