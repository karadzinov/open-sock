<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\StartSocketCommand::class,
        Commands\SocketServerProfiler::class,
        Commands\Scheduler::class,
        Commands\FirstSetupCheck::class,
        Commands\SchedulerCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $schedule->command('command:scheduler')->everyMinute();
        $schedule->command('command:delete-first-setup')->everyMinute();
      //  $schedule->command('command:scheduler-test')->everyMinute();
       // $schedule->command('server:benchmark')->everyMinute();
    }
}
