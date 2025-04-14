<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThermostatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thermostat', function (Blueprint $table) {
            $table->increments('id');
            $table->string('room_name')->default('Bathroom');
            $table->string('ip_address')->default('0.0.0.0');
            $table->integer('port')->default(65000);
            $table->char('therm_mac_address', 18)->default('00:00:00:00:00:00');
            $table->tinyInteger('room_temp')->default(0);
            $table->tinyInteger('floor_temp')->default(0);
            $table->tinyInteger('comp_temp')->default(0);
            $table->tinyInteger('target_temp')->default(0);
            $table->boolean('relay_status')->default(false);
            $table->tinyInteger('signal_strength')->default(0);
            $table->tinyInteger('set_temp')->default(22);
            $table->tinyInteger('sched_temp')->default(0);
            $table->tinyInteger('max_temp')->default(50);
            $table->tinyInteger('min_temp')->default(5);
            $table->boolean('offset_sign')->default(false);
            $table->tinyInteger('offset_temp')->default(0);
            $table->tinyInteger('temp_limiter')->default(40);
            $table->tinyInteger('mode')->default(0);
            $table->tinyInteger('sensors_mode')->default(0);
            $table->boolean('temp_measurement')->default(false);
            $table->boolean('relay_opera')->default(false);
            $table->tinyInteger('relay_limit')->default(6);
            $table->float('sensitivity', 2,1)->default(1.5);
            $table->tinyInteger('differential')->default(0);
            $table->tinyInteger('cool_heat_mode')->default(0);
            $table->smallInteger('boiler_duration')->default(30);
            $table->char('home_router_mac_address', 18)->default('00:00:00:00:00:00');
            $table->tinyInteger('bathroom_on_low_heat')->default(20);
            $table->tinyInteger('bathroom_delay_stby_low_heat')->default(20);
            $table->tinyInteger('bathroom_on_med_heat')->default(20);
            $table->tinyInteger('bathroom_delay_stby_med_heat')->default(15);
            $table->tinyInteger('bathroom_on_high_heat')->default(20);
            $table->tinyInteger('bathroom_delay_stby_high_heat')->default(10);
            $table->smallInteger('cool_room_check')->default(60);
            $table->smallInteger('boost')->default(180);
            $table->tinyInteger('ligth_intensity')->default(1);
            $table->boolean('enab_vibration')->default(true);
            $table->boolean('enab_matrix')->default(true);
            $table->boolean('enab_hart_beep_led')->default(true);
            $table->boolean('enab_lock')->default(true);
            $table->tinyInteger('last_command')->default(0);
            $table->tinyInteger('last_operation')->default(0);
            $table->tinyInteger('last_operation_value')->default(0);
            $table->boolean('restore_default')->default(true);
            $table->smallInteger('encription_code')->default(0);
            $table->boolean('indor_sensor')->default(false);
            $table->boolean('floor_sensor')->default(false);
            $table->boolean('commu_status')->default(false);
            $table->boolean('cool_heat')->default(true);
            $table->tinyInteger('fan_speed')->default(1);
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
        Schema::dropIfExists('thermostat');
    }

    #php artisan make:migration create_thermostat_log_table --create=thermostatLog
}
