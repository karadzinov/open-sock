<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FirstSetUpThermostat;
use Carbon\Carbon;


class FirstSetupCheck extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:delete-first-setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deleting first setup';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $carbon = new Carbon();

        $time_now = $carbon->now('Europe/Skopje');

        $firstSetups = FirstSetUpThermostat::all();


        foreach ($firstSetups as $firstSetup) {

            $created_at =  new Carbon($firstSetup->created_at);

            $created_at->addMinutes(5);
            $created_at->timezone('Europe/Skopje');
            // $created_at->addHour();


            dump($time_now);
            dump($created_at);

            if($time_now >= $created_at)
            {

                $firstSetup->delete();
            }



        }
    }
}
