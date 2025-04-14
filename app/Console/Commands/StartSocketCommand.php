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

#reactphp library
use React\EventLoop\Factory;
use React\Socket\Server;
use React\Socket\ConnectionInterface;
use React\Socket\LimitingServer;
use Carbon\Carbon;
use App\Models\ThermostatStats;


use App\Http\Controllers\Socket\ApiSocketController;

/**
 * Class startSocketCommand
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class StartSocketCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "server:run";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Start new socket server";

    public function __construct(ApiSocketController $apiSockC)
    {
        parent::__construct();
        $this->apiSockC = $apiSockC;
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $loop = Factory::create();
        $ip = env('SOCKET_IP');
        $port = env('SOCKET_PORT');

        $socket = new Server($ip . ':' . $port, $loop);
        $socket = new LimitingServer($socket, null);


        echo "socket server is now running on: " . $ip . ':' . $port . "\n";


        $socket->on('connection',
            function (ConnectionInterface $conn) use ($socket, $loop) {

                $bytes = 0;
                $address = $conn->getRemoteAddress();


                $func = function () use ($conn) {
                    $conn->close();
                };
                $timer = $loop->addTimer(15, $func);

                $conn->on('data', function ($data) use ($conn, &$bytes, $socket, $address, &$timer, $loop, $func) {

                    $loop->cancelTimer($timer);
                    $timer = $loop->addTimer(120, $func);

                    $bytes += strlen($data);

                    $receive_data = explode("|||", $data);
                    if (count($receive_data) == 3) {
                        if ($receive_data[2] == "send") {
                            foreach ($socket->getConnections() as $connection) {

                                $remote = $connection->getRemoteAddress();
                                $remote_address['ip']
                                    = trim(parse_url($remote, PHP_URL_HOST),
                                    '[]');
                                $remote_address['port']
                                    = trim(parse_url($remote, PHP_URL_PORT),
                                    '[]');
                                $client_sock_address = $remote_address['ip']
                                    . ":" . $remote_address['port'];

                                if ($client_sock_address == $receive_data[0]) {
                                    $connection->write($receive_data[1]);
                                }
                            }
                        }
                    } else {
                        $client_address['ip'] = trim(parse_url($address, PHP_URL_HOST), '[]');
                        $client_address['port'] = trim(parse_url($address, PHP_URL_PORT), '[]');
                        $this->apiSockC->getSocketData($data, $client_address, $conn);
                    }

                });

                $conn->on('close', function () use ($conn, &$bytes) {
                    if ($conn->getRemoteAddress()) {
                        $remote = $conn->getRemoteAddress();
                        $remote_address['ip'] = trim(parse_url($remote, PHP_URL_HOST), '[]');
                        $remote_address['port'] = trim(parse_url($remote, PHP_URL_PORT), '[]');
                        $thermostat_stats = ThermostatStats::where('port', '=', $remote_address['port'])
                            ->where('ip', '=', $remote_address['ip'])
                            ->first();

                        if ($thermostat_stats) {
                            $thermostat_stats->disconnected = Carbon::now();
                            $thermostat_stats->save();
                            $this->apiSockC->setDailyStatistic(0, $thermostat_stats->thermostat_id);
                            $isMaster = $this->apiSockC->checkIsMaster($thermostat_stats->thermostat_id);
                            if($isMaster)
                            {
                                $this->apiSockC->setSlavesOff($thermostat_stats->thermostat_id);
                            }

                            $this->apiSockC->pusher($thermostat_stats->thermostat_id, false, true, false, false);
                            $this->apiSockC->pusher($thermostat_stats->thermostat_id, false, true, false, false);
                            $this->apiSockC->pusher($thermostat_stats->thermostat_id, false, true, false, false);
                        }
                    }
                });

                $conn->on('error', function (Exception $e) {
                    echo 'error: ' . $e->getMessage();
                });
            });


        $loop->run();
    }
}
