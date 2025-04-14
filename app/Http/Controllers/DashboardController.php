<?php

namespace App\Http\Controllers;

use App\Communication;
use App\Http\Controllers\Api\ThermostatController;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Thermostat;

use Monolog\Logger;
use Monolog\Handler\BrowserConsoleHandler;
use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use App\Http\Controllers\Socket\ApiSocketController;



class DashboardController extends Controller
{

    public $apiScoket;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ApiSocketController $apiSocketController)
    {
        $this->apiScoket = $apiSocketController;
    }

    public function index()
    {
        $communication_logs = Communication::limit(500)->orderBy('id', 'desc')->get();
        return view('dashboard', ['communication_logs' => $communication_logs]);
    }


    public function thermostats()
    {
        $all_thermostats = Thermostat::all();
        return view('thermostats', ['all_thermostats' => $all_thermostats]);
    }

    public function thermostat_id($id)
    {
        $thermostat = Thermostat::find($id);
        $property = $thermostat->properties->first();
        return view('thermostat-id', ['thermostat' => $thermostat, 'property' => $property['id']]);
    }



    public function test()
    {
        Communication::create(
            [
                'address' => "192.168.100.11",
                'data' => "ok",
                'bytes' => 15,
                'message' => 'Packet received Ok',
            ]
        );
        echo "Ok";
        exit;
    }


    public function testLogs(Request $request)
    {
        $contents = $request->getContent();
        $datenow = Carbon::now();
        $data = str_replace(' ', '_', $datenow->toDateTimeString());
        $filename = $data.'_log.txt';
        Storage::disk('local')->put($filename, $contents);

    }
    public function getSetTemp(Request $request)
    {
        $thermostat = Thermostat::where('therm_mac_address', '38:2b:78:05:5f:6d')->first();
        $data = ["set_temp" => $thermostat->set_temp, "room_temp" => $thermostat->room_temp, "id" => $thermostat->id];
        return view('test')->with($data);
    }

    public function getSetTempAjax()
    {
        $thermostat = Thermostat::where('therm_mac_address', '38:2b:78:05:5f:6d')->first();
        $data = ["set_temp" => $thermostat->set_temp, "room_temp" => $thermostat->room_temp];
        return response()->json($data, 200);
    }

    public function pusher()
    {
        return view('pusher');
    }

    public function listen()
    {
        $thermostat = Thermostat::first();
        $proxy = Request::create(
            '/log',
            'post',
            ["thermostat" => $thermostat]
        );
        global $app;
        return $app->dispatch($proxy);

    }

    public function receive(Request $request)
    {
        return response()->json($request, 200);
    }

    public function weather()
    {




        $response = Curl::to('https://api.openweathermap.org/data/2.5/weather')
            ->withData([
                'appid' => env('WEATHER_KEY'),
                'q' => 'Skopje',
                'units' => 'metric'
            ])
            ->asJson()
            ->get();


        dd( $response);
    }


    public function scheduler_test()
    {

        return $this->setScheduler(31, 4);
    }

    public function setScheduler($thermostat_id, $user_id)
    {


        $scheduler = [];
        for ($i = 0; $i < 7; $i++) {
            if ($i != 0 && $i != 6) {

                $day = [
                            [
                                'day' => $i,
                                'thermostat_id' => $thermostat_id,
                                'start_hour' => 16,
                                'start_minute' => 26,
                                'command_name' => 'sched_temp',
                                'command_value' => 23,
                                'user_id' => $user_id,
                            ],
                            [
                                'day' => $i,
                                'thermostat_id' => $thermostat_id,
                                'start_hour' => 16,
                                'start_minute' => 28,
                                'command_name' => 'sched_temp',
                                'command_value' => 17,
                                'user_id' => $user_id,
                            ],
                            [
                                'day' => $i,
                                'thermostat_id' => $thermostat_id,
                                'start_hour' => 16,
                                'start_minute' => 30,
                                'command_name' => 'sched_temp',
                                'command_value' => 23,
                                'user_id' => $user_id,
                            ],
                            [
                                'day' => $i,
                                'thermostat_id' => $thermostat_id,
                                'start_hour' => 16,
                                'start_minute' => 30,
                                'command_name' => 'sched_temp',
                                'command_value' => 18,
                                'user_id' => $user_id,
                            ]
                     ];


                $scheduler[] = $day;


            }
            else {
                $weekend = [
                    [
                        'day' => $i,
                        'thermostat_id' => $thermostat_id,
                        'start_hour' => 7,
                        'start_minute' => 30,
                        'command_name' => 'sched_temp',
                        'command_value' => 23,
                        'user_id' => $user_id,
                    ],
                    [
                        'day' => $i,
                        'thermostat_id' => $thermostat_id,
                        'start_hour' => 12,
                        'start_minute' => 30,
                        'command_name' => 'sched_temp',
                        'command_value' => 17,
                        'user_id' => $user_id,
                    ],
                    [
                        'day' => $i,
                        'thermostat_id' => $thermostat_id,
                        'start_hour' => 18,
                        'start_minute' => 00,
                        'command_name' => 'sched_temp',
                        'command_value' => 23,
                        'user_id' => $user_id,
                    ],
                    [
                        'day' => $i,
                        'thermostat_id' => $thermostat_id,
                        'start_hour' => 23,
                        'start_minute' => 00,
                        'command_name' => 'sched_temp',
                        'command_value' => 18,
                        'user_id' => $user_id,
                    ]
                ];


               $scheduler[] = $weekend;




            }


        }

        $scheduler = array_reduce($scheduler, 'array_merge', array());



        $t = new ThermostatController($this->apiScoket);
        $request = new Request($scheduler);
        return $t->scheduler($request , $thermostat_id, $user_id);



        // return response()->json($scheduler);
    }


}
