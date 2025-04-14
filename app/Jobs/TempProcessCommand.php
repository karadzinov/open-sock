<?php


namespace App\Jobs;


use App\Models\Commands;
use App\Models\Thermostat;
use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;

class TempProcessCommand extends Job
{

    public $thermostat_id;
    public $delay;
    public $trim_command;
    public $timeNow;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( $trim_command,$thermostat_id,$delay, $timeNow)
    {
        $this->delay = $delay;
        $this->trim_command = $trim_command;
        $this->thermostat_id = $thermostat_id;
        $this->timeNow = $timeNow;
        // $this->handle();

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $thermostat = Thermostat::where('id', '=', $this->thermostat_id)->first();

        $lastTime = $thermostat->last_temp_update;
        $lastTime = new \Carbon\Carbon($lastTime);

        $now = new \Carbon\Carbon();
        $now->now();

        $isOK = false;
        if($lastTime->diffInSeconds($now) > 1)
        {
            dump('OK');
            $isOK = true;
        }
        else {
            dump('NOT');
            $isOK = true;
        }

        if($isOK){
            if ($this->delay) {
                dispatch((new ProcessCommands($this->trim_command, $this->thermostat_id))->delay($this->delay));
            } else {
                dispatch((new ProcessCommands($this->trim_command, $this->thermostat_id))->onQueue('high'));
            }
        }

        $thermostat->last_temp_update = $this->timeNow;
        $thermostat->save();
    }
}