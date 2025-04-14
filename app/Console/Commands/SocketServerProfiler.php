<?php
/**
 *
 * PHP version >= 7.0
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#reactphp library
use React\EventLoop\Factory;
use React\Socket\Connector;
use React\Socket\ConnectionInterface;

#Local library
use App\Communication;

/**
 * Class startSocketCommand
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class SocketServerProfiler extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "server:benchmark";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Start socket server benchmark with 10 thermostats";

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
        $loop = Factory::create();
        $connector = new Connector($loop);
        $ip =  env('SOCKET_IP');
        $port = env('SOCKET_PORT');

        //Laravel is namespaced, so you must use "\Exception":
        echo "Client is conecting to...: " . $ip . ':' . $port . "\n";
        

        $connector->connect($ip . ':' . $port)->then(function (ConnectionInterface $connection) use ($ip, $loop) {
            echo '[connected]' . PHP_EOL;


            $loop->addPeriodicTimer(1, function () use (&$i,$connection) {
                echo ++$i, PHP_EOL;
                $pakets = [
                    "f1f2011600161900a020a6134b95d122feff",
                    "f1f2021c193205002818feff",
                    "f1f20300000100000002001e001414140f140a5a78020101013afeff",
                    "f1f20300000100000002001e001414140f140a5a78020101013afeff",
                    "f1f20500000000000000000000000005feff",
                    "f1f20600fe00f8feff",
                    "f1f20701010000000007feff"
                ];

                $temp32 = 'F1 F2 A1 0A 20 00 00 00 8B FE FF';
               // $connection->write(trim($pakets[array_rand($pakets)]));
                $connection->write($temp32);
            });
            
            $connection->on('data', function ($data) {
                echo $data;
            });
            
            $connection->on('close', function () {
                echo '[CLOSED]' . PHP_EOL;
            });
        }, 'printf');

       
     
        $loop->run();
    }
}
