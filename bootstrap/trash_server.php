<?php

use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\HeaderBag;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Carbon\Carbon;



use App\Communication;

#reactphp library
use React\EventLoop\Factory;
use React\Socket\Server;
use React\Socket\ConnectionInterface;

$app = require __DIR__ . '/../bootstrap/app.php';
$loop = React\EventLoop\Factory::create();
$server = new Server('46.101.119.91:7500', $loop);

 function bin_to_str($string)
 {
     $hexstr = unpack('H*', $string);
     return array_shift($hexstr);
 }


$server->on('connection', function (ConnectionInterface $conn) use ($loop) {
    echo '[connected]' . PHP_EOL;
    // count the number of bytes received from this connection
    $bytes = 0;
    $data = "";


    $conn->on('data', function ($data) use (&$bytes, &$conn) {
        $bytes += strlen($data);

        
        
        DB::table('communicationLog')->insert([
                'address' => $conn->getRemoteAddress(),
                'data' =>  str_to_hex($data),
                'bytes' =>  $bytes,
                'message' => '[data received]',
                'created_at' => Carbon::now(),
        ]);
    });


    
   


    // report average throughput once client disconnects
    $t = microtime(true);
    $conn->on('close', function () use ($conn, $t, &$bytes) {
        $t = microtime(true) - $t;
        echo '[disconnected after receiving ' . $bytes . ' bytes in ' . round($t, 3) . 's => ' . round($bytes / $t / 1024 / 1024, 1) . ' MiB/s]' . PHP_EOL;
        $message =  '[disconnected after receiving ' . $bytes . ' bytes in ' . round($t, 3) . 's => ' . round($bytes / $t / 1024 / 1024, 1) . ' MiB/s]';


        DB::table('communicationLog')->insert([
                'address' => $conn->getRemoteAddress(),
                'data' =>  '[connection close]',
                'bytes' =>  $bytes,
                'message' => $message,
                'created_at' => Carbon::now(),
        ]);
    });
});


$server->on('error', 'printf');

echo 'Listening on ' . $server->getAddress() . PHP_EOL;
$loop->run();
