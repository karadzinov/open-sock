<?php

namespace App\Http\Resources;

use App\Http\Controllers\Api\ThermostatController;
use App\Models\FirstSetUpThermostat;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;


class PropertiesResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */

    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'user_id'               => $this->user_id,
            'name'                  => $this->name,
            'status_all'            => $this->status_all(),
            'app_type'              => $this->app_type,
            'app_name'              => $this->app_name,
            'square_measure'        => $this->square_measure,
            'temp_unit'             => $this->temp_unit,
            'square_meters'         => $this->square_meters,
            'heating_rate'          => $this->heating_rate,
            'address'               => $this->address,
            'lat'                   => $this->lat,
            'lng'                   => $this->lng,
            'number_of_therm'       => $this->number_of_therm,
            'connected_thermostats' => $this->connected_thermostats,
            'weather'               => $this->weather,
            'weather_info'          => $this->weather_info,
            'weather_description'   => $this->weather_description,
            'weather_icon'          => $this->weather_icon,
            'owner'                 => $this->owner,
            'role'                  => $this->role(),
            'thermostats'           => ThermostatResource::collection($this->thermostats),
            'thermostats_waiting'   => $this->waiting_thermostats()
        ];
    }


    public function waiting_thermostats()
    {
        $thermostats = $this->firstSetup();
        $terms = [];
        foreach($thermostats as $thermostat)
        {
            $created_at =  new Carbon($thermostat['created_at']);
            $created_at->timezone($thermostat['time_zone']);
            $thermostat['created_at'] = $created_at->format('Y-m-d H:i:s');
            $terms[] = $thermostat;
        }
        return $terms;
    }

    public function role()
    {
        return \Auth::user()->role->name;
    }
}
