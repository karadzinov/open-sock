<?php

namespace App\Listeners;

use App\Events\ThermostatEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Monolog\Logger;
use Monolog\Handler\SlackHandler;

class ThermostatListener
{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ThermostatEvent $event
     *
     * @return void
     */
    public function handle(ThermostatEvent $event)
    {
        $log = new Logger('name');
        $slackHandler
            = new SlackHandler('xoxp-478547737904-480685261574-515918399861-c2137c8bebb7d4fe1b9f560433fa1b7a',
            '#sapo-errors', 'Monolog', true, null, \Monolog\Logger::INFO);

        $log->pushHandler($slackHandler);

        // add records to the log
        $log->info($event);
    }
}
