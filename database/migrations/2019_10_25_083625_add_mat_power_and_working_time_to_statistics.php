<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMatPowerAndWorkingTimeToStatistics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statistic', function (Blueprint $table) {
            $table->float('mat_power', 7, 2)->nullable();
            $table->bigInteger('working_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statistic', function (Blueprint $table) {
            $table->dropColumn(['mat_power', 'working_time']);
        });
    }
}
