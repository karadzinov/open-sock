<?php

namespace App\Jobs;

use App\Models\Thermostat;
use Pusher\Pusher;

class PusherFirstTimeJob extends Job
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


        if ($thermostat) {
            $data['thermostat'] = $thermostat;
            $pusher->trigger('new-thermostat', $thermostat->therm_mac_address, $data);
        }

    }

}
