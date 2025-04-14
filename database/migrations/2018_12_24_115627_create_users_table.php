<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->integer('role_id')->unsigned();
            $table->integer('country')->unsigned();
            $table->foreign('country')->references('id')->on('countries');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}

