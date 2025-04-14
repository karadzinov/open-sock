<?php

namespace App\Console\Commands;


use App\Jobs\ProcessScheduler;
use Illuminate\Console\Command;


class SchedulerCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:scheduler-command {thermostat_id} {setCommand=F1F2A11c78000000c5FEFF}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scheduler command';

    public $thermController;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {

        //     $this->thermController = $thermostatController;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        dispatch(new ProcessScheduler($this->argument('setCommand'), $this->argument('thermostat_id')));
        return true;
    }
}
