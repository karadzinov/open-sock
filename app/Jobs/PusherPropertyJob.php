<?php

namespace App\Jobs;

use Pusher\Pusher;

class PusherPropertyJob extends Job
{

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $property_id;
    public $thermostat_id;

    public function __construct($property_id, $thermostat_id)
    {
        $this->property_id = $property_id;
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

        $pull = ["pull-".$this->thermostat_id => true];
        $pusher->trigger('property-'.$this->property_id, 'property', $pull);


    }

}
