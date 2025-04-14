<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSchedulerCommand extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scheduler_commands', function (Blueprint $table) {
            $table->increments('id');
            $table->time('start_time')->nullable();
            $table->integer('user_id');
            $table->integer('thermostat_id');
            $table->integer('day');
            $table->text('command');
            $table->text('command_name');
            $table->text('command_value');
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
        Schema::dropIfExists('scheduler_commands');
    }
}
