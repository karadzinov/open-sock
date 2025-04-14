<?php

namespace App\Console\Commands;


use App\Models\Thermostat;
use Illuminate\Console\Command;
use App\Models\CommandScheduler;
use App\Models\Commands;
use Carbon\Carbon;
use App\Jobs\ProcessCommands;
use App\Http\Controllers\Api\ThermostatController;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\ProcessScheduler;

class Scheduler extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:scheduler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run scheduler';

    public $thermController;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ThermostatController $thermostatController)
    {
        $this->thermController = $thermostatController;
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
        $carbon->setTimezone('Europe/Skopje');
        $time_now = $carbon->now('Europe/Skopje');

        $dayOfTheWeek = $time_now->dayOfWeek;


        $commands = CommandScheduler::where('start_time', '>=',
            $time_now->subMinute()->toTimeString())
            ->where('day', '=', $dayOfTheWeek)
            ->where('start_time', '<=', $time_now->addMinute()->toTimeString())
            ->get();


        foreach ($commands as $command) {

            $thermostat = Thermostat::where('id', $command->thermostat_id)->first();

            if ($thermostat->mode === 3) {
                if ($command->command_name != "mode") {

                    $ifCommand = $this->thermController->makeModeCommand(20, 2, $command->user_id, $command->thermostat_id);
                    $c = new Commands();
                    $c->user_id = $command->user_id;
                    $c->thermostat_id = $command->thermostat_id;
                    $c->command = $ifCommand;
                    $c->command_name = "mode";
                    $c->command_value = 2;
                    $c->executed = 0;

                    $c->save();

                    dispatch(new ProcessCommands($ifCommand, $command->thermostat_id));
                }
            }


            if ($thermostat->status_mode >= 2) {


                Artisan::call("command:scheduler-command",
                    [
                        'thermostat_id' => $thermostat->id,
                        'setCommand' => $command->command
                    ]);



                $c = new Commands();
                $c->user_id = $command->user_id;
                $c->thermostat_id = $command->thermostat_id;
                $c->command = $command->command;
                $c->command_name = $command->command_name;
                $c->command_value = $command->command_value;
                $c->executed = 0;
                $c->save();


                sleep(3);

                Artisan::call("command:scheduler-command",
                    [
                        'thermostat_id' => $thermostat->id,
                        'setCommand' => $command->command
                    ]);


                Artisan::call("command:scheduler-command",
                    [
                        'thermostat_id' => $thermostat->id,
                        'setCommand' => $command->command
                    ]);


                Artisan::call("command:scheduler-command",
                    [
                        'thermostat_id' => $thermostat->id,
                        'setCommand' => $command->command
                    ]);

                  //   $request = new Request($sendData);
                  //  $this->thermController->sentCommands($request, false);

                dispatch((new ProcessScheduler($command->command, $command->thermostat_id))->onQueue('high'));


                if ($thermostat->mode === 0) {
                    $wakeup = $this->thermController->makeModeCommand(20, 2, $command->user_id, $command->thermostat_id);

                     Artisan::call("command:scheduler-command",
                         [
                             'thermostat_id' => $thermostat->id,
                             'setCommand' => $wakeup
                         ]);

                     sleep(3);


                     Artisan::call("command:scheduler-command",
                         [
                             'thermostat_id' => $thermostat->id,
                             'setCommand' => $wakeup
                         ]);

                     Artisan::call("command:scheduler-command",
                         [
                             'thermostat_id' => $thermostat->id,
                             'setCommand' => $wakeup
                         ]);

                    //
                    dispatch((new ProcessCommands($wakeup, $command->thermostat_id))->onQueue('high'));
                }
            }
        }
        return true;
    }
}
