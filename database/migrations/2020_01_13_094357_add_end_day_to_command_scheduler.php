<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEndDayToCommandScheduler extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scheduler_commands', function (Blueprint $table) {
            $table->integer('end_day')->nullable()->after('day');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('scheduler_commands', function (Blueprint $table) {
            $table->dropColumn('end_day');
        });
    }
}


