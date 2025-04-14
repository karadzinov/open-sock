<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommandNameAndCommandValueToCommands extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commands', function (Blueprint $table) {
            $table->string('command_name')->nullable();
            $table->string('command_value')->nullable();
            $table->boolean('executed')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commands', function (Blueprint $table) {
            $table->dropColumn(['command_name, command_value, executed']);
        });
    }
}
