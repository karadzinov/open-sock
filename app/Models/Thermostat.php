<?php

namespace App\Models;

use App\Console\Commands\SchedulerCommand;
use Illuminate\Database\Eloquent\Model;

class Thermostat extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'thermostat';

    protected $fillable
        = [
            'room_name',
            'room_temp',
            'floor_temp',
            'comp_temp',
            'target_temp',
            'relay_status',
            'signal_strength',
            'set_temp',
            'sched_temp',
            'max_temp',
            'min_temp',
            'offset_sign',
            'offset_temp',
            'temp_limiter',
            'mode',
            'sensors_mode',
            'temp_measurement',
            'relay_opera',
            'relay_limit',
            'sensitivity',
            'differential',
            'cool_heat_mode',
            'boiler_duration',
            'bathroom_on_low_heat',
            'bathroom_delay_stby_low_heat',
            'bathroom_on_med_heat',
            'bathroom_delay_stby_med_heat',
            'bathroom_on_high_heat',
            'bathroom_delay_stby_high_heat',
            'cool_room_check',
            'boost',
            'ligth_intensity',
            'enab_vibration',
            'enab_matrix',
            'enab_hart_beep_led',
            'enab_lock',
            'last_command',
            'last_operation',
            'last_operation_value',
            'restore_default',
            'encription_code',
            'indor_sensor',
            'floor_sensor',
            'commu_status',
            'cool_heat',
            'fan_speed',
            'previous_state',
            'temp_measurement',
            'square_measurement',
            'force_off',
            'mat_power',
            'room_area',
            'status_mode'
        ];

    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }

    public function properties()
    {
        return $this->belongsToMany('App\Models\Properties');
    }

    public function lastOnline($id, $ip, $port)
    {
        $stats = ThermostatStats::where('thermostat_id', '=', $id)
            ->where('ip', '=', $ip)
            ->where('port', '=', $port)
            ->orderBy('id', 'desc')
            ->first();

        $carbon = \Carbon\Carbon::parse($stats['disconnected'])->diffForHumans();
        return $carbon;
    }
    public function checkOnline($id, $ip, $port)
    {
        $stats = ThermostatStats::where('thermostat_id', '=', $id)
            ->where('ip', '=', $ip)
            ->where('port', '=', $port)
            ->where('disconnected', null)
            ->first();

        return $stats ? 1 : 0;
    }

    public function checkAuto($id)
    {

        // check day of the week
        $carbon = new \Carbon\Carbon();
        $carbon->setTimezone('Europe/Skopje');
        $time_now = $carbon->now('Europe/Skopje');

        $dayOfTheWeek = $time_now->dayOfWeek;

        $checkCommandNow = CommandScheduler::where('thermostat_id', '=', $id)
            ->where('day', '=', $dayOfTheWeek)
            ->where('command_name', '!=', 'mode')
            ->count();

        return $checkCommandNow === 0 ? false : true;
    }

}
