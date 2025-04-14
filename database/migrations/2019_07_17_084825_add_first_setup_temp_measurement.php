<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFirstSetupTempMeasurement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('first_set_up_thermostat', function (Blueprint $table) {
            $table->string('temp_measurement')->nullable();
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
            $table->dropColumn('temp_measurement');
        });
    }
}
