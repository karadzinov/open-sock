<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

#reactphp library
use React\EventLoop\Factory;
use React\Socket\Server;
use React\Socket\Connector;
use React\Socket\ConnectionInterface;
use React\Stream\ReadableResourceStream;
use React\Stream\WritableResourceStream;

#Sysinfo
use Linfo\Laravel\Models\Linfo;

class ApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function str_to_hex($string)
    {
        $hexstr = unpack('H*', $string);
        return array_shift($hexstr);
    }


    public function flash_thermostat(Request $request)
    {
        return response()->json('success');
    }

    //
    public function get_sysinfo()
    {
        $info = new Linfo();
        $connection =  DB::table('config')->where('key', 'num_of_connection')->first();
        return response()->json(["info"=>$info->getProcesseds(), "num_of_connections"=>$connection->value]);
    }
}
