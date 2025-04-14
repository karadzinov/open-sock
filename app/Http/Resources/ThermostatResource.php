<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ThermostatResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                            => $this->id,
            'room_name'                     => $this->room_name,
            'room_temp'                     => $this->room_temp,
            'floor_temp'                    => $this->floor_temp,
            'comp_temp'                     => $this->comp_temp,
            'target_temp'                   => $this->target_temp,
            'relay_status'                  => $this->relay_status,
            'signal_strength'               => $this->signal_strength,
            'set_temp'                      => $this->set_temp,
            'sched_temp'                    => $this->sched_temp,
            'max_temp'                      => $this->max_temp,
            'min_temp'                      => $this->min_temp,
            'offset_sign'                   => $this->offset_sign,
            'offset_temp'                   => $this->offset_temp,
            'temp_limiter'                  => $this->temp_limiter,
            'mode'                          => $this->mode,
            'status_mode'                   => $this->status_mode,
            'sensors_mode'                  => $this->sensors_mode,
            'temp_measurement'              => $this->temp_measurement,
            'relay_opera'                   => $this->relay_opera,
            'relay_limit'                   => $this->relay_limit,
            'sensitivity'                   => $this->sensitivity,
            'differential'                  => $this->differential,
            'cool_heat_mode'                => $this->cool_heat_mode,
            'boiler_duration'               => $this->boiler_duration,
            'bathroom_on_low_heat'          => $this->bathroom_on_low_heat,
            'bathroom_delay_stby_low_heat'  => $this->bathroom_delay_stby_low_heat,
            'bathroom_on_med_heat'          => $this->bathroom_on_med_heat,
            'bathroom_delay_stby_med_heat'  => $this->bathroom_delay_stby_med_heat,
            'bathroom_on_high_heat'         => $this->bathroom_on_high_heat,
            'bathroom_delay_stby_high_heat' => $this->bathroom_delay_stby_high_heat,
            'cool_room_check'               => $this->cool_room_check,
            'boost'                         => $this->boost,
            'ligth_intensity'               => $this->ligth_intensity,
            'enab_vibration'                => $this->enab_vibration,
            'enab_matrix'                   => $this->enab_matrix,
            'enab_hart_beep_led'            => $this->enab_hart_beep_led,
            'enab_lock'                     => $this->enab_lock,
            'last_command'                  => $this->last_command,
            'last_operation'                => $this->last_operation,
            'last_operation_value'          => $this->last_operation_value,
            'restore_default'               => $this->restore_default,
            'encription_code'               => $this->encription_code,
            'indor_sensor'                  => $this->indor_sensor,
            'floor_sensor'                  => $this->floor_sensor,
            'commu_status'                  => $this->commu_status,
            'cool_heat'                     => $this->cool_heat,
            'fan_speed'                     => $this->fan_speed,
            'room_area'                     => $this->room_area,
            'mat_power'                     => $this->mat_power,
            'thermostat_type_id'            => $this->thermostat_type_id,
            'temp_limitation'               => $this->temp_limitation,
            'square_measurement'            => $this->square_measurement,
            'previous_state'                => $this->previous_state,
            'force_off'                     => $this->force_off(),
            'app_type'                      => $this->app_type(),
            'online'                        => $this->checkOnline($this->id, $this->ip_address, $this->port),
            'last_online'                   => $this->lastOnline($this->id, $this->ip_address, $this->port),
            'auto_enabled'                  => $this->checkAuto($this->id),
        ];
    }

    public function app_type()
    {
        $app_type = false;
        try {
            $app_type = $this->properties()->where('thermostat_id', '=', $this->id)->first()->app_type;
        } catch (\Exception $e) {

        }

        return $app_type;
    }

    public function force_off()
    {
        if ($this->mode === 0) {
            if ($this->status_mode >= 2) {
                return 0;
            }
        }

        return 1;
    }
}
