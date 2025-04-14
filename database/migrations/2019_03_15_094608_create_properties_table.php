<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('number_of_therm');
            $table->enum('square_measure', ['m', 'f'])->default('m');
            $table->float('square_meters', 5,2)->nullable();
            $table->enum('temp_unit', ['C', 'F'])->default('C');
            $table->float('heating_rate', 5,2)->nullable();
            $table->string('address')->nullable();
            $table->decimal('lat', 10,8)->nullable();
            $table->decimal('lng', 11,8)->nullable();
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
        Schema::dropIfExists('properties');
    }
}
