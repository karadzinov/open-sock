<?php

namespace App\Events;



use App\Models\Thermostat;
use Pusher\Pusher;

class PusherEvent extends Event
{
    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $thermostat_id;
    public $firstTime;


    public function __construct($thermostat_id, $firstTime = false)
    {
        $this->thermostat_id = $thermostat_id;
        $this->firstTime = $firstTime;
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
        $data['thermostat'] = $thermostat;

        $property = $thermostat->properties->first();

        $info = $pusher->get_channel_info('channel-'.$property['id'], array('info' => 'subscription_count'));
        $subscription_count = $info->subscription_count;


        $propertyInfo = $pusher->get_channel_info('property-'.$property['id'], ["info" => "subscription_count"]);
        if($propertyInfo->subscription_count > 0)
        {
            $pull = ["pull-".$this->thermostat_id => true];
            $pusher->trigger('property-'.$property['id'], 'property', $pull);
        }


        if ($this->firstTime) {
            $pusher->trigger('new-thermostat', $thermostat->therm_mac_address,
                $data);
        } else {
            if($subscription_count > 0)
            {
                $pusher->trigger('channel-'.$property['id'], 'thermostat-'.$this->thermostat_id, $data);
            }
        }
    }

}
