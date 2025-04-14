<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Contracts\Bus\SelfHandling;
use App\Models\Thermostat;

#reactphp library
use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;
use App\Models\Commands;


class ProcessScheduler extends Job
{

    public $prepared_pack;
    public $thermostat_id;
    public $pusher;
    public $temp;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($prepared_pack, $thermostat_id, $temp = false)
    {
        $this->prepared_pack = $prepared_pack;
        $this->thermostat_id = $thermostat_id;
        // $this->handle();

    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $send_data = pack("H*", $this->prepared_pack);
        $thermostat = Thermostat::find($this->thermostat_id);

        if($this->temp) {
            sleep(1);
            $command = Commands::where('thermostat_id', '=', $thermostat->id)->first();
            $send_data = pack('H*', $command->command);
        }


        $ther_ip_port = $thermostat['ip_address'].":".$thermostat['port'];


        $loop = Factory::create();
        $connector = new Connector($loop);

        $connector->connect(env('SOCKET_IP').':'.env('SOCKET_PORT'))->then(function (ConnectionInterface $connection) use ($ther_ip_port, $send_data) {
            $connection->write($ther_ip_port."|||".$send_data."|||send");
            $connection->end();

        });

        $loop->run();


    }


}
