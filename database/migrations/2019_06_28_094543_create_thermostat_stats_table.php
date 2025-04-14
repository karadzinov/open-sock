<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThermostatStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thermostat_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('thermostat_id')->unsigned();
            $table->foreign('thermostat_id')->references('id')->on('thermostat')->onDelete('cascade');
            $table->string('ip');
            $table->string('port');
            $table->dateTime('connected');
            $table->dateTime('disconnected')->nullable();
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
        Schema::dropIfExists('thermostat_stats');
    }
}
