<?php

namespace App\Jobs;

use App\Models\Thermostat;
use App\Http\Resources\ThermostatResource;
use Pusher\Pusher;
use App\Models\Commands;

class PusherSetTempJob extends Job
{

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $thermostat_id;
    public $from_thermostat;

    public function __construct($thermostat_id, $from_thermostat)
    {
        $this->thermostat_id = $thermostat_id;
        $this->from_thermostat = $from_thermostat;

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
        $set_temp = $thermostat->set_temp;
        $sched_temp = $thermostat->sched_temp;
        $thermostat = new ThermostatResource($thermostat);

        if ($thermostat) {

            $data['thermostat'] = $thermostat;

            $property = $thermostat->properties()->first();

            $command = Commands::where('thermostat_id', '=', $this->thermostat_id)->where('command_name', '=', 'set_temp')->orWhere('command_name', '=', 'sched_temp')->orderBy('id', 'desc')->first();


            if($command)
            {
                if($set_temp == $command->command_value || $sched_temp == $command->command_value)
                {
                    $pusher->trigger('channel-'.$property['id'], 'thermostat-temp-'.$this->thermostat_id, $data, $command->socket_id);
                }
            }



            if($this->from_thermostat)
            {
                $pusher->trigger('channel-'.$property['id'], 'thermostat-temp-'.$this->thermostat_id, $data);
            }
        }

    }

}
