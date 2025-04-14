<?php

namespace App\Http\Controllers\Api;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Thermostat;
use App\Models\Properties;
use App\Models\User;
use App\Models\Statistic;
use App\Http\Resources\PropertiesResource;
use Illuminate\Support\Facades\DB;
use Ixudra\Curl\Facades\Curl;
use App\Http\Resources\UserResource;
use Carbon\Carbon;
use App\Models\ThermostatStats;
use App\Http\Controllers\Socket\ApiSocketController;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;


class PropertiesController extends Controller
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
     *   path="/api/properties",
     *   summary="Get Properties for Auth User with number of connected
     *   Thermostats", tags={"Properties"},
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

        $user = User::find(\Auth::user()->id);
        $properties = $user->properties()->get();
        foreach ($properties as $property) {
            $thermostatsNumber = $property->thermostats()->count();
            $property['connected_thermostats'] = $thermostatsNumber;

            $unit = strtoupper($property->temp_unit) === 'C' ? 'metric'
                : 'imperial';


            $jsonfile
                = file_get_contents("http://api.openweathermap.org/data/2.5/weather?lat="
                .$property->lat."&lon=".$property->lng."&units=".$unit."&appid="
                .env('WEATHER_KEY'));
            // $weather = json_encode($jsonfile);
            $weather = json_decode($jsonfile);

            $property['owner'] = \Auth::user()->id === $property['user_id'] ? true : false;

            if ($weather->cod == 200) {
                $property['weather'] = ceil($weather->main->temp);
                $property['weather_info'] = $weather->weather[0]->main;
                $property['weather_description'] = $weather->weather[0]->description;
                $property['weather_icon'] = 'http://openweathermap.org/img/wn/'.$weather->weather[0]->icon.'@2x.png';
            }

        }
        $property = PropertiesResource::collection($properties);

        return response()->json($property, 200);
    }

    /**
     * @SWG\Get(
     *   path="/api/properties/{id}",
     *   summary="Get Specific Properties",
     *   tags={"Properties"},
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

    public function show($id)
    {
        $property = Properties::where('id', '=', $id)->with('thermostats')
            ->with('users')->first();

        $unit = strtoupper($property->temp_unit) === 'C' ? 'metric'
            : 'imperial';


        $jsonfile
            = file_get_contents("http://api.openweathermap.org/data/2.5/weather?lat="
            .$property->lat."&lon=".$property->lng."&units=".$unit."&appid="
            .env('WEATHER_KEY'));
       // $weather = json_encode($jsonfile);
        $weather = json_decode($jsonfile);


        $property->owner = \Auth::user()->id === $property->user_id ? true : false;

        if ($weather->cod == 200) {
             $property->weather = ceil($weather->main->temp);
             $property->weather_info = $weather->weather[0]->main;
             $property->weather_description = $weather->weather[0]->description;
             $property->weather_icon = 'http://openweathermap.org/img/wn/'.$weather->weather[0]->icon.'@2x.png';
         }

        $property = new PropertiesResource($property);

        return response()->json([$property], 200);
    }

    /**
     * @SWG\Post(
     *   path="/api/properties",
     *   summary="Store property",
     *   tags={"Properties"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     required=true,
     *     type="string",
     *     description= "Property name"
     *   ),
     *  @SWG\Parameter(
     *     name="status_all",
     *     in="formData",
     *     required=false,
     *     type="boolean",
     *     default=true,
     *     description= "Allow true or false"
     *   ),
     *   @SWG\Parameter(
     *     name="square_measure",
     *     in="formData",
     *     required=true,
     *     type="string",
     *     enum={"m", "f"},
     *     description= "Square mesument in meters of feets"
     *   ),
     *   @SWG\Parameter(
     *     name="number_of_therm",
     *     in="formData",
     *     required=true,
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="square_meters",
     *     in="formData",
     *     required=true,
     *     type="number",
     *     format="float",
     *     description= "Square meters with format 00.000"
     *   ),
     *   @SWG\Parameter(
     *     name="temp_unit",
     *     in="formData",
     *     required=true,
     *     type="string",
     *     enum={"C", "F"},
     *     description= "Temperature unit"
     *   ),
     *   @SWG\Parameter(
     *     name="heating_rate",
     *     in="formData",
     *     required=true,
     *     type="number",
     *     format="float",
     *     description= "Price of heating in USD or EURO with format 00.000"
     *   ),
     *   @SWG\Parameter(
     *     name="address",
     *     in="formData",
     *     required=false,
     *     type="string",
     *     description= "Pin Address"
     *   ),
     *   @SWG\Parameter(
     *     name="lat",
     *     in="formData",
     *     required=false,
     *     type="number",
     *     format="float",
     *     description= "Latitude with format 00.00000000"
     *   ),
     *   @SWG\Parameter(
     *     name="lng",
     *     in="formData",
     *     required=false,
     *     type="number",
     *     format="float",
     *     description= "Longitude with format 000.00000000"
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
        $v = validator($request->only(
            'name',
            'square_measure',
            'square_meters',
            'temp_unit',
            'heating_rate',
            'address',
            'lat',
            'lng',
            'number_of_therm'

        ), [
            'name'            => 'required|string',
            'square_measure'  => 'required|in:m,f',
            'square_meters'   => 'required||numeric',
            'temp_unit'       => 'required|in:C,F',
            'number_of_therm' => 'required|integer',
            'heating_rate'    => 'required||numeric',
            'address'         => 'string|max:255',
            'lat'             => 'numeric',
            'lng'             => 'numeric'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }


        $input = $request->all();
        $input['user_id'] = \Auth::user()->id;

        $property = new Properties();
        $property->fill($input)->save();

        $user = User::find(\Auth::user()->id);
        $user->properties()->syncWithoutDetaching($property->id);

        return response()->json($property, 200);
    }

    /**
     * @SWG\Put(
     *   path="/api/properties/{id}",
     *   summary="Store property",
     *   tags={"Properties"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *  @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     required=true,
     *     type="string",
     *     description= "Property name"
     *   ),
     *  @SWG\Parameter(
     *     name="status_all",
     *     in="formData",
     *     required=false,
     *     type="boolean",
     *     default=true,
     *     description= "Allow true or false"
     *   ),
     *   @SWG\Parameter(
     *     name="square_measure",
     *     in="formData",
     *     required=true,
     *     type="string",
     *     enum={"m", "f"},
     *     description= "Square mesument in meters of feets"
     *   ),
     *   @SWG\Parameter(
     *     name="number_of_therm",
     *     in="formData",
     *     required=true,
     *     type="integer",
     *   ),
     *   @SWG\Parameter(
     *     name="square_meters",
     *     in="formData",
     *     required=true,
     *     type="number",
     *     format="float",
     *     description= "Square meters with format 00.000"
     *   ),
     *   @SWG\Parameter(
     *     name="temp_unit",
     *     in="formData",
     *     required=true,
     *     type="string",
     *     enum={"C", "F"},
     *     description= "Temperature unit"
     *   ),
     *   @SWG\Parameter(
     *     name="heating_rate",
     *     in="formData",
     *     required=true,
     *     type="number",
     *     format="float",
     *     description= "Price of heating in USD or EURO with format 00.000"
     *   ),
     *   @SWG\Parameter(
     *     name="address",
     *     in="formData",
     *     required=false,
     *     type="string",
     *     description= "Pin Address"
     *   ),
     *   @SWG\Parameter(
     *     name="lat",
     *     in="formData",
     *     required=false,
     *     type="number",
     *     format="float",
     *     description= "Latitude with format 00.00000000"
     *   ),
     *   @SWG\Parameter(
     *     name="lng",
     *     in="formData",
     *     required=false,
     *     type="number",
     *     format="float",
     *     description= "Longitude with format 000.00000000"
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
    public function update($id, Request $request)
    {
        $property = Properties::find($id);

        if (!$property) {
            return response()->json($this->error, 404);
        }

        $input = $request->all();

        if($request->has('temp_unit')) {

            $temp_measurement = $input['temp_unit'] === 'C' ? 1 : 0;


            foreach ($property->thermostats as $thermostat) {
                $apiSocket = new ApiSocketController();
                $t = new ThermostatController($apiSocket);
                $sendComamnd = [
                    "temp_measurement" => $temp_measurement,
                    "id"               => $thermostat->id,
                    "user_id"          => \Auth::user()->id
                ];


                $request = new Request($sendComamnd);
                $t->sentCommands($request);

            }
        }
        $property->fill($input)->save();




        $property = new PropertiesResource($property);

        return response()->json($property, 200);
    }

    /**
     * @SWG\Delete(
     *   path="/api/properties/{id}",
     *   summary="Delete property",
     *   tags={"Properties"},
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

        $property = Properties::where('id', $id)->first();

        if (!$property) {
            return response()->json($this->error, 404);
        }

        if($property->user_id === $user_id)
        {
            $properties = Properties::all()->count();
            if($properties > 1) {
                $property->delete();
            }
            else {
                $error = ['status' => 'ERROR', 'error'  => trans('validation.last_property')];
                return response()->json($error, 401);
            }
        }
        else {
            DB::delete('delete from properties_user where properties_id=? and user_id=?', [$property->id, $user_id]);
            $property->users()->detach($user_id);
        }

        return response()->json([], 200);
    }

    /**
     * @SWG\Get(
     *   path="/api/properties/users",
     *   summary="Get Users for Property",
     *   tags={"Properties"},
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

    public function users()
    {
        $user = User::find(\Auth::user()->id);
        $properties = $user->properties()->first();

        if ($properties) {
            return response()->json(UserResource::collection($properties->users),
                200);
        } else {
            return response()->json(['error' => 'There are no users for this property'],
                401);
        }
    }

    /**
     * @SWG\Get(
     *   path="/api/properties/{id}/users",
     *   summary="Get Users for Property",
     *   tags={"Properties"},
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

    public function getProperties($id)
    {
        $property = Properties::where('id', '=', $id)->where('user_id', '=', \Auth::user()->id)->first();

        if ($property) {
            $users = $property->users()->where('user_id', '!=', $property->user_id)->get();
            return response()->json(UserResource::collection($users), 200);
        } else {
            return response()->json([], 200);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/total-property-consumption",
     *   summary="Get total consumption for property",
     *   tags={"Properties"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="property_id",
     *     in="formData",
     *     required=true,
     *     type="integer",
     *     description= "Property id"
     *   ),
     *  @SWG\Parameter(
     *     name="from_date",
     *     in="formData",
     *     required=true,
     *     type="string",
     *     description= "From date"
     *   ),
     *  @SWG\Parameter(
     *     name="to_date",
     *     in="formData",
     *     required=true,
     *     type="string",
     *     description= "To date"
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
    public function getTotalPropertyConsumption(Request $request)
    {
        $roles = [
            'property_id' => 'required|integer',
            'from_date'   => 'required|string',
            'to_date'     => 'required|string',
        ];

        $v = validator($request->all(), $roles);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }


        $property_id = $request->input('property_id');
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');


        $off_date = Carbon::createFromTimestampMs($to_date)->format('Y-m-d');
        $on_date =  Carbon::createFromTimestampMs($from_date)->format('Y-m-d');


        if($on_date === $off_date)
        {
            $on_date = Carbon::createFromTimestampMs($from_date)->format('Y-m-d 00:00:00');
            $off_date = Carbon::createFromTimestampMs($to_date)->format('Y-m-d 23:59:59');
        }
        else {
            $on_date = Carbon::createFromTimestampMs($from_date)->format('Y-m-d 00:00:00');
            $off_date = Carbon::createFromTimestampMs($to_date)->format('Y-m-d 23:59:59');
        }

        $property = Properties::where('id', $property_id)->with('thermostats')->first();


        $calculations = [];
        $totalTime = [];
        $totalkWh = [];
        $totalCost = [];
        $thermostat_result = [];

        foreach ($property->thermostats as $thermostat) {


            $statistic = Statistic::where('working_time', '!=', 0)
                ->where('created_at', '>=', $on_date)
                ->where('created_at', '<=', $off_date)
                ->where('thermostat_id', '=', $thermostat->id)
                ->whereNotNull('off_date')
                ->get();



            foreach($statistic as $stat)
            {

                $working_time =  $stat->working_time / 60 / 60;
                $kWh = ($stat->mat_power * $working_time) / 1000;
                $cost = $property->heating_rate * $kWh;

                $calculations[] = [
                    "thermostat"   => $thermostat->id,
                    "working_time" => $working_time,
                    "mat_power"    => $stat->mat_power,
                    "cost"         => $cost,
                    "kWh"          => $kWh,
                ];

            }


            foreach ($calculations as $calc) {
                $totalCost[] = $calc['cost'];
                $totalkWh[] = $calc['kWh'];
                $totalTime[] = $calc['working_time'];
            }

            $thermostat_result[] = [
                "thermostat_id" => $thermostat->id,
                "total_time"    => array_sum($totalTime),
                "total_kwh"     => array_sum($totalkWh),
                "total_cost"    => array_sum($totalCost)
            ];

        }

        $total_time = [];
        $total_kwh = [];
        $total_cost = [];

        foreach ($thermostat_result as $res) {
            $total_time[] = $res['total_time'];
            $total_kwh[] = $res['total_kwh'];
            $total_cost[] = $res['total_cost'];
        }

        $data = [
            "total_time" => round(array_sum($total_time), 2),
            "total_kwh"  => round(array_sum($total_kwh), 4),
            "total_cost" => round(array_sum($total_cost), 2),
        ];

        return response()->json($data, 200);

    }
}