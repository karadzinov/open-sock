<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEndTimeColumnInSchedulerCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scheduler_commands', function (Blueprint $table) {
            $table->time('end_time')->nullable()->after('start_time');
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
            $table->dropColumn('end_time');
        });
    }
}
