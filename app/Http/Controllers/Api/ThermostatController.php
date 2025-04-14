<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ThermostatTypeResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Thermostat;
use App\Models\Commands;
use App\Models\CommandScheduler;
use App\Models\FirstSetUpThermostat;
use App\Models\ThermostatType;
use App\Models\Properties;
use App\Http\Resources\ThermostatResource as ThermostatResource;

use App\Http\Controllers\Socket\ApiSocketController;
use Auth;
use App\Jobs\ProcessCommands;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\SchedulerCommand;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Illuminate\Support\Facades\Artisan;


class ThermostatController extends Controller
{

    public $error;

    public $start_time;

    public $checkLast;

    public function __construct(ApiSocketController $socketC)
    {

        $this->middleware('auth:api');
        $this->error = [
            'status' => 'ERROR',
            'error' => '404 not found',
        ];
        $this->socketC = $socketC;
        $this->start_time = null;
        $this->checkLast = 0;
    }

    /**
     * @SWG\Get(
     *   path="/api/thermostats",
     *   summary="Get Thermostats for Auth User",
     *   tags={"Thermostats"},
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

    public function index()
    {
        $user = \Auth::user();
        $thermostats = $user
            ->where('id', $user->id)
            ->with('thermostats')->get();

        return response()->json($thermostats, 200);
    }

    /**
     * @SWG\Get(
     *   path="/api/all-thermostats",
     *   summary="Get All Thermostats",
     *   tags={"Thermostats"},
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

    public function thermostats()
    {
        $thermostats = ThermostatResource::collection(Thermostat::all());

        return response()->json($thermostats, 200);
    }

    /**
     * @SWG\Get(
     *   path="/api/thermostats/{id}",
     *   summary="Get Specific Thermostat",
     *   tags={"Thermostats"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     type="number"
     *   ),
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

    public function view($id)
    {
        $thermostat = Thermostat::find($id);

        if (!$thermostat) {
            return response()->json($this->error, 404);
        }

        $thermostat = new ThermostatResource($thermostat);

        return response()->json($thermostat, 200);
    }


    /**
     * @SWG\Post(
     *   path="/api/thermostats",
     *   summary="Store thermostat",
     *   tags={"Thermostats"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="room_name",
     *     in="formData",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="set_temp",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="22",
     *     minimum= 0,
     *     maximum= 50,
     *     description= "Allow numbers between 0 and 50"
     *   ),
     *   @SWG\Parameter(
     *     name="sched_temp",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="0",
     *     minimum= 0,
     *     maximum= 50,
     *     description= "Allow numbers between 0 and 50"
     *   ),
     *   @SWG\Parameter(
     *     name="max_temp",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="50",
     *     minimum= 22,
     *     maximum= 50,
     *     description= "Allow numbers between 22 and 50"
     *   ),
     *   @SWG\Parameter(
     *     name="min_temp",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="5",
     *     minimum= 5,
     *     maximum= 21,
     *     description= "Allow numbers between 5 and 21"
     *   ),
     *   @SWG\Parameter(
     *     name="offset_sign",
     *     in="formData",
     *   @SWG\Parameter(
     *     name="offset_temp",
     *     required=false,
     *     type="boolean",
     *     default=false,
     *     description= "Allow true or false"
     *   ),
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="0",
     *     minimum= 0,
     *     maximum= 50,
     *     description= "Allow numbers between 0 and 50"
     *   ),
     *   @SWG\Parameter(
     *     name="temp_limiter",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="40",
     *     minimum= 0,
     *     maximum= 50,
     *     description= "Allow numbers between 0 and 50"
     *   ),
     *   @SWG\Parameter(
     *     name="mode",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="0",
     *     minimum= 0,
     *     maximum= 4,
     *     description= "Allow numbers between 0 and 4"
     *   ),
     *   @SWG\Parameter(
     *     name="sensors_mode",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="0",
     *     minimum= 0,
     *     maximum= 3,
     *     description= "Allow numbers between 0 and 3"
     *   ),
     *   @SWG\Parameter(
     *     name="temp_measurement",
     *     in="formData",
     *     required=false,
     *     type="boolean",
     *     default=false,
     *     description= "Allow true or false"
     *   ),
     *   @SWG\Parameter(
     *     name="relay_opera",
     *     in="formData",
     *     required=false,
     *     type="boolean",
     *     default=false,
     *     description= "Allow true or false"
     *   ),
     *   @SWG\Parameter(
     *     name="relay_limit",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="6",
     *     minimum= 0,
     *     maximum= 10,
     *     description= "Allow numbers between 0 and 10"
     *   ),
     *   @SWG\Parameter(
     *     name="sensitivity",
     *     in="formData",
     *     required=false,
     *     type="number",
     *     format="float",
     *     default="1.5",
     *     minimum= 0,
     *     maximum= 3,
     *     description= "Allow numbers between 0 and 3"
     *   ),
     *   @SWG\Parameter(
     *     name="differential",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="0",
     *     minimum= 0,
     *     maximum= 3,
     *     description= "Allow numbers between 0 and 3"
     *   ),
     *   @SWG\Parameter(
     *     name="cool_heat_mode",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="0",
     *     minimum= 0,
     *     maximum= 2,
     *     description= "Allow numbers between 0 and 2"
     *   ),
     *   @SWG\Parameter(
     *     name="boiler_duration",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="30",
     *     minimum= 30,
     *     maximum= 210,
     *     description= "Allow numbers between 30 and 210"
     *   ),
     *   @SWG\Parameter(
     *     name="home_router_mac_address",
     *     in="formData",
     *     required=false,
     *     type="string",
     *     default="00:00:00:00:00:00",
     *   ),
     *   @SWG\Parameter(
     *     name="bathroom_on_low_heat",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="20",
     *     minimum= 1,
     *     maximum= 30,
     *     description= "Allow numbers between 1 and 30"
     *   ),
     *   @SWG\Parameter(
     *     name="bathroom_delay_stby_low_heat",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="20",
     *     minimum= 1,
     *     maximum= 30,
     *     description= "Allow numbers between 1 and 30"
     *   ),
     *   @SWG\Parameter(
     *     name="bathroom_on_med_heat",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="20",
     *     minimum= 1,
     *     maximum= 30,
     *     description= "Allow numbers between 1 and 30"
     *   ),
     *   @SWG\Parameter(
     *     name="bathroom_delay_stby_med_heat",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="15",
     *     minimum= 1,
     *     maximum= 30,
     *     description= "Allow numbers between 1 and 30"
     *   ),
     *   @SWG\Parameter(
     *     name="bathroom_on_high_heat",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="20",
     *     minimum= 1,
     *     maximum= 30,
     *     description= "Allow numbers between 1 and 30"
     *   ),
     *   @SWG\Parameter(
     *     name="bathroom_delay_stby_high_heat",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="10",
     *     minimum= 1,
     *     maximum= 30,
     *     description= "Allow numbers between 1 and 30"
     *   ),
     *   @SWG\Parameter(
     *     name="cool_room_check",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="60",
     *     minimum= 0,
     *     maximum= 120,
     *     description= "Allow numbers between 0 and 120"
     *   ),
     *   @SWG\Parameter(
     *     name="boost",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="180",
     *     minimum= 0,
     *     maximum= 240,
     *     description= "Allow numbers between 0 and 240"
     *   ),
     *   @SWG\Parameter(
     *     name="ligth_intensity",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="1",
     *     minimum= 0,
     *     maximum= 2,
     *     description= "Allow numbers between 0 and 2"
     *   ),
     *   @SWG\Parameter(
     *     name="enab_vibration",
     *     in="formData",
     *     required=false,
     *     type="boolean",
     *     default=true,
     *     description= "Allow true or false"
     *   ),
     *   @SWG\Parameter(
     *     name="enab_matrix",
     *     in="formData",
     *     required=false,
     *     type="boolean",
     *     default=true,
     *     description= "Allow true or false"
     *   ),
     *   @SWG\Parameter(
     *     name="enab_hart_beep_led",
     *     in="formData",
     *     required=false,
     *     type="boolean",
     *     default=true,
     *     description= "Allow true or false"
     *   ),
     *   @SWG\Parameter(
     *     name="enab_lock",
     *     in="formData",
     *     required=false,
     *     type="boolean",
     *     default=true,
     *     description= "Allow true or false"
     *   ),
     *   @SWG\Parameter(
     *     name="last_operation",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="0",
     *     minimum= 0,
     *     maximum= 15,
     *     description= "Allow numbers between 0 and 15"
     *   ),
     *   @SWG\Parameter(
     *     name="last_operation_value",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="0",
     *     minimum= 0,
     *     maximum= 50,
     *     description= "Allow numbers between 0 and 50"
     *   ),
     *   @SWG\Parameter(
     *     name="restore_default",
     *     in="formData",
     *     required=false,
     *     type="boolean",
     *     default=true,
     *     description= "Allow true or false"
     *   ),
     *   @SWG\Parameter(
     *     name="encription_code",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="0",
     *     minimum= 0,
     *     maximum= 999,
     *     description= "Allow numbers between 0 and 999"
     *   ),
     *   @SWG\Parameter(
     *     name="room_area",
     *     in="formData",
     *     required=false,
     *     type="number",
     *     format="float",
     *     description= "Room area with format 00.000"
     *   ),
     *   @SWG\Parameter(
     *     name="mat_power",
     *     in="formData",
     *     required=false,
     *     type="number",
     *     format="float",
     *     description= "Calculate device power with format 00.000"
     *   ),
     *   @SWG\Parameter(
     *     name="thermostat_type_id",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="temp_limitation",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *   ),
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

    public function store(Request $request)
    {

        $user = \Auth::user();

        $roles = [
            'room_name' => 'required|string|max:255',
            'room_temp' => 'integer|min:0|max:50',
            'floor_temp' => 'integer|min:0|max:50',
            'comp_temp' => 'integer|min:0|max:50',
            'target_temp' => 'integer|min:0|max:50',
            'relay_status' => 'boolean',
            'signal_strength' => 'integer',
            'set_temp' => 'integer|min:0|max:50',
            'sched_temp' => 'integer|min:0|max:50',
            'max_temp' => 'integer|min:22|max:50',
            'min_temp' => 'integer|min:5|max:21',
            'offset_sign' => 'boolean',
            'offset_temp' => 'integer|min:0|max:50',
            'temp_limiter' => 'integer|min:0|max:50',
            'mode' => 'integer|min:0|max:4',
            'sensors_mode' => 'integer|min:0|max:6',
            'temp_measurement' => 'boolean',
            'relay_opera' => 'boolean',
            'relay_limit' => 'integer|min:0|max:10',
            'sensitivity' => 'nullable|min:0|regex:/^\d*(\.\d{2})?$/',
            'differential' => 'integer|min:0|max:3',
            'cool_heat_mode' => 'integer|min:0|max:2',
            'boiler_duration' => 'integer|min:30|max:210',
            'home_router_mac_address' => 'string|max:30',
            'bathroom_on_low_heat' => 'integer|min:1|max:30',
            'bathroom_delay_stby_low_heat' => 'integer|min:1|max:30',
            'bathroom_on_med_heat' => 'integer|min:1|max:30',
            'bathroom_delay_stby_med_heat' => 'integer|min:1|max:30',
            'bathroom_on_high_heat' => 'integer|min:1|max:30',
            'bathroom_delay_stby_high_heat' => 'integer|min:1|max:30',
            'cool_room_check' => 'integer|min:0|max:120',
            'boost' => 'integer|min:0|max:240',
            'ligth_intensity' => 'integer|min:0|max:2',
            'enab_vibration' => 'boolean',
            'enab_matrix' => 'boolean',
            'enab_hart_beep_led' => 'boolean',
            'enab_lock' => 'boolean',
            'last_command' => 'integer|min:0|max:2',
            'last_operation' => 'integer|min:0|max:15',
            'last_operation_value' => 'integer|min:0|max:50',
            'restore_default' => 'boolean',
            'encription_code' => 'integer|min:0|max:999',
            'indor_sensor' => 'boolean',
            'floor_sensor' => 'boolean',
            'commu_status' => 'boolean',
            'cool_heat' => 'boolean',
            'fan_speed' => 'integer|min:0|max:3',
            'room_area' => 'number',
            'mat_power' => 'number',
            'thermostat_type_id' => 'integer',
            'temp_limitation' => 'integer',
        ];

        $v = validator($request->all(), $roles);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = $request->all();

        $thermostat = new Thermostat();
        $thermostat->room_name = $data['room_name'];
        (isset($data['room_temp'])) ?
            $thermostat->room_temp = $data['room_temp'] : null;
        (isset($data['floor_temp'])) ?
            $thermostat->floor_temp = $data['floor_temp'] : null;
        (isset($data['comp_temp'])) ?
            $thermostat->comp_temp = $data['comp_temp'] : null;
        (isset($data['target_temp'])) ?
            $thermostat->target_temp = $data['target_temp'] : null;
        (isset($data['relay_status'])) ?
            $thermostat->relay_status = $data['relay_status'] : null;
        (isset($data['signal_strength'])) ?
            $thermostat->signal_strength = $data['signal_strength'] : null;
        (isset($data['set_temp'])) ? $thermostat->set_temp = $data['set_temp']
            : null;
        (isset($data['sched_temp'])) ?
            $thermostat->sched_temp = $data['sched_temp'] : null;
        (isset($data['max_temp'])) ? $thermostat->max_temp = $data['max_temp']
            : null;
        (isset($data['min_temp'])) ? $thermostat->min_temp = $data['min_temp']
            : null;
        (isset($data['offset_sign'])) ?
            $thermostat->offset_sign = $data['offset_sign'] : null;
        (isset($data['offset_temp'])) ?
            $thermostat->offset_temp = $data['offset_temp'] : null;
        (isset($data['temp_limiter'])) ?
            $thermostat->temp_limiter = $data['temp_limiter'] : null;
        (isset($data['mode'])) ? $thermostat->mode = $data['mode'] : null;
        (isset($data['sensors_mode'])) ?
            $thermostat->sensors_mode = $data['sensors_mode'] : null;
        (isset($data['temp_measurement'])) ?
            $thermostat->temp_measurement = $data['temp_measurement'] : null;
        (isset($data['relay_opera'])) ?
            $thermostat->relay_opera = $data['relay_opera'] : null;
        (isset($data['relay_limit'])) ?
            $thermostat->relay_limit = $data['relay_limit'] : null;
        (isset($data['sensitivity'])) ?
            $thermostat->sensitivity = $data['sensitivity'] : null;
        (isset($data['differential'])) ?
            $thermostat->differential = $data['differential'] : null;
        (isset($data['cool_heat_mode'])) ?
            $thermostat->cool_heat_mode = $data['cool_heat_mode'] : null;
        (isset($data['boiler_duration'])) ?
            $thermostat->boiler_duration = $data['boiler_duration'] : null;
        (isset($data['bathroom_on_low_heat'])) ?
            $thermostat->bathroom_on_low_heat = $data['bathroom_on_low_heat']
            : null;
        (isset($data['bathroom_delay_stby_low_heat'])) ?
            $thermostat->bathroom_delay_stby_low_heat
                = $data['bathroom_delay_stby_low_heat'] : null;
        (isset($data['bathroom_on_med_heat'])) ?
            $thermostat->bathroom_on_med_heat = $data['bathroom_on_med_heat']
            : null;
        (isset($data['bathroom_delay_stby_med_heat'])) ?
            $thermostat->bathroom_delay_stby_med_heat
                = $data['bathroom_delay_stby_med_heat'] : null;
        (isset($data['bathroom_on_high_heat'])) ?
            $thermostat->bathroom_on_high_heat = $data['bathroom_on_high_heat']
            : null;
        (isset($data['bathroom_delay_stby_high_heat'])) ?
            $thermostat->bathroom_delay_stby_high_heat
                = $data['bathroom_delay_stby_high_heat'] : null;
        (isset($data['cool_room_check'])) ?
            $thermostat->cool_room_check = $data['cool_room_check'] : null;
        (isset($data['boost'])) ? $thermostat->boost = $data['boost'] : null;
        (isset($data['ligth_intensity'])) ?
            $thermostat->ligth_intensity = $data['ligth_intensity'] : null;
        (isset($data['enab_vibration'])) ?
            $thermostat->enab_vibration = $data['enab_vibration'] : null;
        (isset($data['enab_matrix'])) ?
            $thermostat->enab_matrix = $data['enab_matrix'] : null;
        (isset($data['enab_hart_beep_led'])) ?
            $thermostat->enab_hart_beep_led = $data['enab_hart_beep_led']
            : null;
        (isset($data['enab_lock'])) ?
            $thermostat->enab_lock = $data['enab_lock'] : null;
        (isset($data['last_command'])) ?
            $thermostat->last_command = $data['last_command'] : null;
        (isset($data['last_operation'])) ?
            $thermostat->last_operation = $data['last_operation'] : null;
        (isset($data['last_operation_value'])) ?
            $thermostat->last_operation_value = $data['last_operation_value']
            : null;
        (isset($data['restore_default'])) ?
            $thermostat->restore_default = $data['restore_default'] : null;
        (isset($data['encription_code'])) ?
            $thermostat->encription_code = $data['encription_code'] : null;
        (isset($data['indor_sensor'])) ?
            $thermostat->indor_sensor = $data['indor_sensor'] : null;
        (isset($data['floor_sensor'])) ?
            $thermostat->floor_sensor = $data['floor_sensor'] : null;
        (isset($data['commu_status'])) ?
            $thermostat->commu_status = $data['commu_status'] : null;
        (isset($data['cool_heat'])) ?
            $thermostat->cool_heat = $data['cool_heat'] : null;
        (isset($data['fan_speed'])) ?
            $thermostat->fan_speed = $data['fan_speed'] : null;
        (isset($data['room_area'])) ?
            $thermostat->room_area = $data['room_area'] : null;
        (isset($data['mat_power'])) ?
            $thermostat->mat_power = $data['mat_power'] : null;
        (isset($data['thermostat_type_id'])) ?
            $thermostat->thermostat_type_id = $data['thermostat_type_id']
            : null;
        (isset($data['temp_limitation'])) ?
            $thermostat->temp_limitation = $data['temp_limitation'] : null;

        //Set previous state
        (isset($data['mode'])) ? $thermostat->previous_state = $data['mode']
            : null;

        $thermostat->save();
        $thermostat->users()->attach($user->id);
        $thermostat = new ThermostatResource($thermostat);

        return response()->json($thermostat, 200);
    }

    /**
     * @SWG\Put(
     *   path="/api/thermostats/{id}",
     *   summary="Update thermostat",
     *   tags={"Thermostats"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     type="number"
     *   ),
     *   @SWG\Parameter(
     *     name="room_name",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="set_temp",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="22",
     *     minimum= 0,
     *     maximum= 50,
     *     description= "Allow numbers between 0 and 50"
     *   ),
     *   @SWG\Parameter(
     *     name="sched_temp",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="0",
     *     minimum= 0,
     *     maximum= 50,
     *     description= "Allow numbers between 0 and 50"
     *   ),
     *   @SWG\Parameter(
     *     name="max_temp",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="50",
     *     minimum= 22,
     *     maximum= 50,
     *     description= "Allow numbers between 22 and 50"
     *   ),
     *   @SWG\Parameter(
     *     name="min_temp",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="5",
     *     minimum= 5,
     *     maximum= 21,
     *     description= "Allow numbers between 5 and 21"
     *   ),
     *   @SWG\Parameter(
     *     name="offset_sign",
     *     in="formData",
     *     required=false,
     *     type="boolean",
     *     default=false,
     *     description= "Allow true or false"
     *   ),
     *   @SWG\Parameter(
     *     name="offset_temp",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="0",
     *     minimum= 0,
     *     maximum= 50,
     *     description= "Allow numbers between 0 and 50"
     *   ),
     *   @SWG\Parameter(
     *     name="temp_limiter",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="40",
     *     minimum= 0,
     *     maximum= 50,
     *     description= "Allow numbers between 0 and 50"
     *   ),
     *   @SWG\Parameter(
     *     name="mode",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="0",
     *     minimum= 0,
     *     maximum= 4,
     *     description= "Allow numbers between 0 and 4"
     *   ),
     *   @SWG\Parameter(
     *     name="sensors_mode",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="0",
     *     minimum= 0,
     *     maximum= 3,
     *     description= "Allow numbers between 0 and 3"
     *   ),
     *   @SWG\Parameter(
     *     name="temp_measurement",
     *     in="formData",
     *     required=false,
     *     type="boolean",
     *     default=false,
     *     description= "Allow true or false"
     *   ),
     *   @SWG\Parameter(
     *     name="relay_opera",
     *     in="formData",
     *     required=false,
     *     type="boolean",
     *     default=false,
     *     description= "Allow true or false"
     *   ),
     *   @SWG\Parameter(
     *     name="relay_limit",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="6",
     *     minimum= 0,
     *     maximum= 10,
     *     description= "Allow numbers between 0 and 10"
     *   ),
     *   @SWG\Parameter(
     *     name="sensitivity",
     *     in="formData",
     *     required=false,
     *     type="number",
     *     format="float",
     *     default="1.5",
     *     minimum= 0,
     *     maximum= 3,
     *     description= "Allow numbers between 0 and 3"
     *   ),
     *   @SWG\Parameter(
     *     name="differential",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="0",
     *     minimum= 0,
     *     maximum= 3,
     *     description= "Allow numbers between 0 and 3"
     *   ),
     *   @SWG\Parameter(
     *     name="cool_heat_mode",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="0",
     *     minimum= 0,
     *     maximum= 2,
     *     description= "Allow numbers between 0 and 2"
     *   ),
     *   @SWG\Parameter(
     *     name="boiler_duration",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="30",
     *     minimum= 30,
     *     maximum= 210,
     *     description= "Allow numbers between 30 and 210"
     *   ),
     *   @SWG\Parameter(
     *     name="home_router_mac_address",
     *     in="formData",
     *     required=false,
     *     type="string",
     *     default="00:00:00:00:00:00",
     *   ),
     *   @SWG\Parameter(
     *     name="bathroom_on_low_heat",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="20",
     *     minimum= 1,
     *     maximum= 30,
     *     description= "Allow numbers between 1 and 30"
     *   ),
     *   @SWG\Parameter(
     *     name="bathroom_delay_stby_low_heat",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="20",
     *     minimum= 1,
     *     maximum= 30,
     *     description= "Allow numbers between 1 and 30"
     *   ),
     *   @SWG\Parameter(
     *     name="bathroom_on_med_heat",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="20",
     *     minimum= 1,
     *     maximum= 30,
     *     description= "Allow numbers between 1 and 30"
     *   ),
     *   @SWG\Parameter(
     *     name="bathroom_delay_stby_med_heat",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="15",
     *     minimum= 1,
     *     maximum= 30,
     *     description= "Allow numbers between 1 and 30"
     *   ),
     *   @SWG\Parameter(
     *     name="bathroom_on_high_heat",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="20",
     *     minimum= 1,
     *     maximum= 30,
     *     description= "Allow numbers between 1 and 30"
     *   ),
     *   @SWG\Parameter(
     *     name="bathroom_delay_stby_high_heat",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="10",
     *     minimum= 1,
     *     maximum= 30,
     *     description= "Allow numbers between 1 and 30"
     *   ),
     *   @SWG\Parameter(
     *     name="cool_room_check",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="60",
     *     minimum= 0,
     *     maximum= 120,
     *     description= "Allow numbers between 0 and 120"
     *   ),
     *   @SWG\Parameter(
     *     name="boost",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="180",
     *     minimum= 0,
     *     maximum= 240,
     *     description= "Allow numbers between 0 and 240"
     *   ),
     *   @SWG\Parameter(
     *     name="ligth_intensity",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="1",
     *     minimum= 0,
     *     maximum= 2,
     *     description= "Allow numbers between 0 and 2"
     *   ),
     *   @SWG\Parameter(
     *     name="enab_vibration",
     *     in="formData",
     *     required=false,
     *     type="boolean",
     *     default=true,
     *     description= "Allow true or false"
     *   ),
     *   @SWG\Parameter(
     *     name="enab_matrix",
     *     in="formData",
     *     required=false,
     *     type="boolean",
     *     default=true,
     *     description= "Allow true or false"
     *   ),
     *   @SWG\Parameter(
     *     name="enab_hart_beep_led",
     *     in="formData",
     *     required=false,
     *     type="boolean",
     *     default=true,
     *     description= "Allow true or false"
     *   ),
     *   @SWG\Parameter(
     *     name="enab_lock",
     *     in="formData",
     *     required=false,
     *     type="boolean",
     *     default=true,
     *     description= "Allow true or false"
     *   ),
     *   @SWG\Parameter(
     *     name="last_operation",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="0",
     *     minimum= 0,
     *     maximum= 15,
     *     description= "Allow numbers between 0 and 15"
     *   ),
     *   @SWG\Parameter(
     *     name="last_operation_value",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="0",
     *     minimum= 0,
     *     maximum= 50,
     *     description= "Allow numbers between 0 and 50"
     *   ),
     *   @SWG\Parameter(
     *     name="restore_default",
     *     in="formData",
     *     required=false,
     *     type="boolean",
     *     default=true,
     *     description= "Allow true or false"
     *   ),
     *   @SWG\Parameter(
     *     name="encription_code",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *     default="0",
     *     minimum= 0,
     *     maximum= 999,
     *     description= "Allow numbers between 0 and 999"
     *   ),
     *   @SWG\Parameter(
     *     name="room_area",
     *     in="formData",
     *     required=false,
     *     type="number",
     *     format="float",
     *     description= "Room area with format 00.000"
     *   ),
     *   @SWG\Parameter(
     *     name="mat_power",
     *     in="formData",
     *     required=false,
     *     type="number",
     *     format="float",
     *     description= "Calculate device power with format 00.000"
     *   ),
     *   @SWG\Parameter(
     *     name="thermostat_type_id",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="temp_limitation",
     *     in="formData",
     *     required=false,
     *     type="integer",
     *   ),
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

    public function update(Request $request, $id)
    {
        $thermostat = Thermostat::find($id);

        if (!$thermostat) {
            return response()->json($this->error, 404);
        }

        $input = $request->all();
        $thermostat->fill($input)->save();

        $thermostat = new ThermostatResource($thermostat);

        return response()->json($thermostat, 200);
    }

    /**
     * @SWG\Delete(
     *   path="/api/thermostats/{id}",
     *   summary="Delete thermostat",
     *   tags={"Thermostats"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     type="number"
     *   ),
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

    public function delete($id)
    {

        $user_id = \Auth::user()->id;
        $thermostat = Thermostat::where('id', '=', $id)->first();
        $property = $thermostat->properties()->where('thermostat_id', '=', $thermostat->id)->first();

        if (!$thermostat) {
            return response()->json($this->error, 404);
        }

        if ($user_id === $property->user_id) {
            if ($thermostat->thermostat_type_id === 5) {
                $property->app_type = null;
                $property->save();
            }
            $thermostat->delete();
        } else {
            $error = [
                'status' => 'ERROR',
                'error' => '401 Unable to delete thermostat',
            ];

            return response()->json($error, 401);
        }

        return response()->json([], 200);
    }

    public function sentCommands(Request $request, $sleep = 0)
    {

        Log::error('Request ' . $request);
        $roles = [
            'id' => 'required|integer',
            'set_temp' => 'integer|min:0|max:50',
            'sched_temp' => 'integer|min:0|max:50',
            'max_temp' => 'integer|min:22|max:50',
            'min_temp' => 'integer|min:5|max:21',
            'offset_sign' => 'boolean|required_with:offset_temp',
            'offset_temp' => 'integer|min:0|max:50|required_with:offset_sign',
            'temp_limiter' => 'integer|min:0|max:50',
            'mode' => 'integer|min:0|max:4',
            'sensors_mode' => 'integer|min:0|max:6',
            'temp_measurement' => 'boolean',
            'relay_opera' => 'boolean',
            'relay_limit' => 'integer|min:0|max:10',
            'sensitivity' => 'nullable|min:0|regex:/^\d*(\.\d{2})?$/',
            'differential' => 'integer|min:0|max:3',
            'cool_heat_mode' => 'integer|min:0|max:2',
            'boiler_duration' => 'integer|min:30|max:210',
            'home_router_mac_address' => 'string|max:30',
            'bathroom_on_low_heat' => 'integer|min:1|max:30',
            'bathroom_delay_stby_low_heat' => 'integer|min:1|max:30',
            'bathroom_on_med_heat' => 'integer|min:1|max:30',
            'bathroom_delay_stby_med_heat' => 'integer|min:1|max:30',
            'bathroom_on_high_heat' => 'integer|min:1|max:30',
            'bathroom_delay_stby_high_heat' => 'integer|min:1|max:30',
            'cool_room_check' => 'integer|min:0|max:120',
            'boost' => 'integer|min:0|max:240',
            'ligth_intensity' => 'integer|min:0|max:2',
            'enab_vibration' => 'boolean',
            'enab_matrix' => 'boolean',
            'enab_hart_beep_led' => 'boolean',
            'enab_lock' => 'boolean',
            'last_operation' => 'integer|min:0|max:15',
            'last_operation_value' => 'integer|min:0|max:50',
            'restore_default' => 'boolean',
            'encription_code' => 'integer|min:0|max:999',
            'all' => 'boolean',
        ];

        $v = validator($request->all(), $roles);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }


        $all_request = $request->all();

        if ($request->has('user_id')) {

            $user_id = $all_request['user_id'];
        } else {
            $user_id = Auth::user()->id;
        }


        unset($all_request['id']);


        $registerData = $this->validateRegistersByRequest();
        $thermostat_id = $request->get('id');
        $thermostat = Thermostat::find($thermostat_id);
        $thermostat_id = $thermostat->id;

        if ($request->has('mode') && $request['mode'] === 2)
        {

            $thermostat->force_off = false;
            $thermostat->save();
        }

        $all_request = $request->all();

        $socket_id = null;
        if (isset($all_request['socket_id'])) {
            $socket_id = $all_request['socket_id'];
        }

        //If is send offset_sign and offset_temp special case
        if (isset($all_request['offset_sign']) || isset($all_request['offset_temp'])) {
            $offsets['offset_sign'] = $all_request['offset_sign'];
            $offsets['offset_temp'] = $all_request['offset_temp'];
            $this->transSaveCommand(14, $offsets, $thermostat_id, $user_id);
        }




        $log = new Logger('name');
        $log->pushHandler(new StreamHandler('multicommands.log', Logger::WARNING));




        $i = 0;
        if ($request->has('settings') && $request['settings'] === true) {

            // get property
            $property = $thermostat->properties->first();

            // all set true
            if ($request->has('all') && $request['all'] === true) {
                foreach ($property->thermostats as $thermostat) {
                    $this->transSaveCommands($all_request, $thermostat->id, $user_id);
                }
            } // for one
            else {
                $this->transSaveCommands($all_request, $thermostat->id, $user_id);
            }

        } else {
            sleep($sleep);


            $i = 0;

            unset($all_request['id']);
            unset($all_request['socket_id']);
            unset($all_request['map']);
            krsort($all_request);
            $log->warning(json_encode($all_request));


            if(array_key_exists("set_temp", $all_request) && array_key_exists("mode", $all_request))
            {
                $this->transSaveCommand($registerData['set_temp'], $all_request['set_temp'], $thermostat_id, $user_id, 0, $socket_id);
                $this->transSaveCommand($registerData['mode'], $all_request['mode'], $thermostat_id, $user_id, 1, $socket_id);
                $this->transSaveCommand($registerData['mode'], $all_request['mode'], $thermostat_id, $user_id, 2, $socket_id);

                unset($all_request['mode']);
                unset($all_request['set_temp']);

            }

            foreach ($all_request as $key => $requ_val) {
                if (array_key_exists($key, $registerData)) {
                    $this->transSaveCommand($registerData[$key], $requ_val, $thermostat_id, $user_id, $i, $socket_id);
                    $log->warning($key. ' '. $requ_val);

                }
                $i = $i + 2;

            }
        }

        return response()->json([], 200);
        // $checkCommand = $this->socketC->checkLastCommand($thermostat->id, 'boiler_duration', $all_request['boiler_duration']);


    }

    public function validateRegistersByRequest()
    {
        $registerDataArray = [
            'room_temp' => 0,
            'floor_temp' => 1,
            'comp_temp' => 2,
            'target_temp' => 3,
            'relay_status' => 4,
            'signal_strength' => 5,
            'set_temp' => 10,
            'sched_temp' => 11,
            'max_temp' => 12,
            'min_temp' => 13,
            'temp_limiter' => 15,
            'mode' => 20,
            'sensors_mode' => 21,
            'temp_measurement' => 22,
            'relay_opera' => 23,
            'relay_limit' => 24,
            'sensitivity' => 25,
            'differential' => 26,
            'cool_heat_mode' => 27,
            'boiler_duration' => 28,
            'home_router_mac_address' => 29,
            'bathroom_on_low_heat' => 30,
            'bathroom_delay_stby_low_heat' => 31,
            'bathroom_on_med_heat' => 32,
            'bathroom_delay_stby_med_heat' => 33,
            'bathroom_on_high_heat' => 34,
            'bathroom_delay_stby_high_heat' => 35,
            'cool_room_check' => 36,
            'boost' => 37,
            'ligth_intensity' => 38,
            'enab_vibration' => 39,
            'enab_matrix' => 40,
            'enab_hart_beep_led' => 41,
            'enab_lock' => 42,
            'last_command' => 50,
            'last_operation' => 51,
            'last_operation_value' => 52,
            'restore_default' => 53,
            'encription_code' => 54,
            'indor_sensor' => 100,
            'floor_sensor' => 101,
            'commu_status' => 102,
            'cool_heat' => 201,
            'fan_speed' => 202,
        ];

        return $registerDataArray;
    }

    public function transSaveCommand($send_register, $send_val, $thermostat_id, $user_id, $delay = false, $socket_id = null)
    {


        $registerData = $this->validateRegistersByRequest();
        $command_name = array_keys($registerData, $send_register);


        $tran_register = dechex($send_register);
        $tran_value = ($send_register == 14) ? $this->socketC->tranSpecRegister($send_val) : dechex($send_val);
        $prepared_pack = [
            'F1',
            'F2',
            'A1',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            'FE',
            'FF',
        ];
        $prepared_pack[3] = (strlen($tran_register) == 1) ? "0" . $tran_register
            : $tran_register;
        $prepared_pack[4] = (strlen($tran_value) == 1) ? "0" . $tran_value
            : $tran_value;
        $tran_checksum = $this->socketC->calcAddChecksum($prepared_pack);
        $prepared_pack[8] = $tran_checksum;

        $trim_command = str_replace(" ", "", implode(" ", $prepared_pack));


        $command = new Commands();
        $command->user_id = $user_id;
        $command->thermostat_id = $thermostat_id;
        $command->command = $trim_command;
        $command->command_name = $command_name[0];
        $command->command_value = $send_val;
        $command->socket_id = $socket_id;
        $command->save();

        if ($delay) {
            dispatch((new ProcessCommands($trim_command, $thermostat_id))->delay($delay));
        } else {
            dispatch((new ProcessCommands($trim_command, $thermostat_id))->onQueue('high'));
        }

    }


    public function sentAllCommands($thermostat_id)
    {
        $prepared_pack = [
            'F1',
            'F2',
            'B1',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            'FE',
            'FF',
        ];

        $tran_checksum = $this->socketC->calcAddChecksum($prepared_pack);
        $prepared_pack[8] = $tran_checksum;

        $trim_command = str_replace(" ", "", implode(" ", $prepared_pack));

        dispatch((new ProcessCommands($trim_command, $thermostat_id))->onQueue('high'));

    }


    public function checkDispatched($commandId)
    {

        sleep(5);

        $command = Commands::find($commandId);
        if ($command->executed === 0) {
            dispatch((new ProcessCommands($command->command, $command->thermostat_id))->onQueue('high'));
            return true;
        }
        return false;
    }

    /**
     * @param $all_request
     * @param $thermostat_id
     * @param $user_id
     */
    public function transSaveCommands($all_request, $thermostat_id, $user_id)
    {


        $dataArray = [

            'mode' => 3,
            'sensors_mode' => 4,
            'temp_measurement' => 5,
            'relay_opera' => 6,
            'relay_limit' => 7,
            'sensitivity' => 8,
            'differential' => 9,
            'cool_heat_mode' => 10,
            'boiler_duration' => 11,
            'home_router_mac_address' => 12,
            'bathroom_on_low_heat' => 13,
            'bathroom_delay_stby_low_heat' => 14,
            'bathroom_on_med_heat' => 15,
            'bathroom_delay_stby_med_heat' => 16,
            'bathroom_on_high_heat' => 17,
            'bathroom_delay_stby_high_heat' => 18,
            'cool_room_check' => 19,
            'boost' => 20,
            'ligth_intensity' => 21,
            'enab_vibration' => 22,
            'enab_matrix' => 23,
            'enab_hart_beep_led' => 24,
            'enab_lock' => 25,
        ];

        $prepared_pack = [
            'F1',
            'F2',
            '03',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            'FE',
            'FF',
        ];


        $thermostat = Thermostat::find($thermostat_id)->toArray();
        foreach ($thermostat as $key => $value) {
            if (array_key_exists($key, $dataArray)) {
                $sendData = dechex($value);
                $finalData = (strlen($sendData) == 1) ? "0" . $sendData : $sendData;
                $prepared_pack[$dataArray[$key]] = $finalData;

            }
        }


        // sensitivity intval($prepared_pack[8])
        $sensitivity = intval($prepared_pack[8]);
        $prepared_pack[8] = "0" . $sensitivity;


        foreach ($all_request as $key => $requ_val) {
            if (array_key_exists($key, $dataArray)) {
                $sendData = dechex($requ_val);
                $finalData = (strlen($sendData) == 1) ? "0" . $sendData : $sendData;
                $prepared_pack[$dataArray[$key]] = $finalData;
            }
        }


        $checkSum = $this->socketC->calcAddChecksum($prepared_pack);
        $checkSum = (strlen($checkSum) == 1) ? "0" . $checkSum : $checkSum;


        $prepared_pack[12] = "00"; // home router mac address
        $prepared_pack[26] = $checkSum;


        $trim_command = str_replace(" ", "", implode(" ", $prepared_pack));
        $command = new Commands();
        $command->user_id = $user_id;
        $command->thermostat_id = $thermostat_id;
        $command->command_name = "settings";
        $command->executed = 0;
        $command->command = $trim_command;

        $command->save();

        dispatch((new ProcessCommands($trim_command, $thermostat_id))->onQueue('high'));

       // $this->sentAllCommands($thermostat_id);

    }


    /**
     * @SWG\POST(
     *   path="/api/first-set-up",
     *   summary="First Set Up Thermostat",
     *   tags={"Thermostats"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="mac_address",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="property_id",
     *     in="formData",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="room_name",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="room_area",
     *     in="formData",
     *     required=true,
     *     type="number",
     *     format="float",
     *     description= "Room area with format 00.000"
     *   ),
     *   @SWG\Parameter(
     *     name="mat_power",
     *     in="formData",
     *     required=true,
     *     type="number",
     *     format="float",
     *     description= "Calculate device power with format 00.000"
     *   ),
     *   @SWG\Parameter(
     *     name="thermostat_type_id",
     *     in="formData",
     *     required=true,
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="temp_limitation",
     *     in="formData",
     *     required=true,
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="temp_measurement",
     *     in="formData",
     *     required=true,
     *     type="string",
     *     enum={"C", "F"},
     *     description= "Temperature unit"
     *   ),
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
    public function firstSetUpThermostat(Request $request)
    {
        $user = Auth::user();

        $log = new Logger('name');
        $log->pushHandler(new StreamHandler('firstSetup.log', Logger::WARNING));

        $log->warning($request);

        $v = validator($request->all(), [
            'mac_address' => 'required|string',
            'property_id' => 'required|integer',
            'room_name' => 'required|string|max:255',
            'room_area' => 'required|string',
            'mat_power' => 'required|string',
            'thermostat_type_id' => 'required|integer',
            'temp_limitation' => 'required|integer',
            'temp_measurement' => 'required|in:C,F',
            'square_measurement' => 'required|in:m,f',
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }


        $thermostat = Thermostat::where('therm_mac_address', '=', $request->input('mac_address'))->first();
        if ($thermostat) {

            $thermostat->delete();
        }


        try {
            $record = app()->geoip->getIp();
            $record = app()->geoip->getLocation($record);

            $cc = json_encode($record->location);
            $cc = json_decode($cc);
            $timezone = $cc->time_zone;

        } catch (\Exception $e) {
            $timezone = 'Asia/Jerusalem';
        }


        $first_set = new FirstSetUpThermostat();
        $first_set->mac_address = $request->input('mac_address');
        $first_set->property_id = $request->input('property_id');
        $first_set->room_name = $request->input('room_name');
        $first_set->room_area = $request->input('room_area');
        $first_set->mat_power = $request->input('mat_power');
        $first_set->thermostat_type_id = $request->input('thermostat_type_id');
        $first_set->temp_limitation = $request->input('temp_limitation');
        $first_set->temp_measurement = $request->input('temp_measurement');
        $first_set->user_id = $user->id;
        $first_set->lat = $request->input('lat');
        $first_set->lng = $request->input('lng');
        $first_set->square_measurement = $request->input('square_measurement');
        $first_set->time_zone = $timezone;

        $first_set->save();

        return response()->json([], 200);
    }


    /**
     * @SWG\Delete(
     *   path="/api/first-setup/delete/{mac_address}",
     *   summary="Delete First Setup",
     *   tags={"Thermostats"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="mac_address",
     *     in="path",
     *     required=true,
     *     type="string"
     *   ),
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

    public function deleteFirstSetup($mac_address)
    {
        $v = validator(["mac_address" => $mac_address], [
            'mac_address' => 'required|string',
        ]);


        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $thermostat = Thermostat::where('therm_mac_address', '=', $mac_address)->first();
        if ($thermostat) {

            $thermostat->delete();
        }

        $firstSetups = FirstSetUpThermostat::where('mac_address', '=', $mac_address)->get();
        foreach ($firstSetups as $firstSetup) {
            $firstSetup->delete();
        }

        return response()->json([], 200);
    }

    /**
     * @SWG\GET(
     *   path="/api/get-all-thermostat-types",
     *   summary="Get All Thermostat Types",
     *   tags={"Thermostats"},
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
    public function getAllThermostatType()
    {
        $ther_types = ThermostatType::all();
        $ther_types = ThermostatTypeResource::collection($ther_types);
        return response()->json(['ther_types' => $ther_types], 200);
    }

    /**
     * @SWG\GET(
     *   path="/api/thermostat/{mac_address}",
     *   summary="Get Thermostat By Mac Address",
     *   tags={"Thermostats"},
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

    public function getThermostatByMac($mac_address)
    {
        $thermostat = Thermostat::where('therm_mac_address', '=', $mac_address)
            ->first();


        if (!$thermostat) {
            return response()->json($this->error, 404);
        }

        $thermostat = new ThermostatResource($thermostat);


        return response()->json($thermostat, 200);

    }

    /**
     * @SWG\POST(
     *   path="/api/set-scheduler/{thermostat_id}",
     *   summary="Get Scheduler for Thermostat",
     *   tags={"Scheduler"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="thermostat_id",
     *     in="formData",
     *     required=true,
     *     type="integer",
     *     description= "Thermostat ID"
     *   ),
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
    public function scheduler(Request $request, $thermostat_id, $user_id = null)
    {

        CommandScheduler::where('thermostat_id', '=', $thermostat_id)->delete();



        $log = new Logger('name');
        $log->pushHandler(new StreamHandler('scheduler-info.log', Logger::WARNING));



        $requests = $request->all();

        $thermostat = Thermostat::where('id', '=', $thermostat_id)->first();


        if (count($request->all()) === 0) {

            if($thermostat->mode == 2 || $thermostat->mode === 3)
            {
                $modeOn = $this->makeModeCommand(20, 1, \Auth::user()->id, $thermostat_id);
                dispatch((new ProcessCommands($modeOn, $thermostat->id))->onQueue('high'));
            }
            $this->socketC->pusher($thermostat->id, false, true, false, true);
            $this->socketC->pusher($thermostat->id, false, true, false, true);
            return response()->json([], 200);
        }


        $data = [];

        $lastElement = count($request->toArray()) - 1;


        foreach ($requests as $index => $request) {



            $this->checkLast = $index;


            $roles = [
                'command_name' => 'required|string',
                'command_value' => 'required',
                'thermostat_id' => 'required|integer',
                'set_temp' => 'integer|min:0|max:50',
                'sched_temp' => 'integer|min:0|max:50',
                'max_temp' => 'integer|min:22|max:50',
                'min_temp' => 'integer|min:5|max:21',
                'offset_sign' => 'boolean|required_with:offset_temp',
                'offset_temp' => 'integer|min:0|max:50|required_with:offset_sign',
                'temp_limiter' => 'integer|min:0|max:50',
                'mode' => 'integer|min:0|max:4',
                'sensors_mode' => 'integer|min:0|max:6',
                'temp_measurement' => 'boolean',
                'relay_opera' => 'boolean',
                'relay_limit' => 'integer|min:0|max:10',
                'sensitivity' => 'nullable|min:0|regex:/^\d*(\.\d{2})?$/',
                'differential' => 'integer|min:0|max:3',
                'cool_heat_mode' => 'integer|min:0|max:2',
                'boiler_duration' => 'integer|min:30|max:210',
                'home_router_mac_address' => 'string|max:30',
                'bathroom_on_low_heat' => 'integer|min:1|max:30',
                'bathroom_delay_stby_low_heat' => 'integer|min:1|max:30',
                'bathroom_on_med_heat' => 'integer|min:1|max:30',
                'bathroom_delay_stby_med_heat' => 'integer|min:1|max:30',
                'bathroom_on_high_heat' => 'integer|min:1|max:30',
                'bathroom_delay_stby_high_heat' => 'integer|min:1|max:30',
                'cool_room_check' => 'integer|min:0|max:120',
                'boost' => 'integer|min:0|max:240',
                'ligth_intensity' => 'integer|min:0|max:2',
                'enab_vibration' => 'boolean',
                'enab_matrix' => 'boolean',
                'enab_hart_beep_led' => 'boolean',
                'enab_lock' => 'boolean',
                'last_operation' => 'integer|min:0|max:15',
                'last_operation_value' => 'integer|min:0|max:50',
                'restore_default' => 'boolean',
                'encription_code' => 'integer|min:0|max:999',
            ];

            $v = validator($request, $roles);

            if ($v->fails()) {
                return response()->json($v->errors()->all(), 400);
            }


            $registerData = $this->validateRegistersByRequest();
            $thermostat_id = $request['thermostat_id'];


            $start_hour = (string)$request['start_hour'];
            $start_hour = (strlen($start_hour) == 1) ? "0" . $start_hour : $start_hour;


            $start_minute = (string)$request['start_minute'];
            $start_minute = (strlen($start_minute) == 1) ? "0" . $start_minute : $start_minute;


            try {
                $record = app()->geoip->getIp();
                $record = app()->geoip->getLocation($record);

                $cc = json_encode($record->location);
                $cc = json_decode($cc);
                $timezone = $cc->time_zone;

            } catch (\Exception $e) {
                $timezone = 'Asia/Jerusalem';
            }


            $start_time = \Carbon\Carbon::createFromTime($start_hour, $start_minute, 0, $timezone);
            $start_time = $start_time->timezone('Europe/Skopje')->format('H:i');


            $end_hour = (string)$request['end_hour'];
            $end_hour = (strlen($end_hour) == 1) ? "0" . $end_hour : $end_hour;


            $end_minute = (string)$request['end_minute'];
            $end_minute = (strlen($end_minute) == 1) ? "0" . $end_minute : $end_minute;


            $end_time = \Carbon\Carbon::createFromTime($end_hour, $end_minute, 0, $timezone);

            $end_time = $end_time->timezone('Europe/Skopje')->format('H:i');


            $day = $request['day'];
            $endDay = $request['end_day'];

            $all_request = $request;

            $command_name = $request['command_name'];
            $command_value = $request['command_value'];

            $all_request[$command_name] = $request['command_value'];

            if ($user_id === null) {
                $user_id = \Auth::user()->id;
            }
            unset($all_request['id']);



            // check day of the week
            $carbon = new \Carbon\Carbon();
            $carbon->setTimezone('Europe/Skopje');
            $time_now = $carbon->now('Europe/Skopje');

            $dayOfTheWeek = $time_now->dayOfWeek;

            if($day == $dayOfTheWeek)
            {
                $log->warning($start_time. ' '. $time_now->toTimeString());
                if($start_time <= $time_now->toTimeString() && $end_time >=  $time_now->toTimeString())
                {

                    $thermostat->sched_temp = $command_value;
                    $thermostat->save();

                    if($thermostat->mode === 2 || $thermostat->mode === 3)
                    {
                        if($thermostat->thermostat_type_id != 6) {
                            $sched_temp = $this->makeModeCommand(11, $command_value, \Auth::user()->id, $thermostat->id);
                            $this->socketC->pusher($thermostat->id, false, true, false, true);
                            $this->socketC->pusher($thermostat->id, false, true, false, true);
                            dispatch((new ProcessCommands($sched_temp, $thermostat->id))->onQueue('high'));
                            $log->warning("Isprateno ". $sched_temp. '$command_value: '. $command_value);

                        } else {
                            $boiler_duration = $this->makeModeCommand(28, $command_value, \Auth::user()->id, $thermostat->id);
                            $log->warning("Isprateno ". $boiler_duration. ' $command_value: '. $command_value . ' $command_name: '. $command_name);
                            dispatch((new ProcessCommands($boiler_duration, $thermostat->id))->onQueue('high'));
                        }

                    }

                }
            }

            $commandScheduler = '';
            //If is send offset_sign and offset_temp special case
            if (isset($all_request['offset_sign']) || isset($all_request['offset_temp'])) {
                $offsets['offset_sign'] = $all_request['offset_sign'];
                $offsets['offset_temp'] = $all_request['offset_temp'];
                $commandScheduler = $this->transSaveCommandScheduler(14, $offsets, $thermostat_id, $user_id, $start_time, $end_time, $day, $endDay,
                    $command_name, $command_value, $timezone);
            }



            foreach ($all_request as $key => $requ_val) {

                if (array_key_exists($key, $registerData)) {

                    if ($this->start_time === null) {
                        //  $this->transSaveCommandScheduler(20, "0", $thermostat_id, $user_id, "00:00:00", $start_time, $day,  $endDay,"mode", "0", $timezone);
                        $this->start_time = $end_time;
                    } else {
                        //   $this->transSaveCommandScheduler(20, "0", $thermostat_id, $user_id, $this->start_time, $start_time, $day,  $endDay,"mode", "0", $timezone);
                        $this->start_time = $end_time;
                    }

                    $commandScheduler = $this->transSaveCommandScheduler($registerData[$key], $requ_val, $thermostat_id, $user_id,
                        $start_time, $end_time, $day, $endDay, $command_name, $command_value, $timezone);


                    if ($lastElement === $this->checkLast) {

                        // $this->transSaveCommandScheduler(20, "0", $thermostat_id, $user_id, $end_time, "23:59:59", $day,  $endDay,"mode", "0", $timezone);
                    }

                }
            }


            $starTime = explode(':', $commandScheduler->start_time);
            $endTime = explode(':', $commandScheduler->end_time);


            $data[$index] = [
                "start_hour" => $starTime[0],
                "start_minute" => $starTime[1],
                "end_hour" => $endTime[0],
                "end_minute" => $endTime[1],
                "user_id" => $commandScheduler->user_id,
                "thermostat_id" => $commandScheduler->thermostat_id,
                "command_name" => $commandScheduler->command_name,
                "command_value" => $commandScheduler->command_value,
                "day" => $commandScheduler->day,
                "end_day" => $endDay
            ];


        }


        $days = [0, 1, 2, 3, 4, 5, 6];
        $timezone = '';

        $emptyDays = $days;

        $commands = CommandScheduler::where('thermostat_id', '=', $thermostat_id)->get();
        // dd($commands->count());

        foreach ($days as $day) {


            if ($commands) {
                foreach ($commands as $command) {

                    $i = $command->day;


                    while ($i <= $command->end_day) {
                        unset($emptyDays[$i]);
                        $i++;
                    }


                    $timezone = $command->time_zone;

                    if ($command->day === $day) {
                        $this->transSaveCommandScheduler(20, "0", $thermostat_id, $user_id, "00:00:00", $command->start_time, $day, $command->day, "mode", "0", $timezone);
                    }

                    if ($command->end_day === $day) {
                        $this->transSaveCommandScheduler(20, "0", $thermostat_id, $user_id, $command->end_time, "23:59:59", $day, $command->end_day, "mode", "0", $timezone);
                    }


                }
            }

        }

        foreach ($emptyDays as $day) {
            try {
                $record = app()->geoip->getIp();
                $record = app()->geoip->getLocation($record);

                $cc = json_encode($record->location);
                $cc = json_decode($cc);
                $timezone = $cc->time_zone;

            } catch (\Exception $e) {
                $timezone = 'Asia/Jerusalem';
            }

            $this->transSaveCommandScheduler(20, "0", $thermostat_id, $user_id, "00:00:00", "23:59:59", $day, $day, "mode", "0", $timezone);
        }

        // check day of the week
        $carbon = new \Carbon\Carbon();
        $carbon->setTimezone('Europe/Skopje');
        $time_now = $carbon->now('Europe/Skopje');

        $dayOfTheWeek = $time_now->dayOfWeek;

        $checkCommandNow = CommandScheduler::where('thermostat_id', '=', $thermostat_id)
            ->where('day', '=', $dayOfTheWeek)
            ->where('command_name', '!=', 'mode')
            ->count();

        if($checkCommandNow === 0)
        {
            if($thermostat->mode == 2 || $thermostat->mode == 3) {

                $modeOn = $this->makeModeCommand(20, 1, \Auth::user()->id, $thermostat_id);
                dispatch((new ProcessCommands($modeOn, $thermostat->id))->onQueue('high'));

            }

        }

        $this->socketC->pusher($thermostat->id, false, true, false, true);
        $this->socketC->pusher($thermostat->id, false, true, false, true);

        return response()->json($data, 200);
    }

    public function transSaveCommandScheduler(
        $send_register,
        $send_val,
        $thermostat_id,
        $user_id,
        $start_time,
        $end_time,
        $day,
        $endDay,
        $command_name,
        $command_value,
        $time_zone
    )
    {
        $tran_register = dechex($send_register);
        $tran_value = ($send_register == 14)
            ? $this->socketC->tranSpecRegister($send_val) : dechex($send_val);
        $prepared_pack = [
            'F1',
            'F2',
            'A1',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            'FE',
            'FF',
        ];
        $prepared_pack[3] = (strlen($tran_register) == 1) ? "0" . $tran_register
            : $tran_register;
        $prepared_pack[4] = (strlen($tran_value) == 1) ? "0" . $tran_value
            : $tran_value;
        $tran_checksum = $this->socketC->calcAddChecksum($prepared_pack);
        $prepared_pack[8] = $tran_checksum;

        $trim_command = str_replace(" ", "", implode(" ", $prepared_pack));
        $commandScheduler = new CommandScheduler();
        $commandScheduler->user_id = $user_id;
        $commandScheduler->thermostat_id = $thermostat_id;
        $commandScheduler->command = $trim_command;
        $commandScheduler->start_time = $start_time;
        $commandScheduler->end_time = $end_time;
        $commandScheduler->day = $day;
        $commandScheduler->end_day = $endDay;
        $commandScheduler->command_name = $command_name;
        $commandScheduler->command_value = $command_value;
        $commandScheduler->time_zone = $time_zone;


        $commandScheduler->save();

        return $commandScheduler;


    }

    /**
     * @SWG\GET(
     *   path="/api/get-scheduler/{thermostat_id}",
     *   summary="Get Scheduler for Thermostat",
     *   tags={"Scheduler"},
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
    public function getScheduler($id)
    {
        $scheduler = CommandScheduler::where('thermostat_id', '=', $id)->where('command_name', '!=', 'mode')->get();
        $schedulers = SchedulerCommand::collection($scheduler);

        return response()->json($schedulers, 200);
    }

    /**
     * @SWG\POST(
     *   path="/api/set-all-thermostats-status",
     *   summary="Set all thermostats status",
     *   tags={"Properties"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="property_id",
     *     in="formData",
     *     required=true,
     *     type="integer",
     *     description= "Property ID"
     *   ),
     *   @SWG\Parameter(
     *     name="status_comm",
     *     in="formData",
     *     required=true,
     *     type="integer",
     *     description= "Command"
     *   ),
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
    public function setAllThermostatStatus(Request $request)
    {
        $roles = [
            'property_id' => 'required|integer',
            'status_comm' => 'required|integer|between:0,2',
        ];

        $v = validator($request->all(), $roles, [
            'status_comm.between' => 'In field :attribute allowed values are: 0, 1, 2',
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $property_id = $request->input('property_id');
        $status_comm = $request->input('status_comm');
        $all_thermostats = Properties::where('id', $property_id)
            ->with('thermostats')->first();

        $heatPump = $all_thermostats->thermostats()->where('thermostat_type_id', '=', 5)->first();


        foreach ($all_thermostats->thermostats as $therm) {
            if ($status_comm == 2) {

                if (!$heatPump) {
                    $this->transSaveCommandMode(20, $status_comm, $therm->id, $all_thermostats->user_id, $therm->previous_state);
                }

            } else {
                if ($status_comm === 1) {

                    $therm->force_off = false;
                    $therm->save();

                    if ($heatPump) {
                        if ($heatPump->mode === 1) {
                            $this->transSaveCommandMode(20, 1, $therm->id, $all_thermostats->user_id, false);
                        } else {
                            $therm->force_off = true;
                            $therm->save();
                        }
                    } else {
                        $this->transSaveCommandMode(20, 1, $therm->id, $all_thermostats->user_id, false);
                    }


                } else {
                    if ($status_comm === 0) {
                        if ($therm->thermostat_type_id != 5) {
                            $this->transSaveCommandMode(20, 0, $therm->id, $all_thermostats->user_id, false);
                            $therm->force_off = true;
                            $therm->save();
                        }
                    } else {
                        $this->transSaveCommandMode(20, $status_comm, $therm->id, $all_thermostats->user_id, false);
                    }
                }
            }
        }

        return response()->json($all_thermostats, 200);
    }

    public function transSaveCommandMode(
        $send_register,
        $send_val,
        $thermostat_id,
        $user_id,
        $previous_state
    )
    {


        $registerData = $this->validateRegistersByRequest();
        $command_name = array_keys($registerData, $send_register);


        $tran_register = dechex($send_register);
        $prepared_pack = [
            'F1',
            'F2',
            'A1',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            'FE',
            'FF',
        ];
        $prepared_pack[3] = (strlen($tran_register) == 1) ? "0" . $tran_register
            : $tran_register;

        if ($previous_state) {
            $tran_value = dechex($previous_state);
        } else {
            $tran_value = dechex($send_val);
        }
        $prepared_pack[4] = (strlen($tran_value) == 1) ? "0" . $tran_value
            : $tran_value;
        $tran_checksum = $this->socketC->calcAddChecksum($prepared_pack);
        $prepared_pack[8] = $tran_checksum;


        $trim_command = str_replace(" ", "", implode(" ", $prepared_pack));


        $command = new Commands();
        $command->user_id = $user_id;
        $command->thermostat_id = $thermostat_id;
        $command->command = $trim_command;
        $command->command_name = $command_name[0];
        $command->command_value = $send_val;

        $command->save();

        dispatch((new ProcessCommands($trim_command, $thermostat_id))->onQueue('high'));
        $this->checkDispatched($command->id);
    }

    public function makeModeCommand($send_register, $send_val, $user_id, $thermostat_id, $boiler = false)
    {
        $tran_register = dechex($send_register);
        $prepared_pack = [
            'F1',
            'F2',
            'A1',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            'FE',
            'FF',
        ];
        $prepared_pack[3] = (strlen($tran_register) == 1) ? "0" . $tran_register
            : $tran_register;

        $tran_value = dechex($send_val);
        $prepared_pack[4] = (strlen($tran_value) == 1) ? "0" . $tran_value
            : $tran_value;
        $tran_checksum = $this->socketC->calcAddChecksum($prepared_pack);
        $prepared_pack[8] = $tran_checksum;

        $trim_command = str_replace(" ", "", implode(" ", $prepared_pack));


        $registerData = $this->validateRegistersByRequest();
        $command_name = array_keys($registerData, $send_register);

        if (!$boiler) {
            $command = new Commands();
            $command->user_id = $user_id;
            $command->thermostat_id = $thermostat_id;
            $command->command = $trim_command;
            $command->command_name = $command_name[0];
            $command->command_value = $send_val;

            $command->save();
        }


        return $trim_command;
    }


    public function checkMaster($thermostat_id)
    {
        $thermostat = Thermostat::Find($thermostat_id);
        $property = $thermostat->properties->first();

        $status = false;

        if($property->app_type != null)
        {
            foreach($property->thermostats as $thermostat)
            {
                if($thermostat->thermostat_type_id === 5)
                {
                    $status = $thermostat->mode === 0 ? true : false;
                }
            }
        }
        return $status;
    }

    public function checkPropertyMaster($thermostat_id)
    {
        $thermostat = Thermostat::Find($thermostat_id);
        $property = $thermostat->properties->first();

        $status = true;

        if($property->app_type != null)
        {
            foreach($property->thermostats as $thermostat)
            {
                if($thermostat->thermostat_type_id === 5)
                {
                    $status = false;
                }
            }
        }
        return $status;
    }
}