<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeMatPowerToThermostat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('thermostat', function (Blueprint $table) {
            $table->float('mat_power', 7, 2)->change();
        });
    }
}
