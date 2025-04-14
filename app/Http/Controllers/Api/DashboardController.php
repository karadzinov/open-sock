<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\User;
use App\Models\Properties;
use App\Models\Thermostat;
use App\Models\ThermostatStats;

use App\Http\Resources\UserResource;
use App\Http\Resources\FaqResource;
use App\Http\Resources\PropertiesResource;
use App\Http\Resources\ThermostatResource;

class DashboardController extends Controller
{

    public $error;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->error = [
            'status' => 'ERROR',
            'error'  => '404 not found',
        ];
    }

    /**
     * @SWG\Get(
     *   path="/api/dashboard",
     *   summary="Get Dashboard",
     *   tags={"Dashboard"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Response(
     *     response=200,
     *     description="Success"
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="Validation Errors"
     *   ),
     *   @SWG\Response(
     *     response=500,
     *     description="Internal Server Error"
     *   )
     * )
     */

    public function index(Request $request)
    {

        $user_id = '';
        if($request->has('user'))
        {
            $user_id = $request->get('user');
        }

        $getUsers = User::all();
        $getFaq = Faq::orderBy('order')->get();
        if($user_id)
        {

            $getProperties = Properties::where('user_id', '=', $user_id)->get();
        }
        else {
            $getProperties = Properties::all();
        }

        $getThermostats = Thermostat::all();


        $users = UserResource::collection($getUsers);
        $faqs = FaqResource::collection($getFaq);
        $properties = PropertiesResource::collection($getProperties);
        $thermostats = ThermostatResource::collection($getThermostats);

        $online = 0;
        $offline = 0;

        foreach ($thermostats as $thermostat) {
            $ifOnline = $thermostat->checkOnline($thermostat->id,
                $thermostat->ip_address, $thermostat->port);
            $ifOnline === 1 ? $online++ : $offline++;
        }




        $data = [
            "users"            => $users,
            "faqs"             => $faqs,
            "properties"       => $properties,
            "thermostats"      => $thermostats,
            "totalUsers"       => $users->count(),
            "totalProperties"  => $properties->count(),
            "totalThermostats" => $thermostats->count(),
            "offline"          => $offline,
            "online"           => $online,
        ];

        return response()->json($data, 200);
    }

    public function getTechnicians()
    {

        $users = UserResource::collection(User::where('role_id', '=', 1)->get());

        return response()->json($users, 200);
    }


    public function deleteTechnicians($id)
    {

        $user = User::where('id', '=', $id)->first();
        if($user)
        {
            $user->delete();
        }

        $users = UserResource::collection(User::where('role_id', '=', 1)->get());

        return response()->json($users, 200);
    }

    function getSysInfo()
    {
        $system_load = $this->system_load();
        $memory = $this->memory_usage();
        $server_memory = $this->server_memory_usage();
        $system_cores = $this->system_cores();
        $http_connections = $this->http_connections();
        $disk_usage = $this->disk_usage();
        $kernel_version = $this->kernel_version();
        $number_processes = $this->number_processes();

        $data =
            [
                "systemLoad"       => $system_load,
                "memory"           => $memory,
                "serverMemory"     => $server_memory,
                "systemCores"      => $system_cores,
                "httpConnections"  => $http_connections,
                "discUsage"        => $disk_usage,
                "kernelVersion"    => $kernel_version,
                "numberProcesses"  => $number_processes
            ];

        return response()->json($data, 200);

    }
    function system_load($coreCount = 2, $interval = 1)
    {
        $rs = sys_getloadavg();
        $interval = $interval >= 1 && 3 <= $interval ? $interval : 1;
        $load = $rs[$interval];

        return round(($load * 100) / $coreCount, 2);
    }

    function system_cores()
    {

        $cmd = "uname";
        $OS = strtolower(trim(shell_exec($cmd)));

        switch ($OS) {
            case('linux'):
                $cmd = "cat /proc/cpuinfo | grep processor | wc -l";
                break;
            case('freebsd'):
                $cmd = "sysctl -a | grep 'hw.ncpu' | cut -d ':' -f2";
                break;
            default:
                unset($cmd);
        }

        if ($cmd != '') {
            $cpuCoreNo = intval(trim(shell_exec($cmd)));
        }

        return empty($cpuCoreNo) ? 1 : $cpuCoreNo;

    }

    function http_connections()
    {

        if (function_exists('exec')) {


            @exec('netstat -an | egrep \':80|:443\' | awk \'{print $5}\' | grep -v \':::\*\' |  grep -v \'0.0.0.0\'',
                $results);

            $unique = [];
            $www_total_count = 0;
            $www_unique_count = 0;
            foreach ($results as $result) {
                $array = explode(':', $result);
                $www_total_count++;

                if (preg_match('/^::/', $result)) {
                    $ipaddr = $array[3];
                } else {
                    $ipaddr = $array[0];
                }

                if (!in_array($ipaddr, $unique)) {
                    $unique[] = $ipaddr;
                    $www_unique_count++;
                }
            }

            unset ($results);

            return count($unique);

        }

    }

    function server_memory_usage()
    {

        $free = shell_exec('free');
        $free = (string)trim($free);
        $free_arr = explode("\n", $free);
        $mem = explode(" ", $free_arr[1]);
        $mem = array_filter($mem);
        $mem = array_merge($mem);
        $memory_usage = $mem[2] / $mem[1] * 100;

        return $memory_usage;

    }

    function disk_usage()
    {

        $disktotal = disk_total_space('/');
        $diskfree = disk_free_space('/');
        $diskuse = round(100 - (($diskfree / $disktotal) * 100)).'%';

        return $diskuse;

    }

    function kernel_version()
    {

        $kernel = explode(' ', file_get_contents('/proc/version'));
        $kernel = $kernel[2];

        return $kernel;

    }

    function number_processes()
    {

        $proc_count = 0;
        $dh = opendir('/proc');

        while ($dir = readdir($dh)) {
            if (is_dir('/proc/'.$dir)) {
                if (preg_match('/^[0-9]+$/', $dir)) {
                    $proc_count++;
                }
            }
        }

        return $proc_count;

    }

    function memory_usage()
    {

        $mem = memory_get_usage(true);

        if ($mem < 1024) {

            $memory = $mem.' B';

        } elseif ($mem < 1048576) {

            $memory = round($mem / 1024, 2).' KB';

        } else {

            $memory = round($mem / 1048576, 2).' MB';

        }

        return $memory;

    }


}