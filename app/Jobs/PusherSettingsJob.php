<?php

namespace App\Jobs;

use App\Models\Thermostat;
use App\Http\Resources\ThermostatResource;
use Pusher\Pusher;

class PusherSettingsJob extends Job
{

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $thermostat_id;

    public function __construct($thermostat_id)
    {
        $this->thermostat_id = $thermostat_id;

    }


    public function handle()
    {

        $options = [
            'cluster' => env('PUSHER_CLUSTER'),
            'useTLS'  => true,
        ];
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );

        $thermostat = Thermostat::where('id', $this->thermostat_id)->first();
        $thermostat = new ThermostatResource($thermostat);

        if ($thermostat) {


            $data['thermostat'] = $thermostat;

            $property = $thermostat->properties()->first();

            $pusher->trigger('channel-'.$property['id'], 'thermostat-settings-'.$this->thermostat_id, $data);

        }

    }

}
