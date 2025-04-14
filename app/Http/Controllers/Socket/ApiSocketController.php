<?php

namespace App\Http\Controllers\Socket;

use App\Jobs\PusherSettingsJob;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Thermostat;
use App\Models\FirstSetUpThermostat;
use App\Models\User;
use App\Models\Properties;
use App\Models\Statistic;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Api\ThermostatController;
use App\Models\ThermostatStats;
use Illuminate\Support\Facades\Artisan;
use Pusher\Pusher;
use App\Models\Commands;
use App\Jobs\ProcessCommands;
use App\Models\CommandScheduler;

use App\Jobs\PusherPropertyJob;
use App\Jobs\PusherSetTempJob;
use App\Jobs\PusherFirstTimeJob;
use App\Jobs\PusherThermostatJob;
use App\Jobs\PusherJob;


class ApiSocketController extends Controller
{


    private $thermostatId;


    public function __construct($thermostatId = 0)
    {
        $this->thermostatId = $thermostatId;
        $this->last_command = 0;
        $this->firstSetThermostat = '';
    }

    private function setThermostatId($thermostatId)
    {
        $this->thermostatId = $thermostatId;
    }

    public function getSocketData($socket_data, $client_address, $conn)
    {


        $splitedArrayData = $this->unpackSplitData($socket_data);
        // dump($splitedArrayData);
        if ($splitedArrayData[0] == "f1") {

            $checksum_validation = $this->validateCheckSum($splitedArrayData);

            if ($checksum_validation) {
                /* transcode data and write in DB */
                $this->sortInsertDBData($splitedArrayData, $client_address,
                    $conn);
                // return true;
                // $return_data = str_replace(" ", "", "F1 F2 A1 0A 20 00 00 00 8B FE FF");
                // $pack_data = pack("H*", $return_data);
                // return $pack_data;

            } else {

                // $return_data = str_replace(" ", "", "F1 F2 A1 0A 20 00 00 00 8B FE FF");
                // $pack_data = pack("H*", $return_data);
                // return $pack_data;
            }
        }
    }

    /* Unpack And split Socket data */
    public function unpackSplitData($socket_data)
    {
        $unpack_data = unpack('H*', $socket_data);
        $splited_data = implode(' ', str_split($unpack_data[1], 2));

        return explode(' ', $splited_data);
    }

    /* Validate CheckSum */
    public function validateCheckSum($data_array)
    {
        $recive_checksum = hexdec($data_array[count($data_array) - 3]);
        $recievedData = array_splice($data_array, -3);
        $recievedData = array_splice($data_array, 2);
        $calcu_checksum = 0;

        foreach ($recievedData as $rD) {
            $hexDec = hexdec($rD);
            $calcu_checksum ^= $hexDec;
        }

        // var_dump($calcu_checksum);
        return ($calcu_checksum == $recive_checksum) ? true : false;
    }

    /* Sort and Insert data in DB */
    public function sortInsertDBData($recieve_data, $client_address, $conn)
    {


        $spliceStartStop = array_splice($recieve_data, -3);
        $spliceStartStop = array_splice($recieve_data, 2);


        $thermostat = Thermostat::where('ip_address', '=', $client_address['ip'])->where('port', '=', $client_address['port'])->first();


        switch ($spliceStartStop[0]) {
            case "01":
                /* Group Values */
                $filterData = array_slice($spliceStartStop, 1, 12);
                $thermostat_id = $this->insertUpdateTermostatG1($filterData, $client_address);
                if ($thermostat_id) {
                    $this->setThermostatId($thermostat['id']);
                } else {
                    $conn->close();
                }
                break;
            case "02":
                /* Group Temperature */
                $filterData = array_slice($spliceStartStop, 1, 6);
                $this->insertUpdateTermostatG2($filterData, $thermostat['id']);
                break;
            case "03":
                /* Group Definitions */
                // var_dump('No Filtered', $spliceStartStop);
                $filterData = array_slice($spliceStartStop, 1, 23);

                $this->insertUpdateTermostatG3($filterData, $thermostat['id']);
                break;
            case "04":
                /* Group External Values */
                // var_dump('No Filtered', $spliceStartStop);
                $filterData = array_slice($spliceStartStop, 1, 6);
                $this->insertUpdateTermostatG4($filterData, $thermostat['id']);
                // dd('Filtered', $filterData);
                break;
            case "05":
                /* Group Spare */
                break;
            case "06":
                /* Group Faults */
                // var_dump('No Filtered', $spliceStartStop);
                $filterData = array_slice($spliceStartStop, 1, 3);
                $this->insertUpdateTermostatG6($filterData, $thermostat['id']);
                // dd('Filtered', $filterData);
                break;
            case "07":
                /* Group Spare for Air Condition */
                // var_dump('No Filtered', $spliceStartStop);

                $filterData = array_slice($spliceStartStop, 1, 6);

                $this->insertUpdateTermostatG7($filterData, $thermostat['id']);
                // dd('Filtered', $filterData);
                break;
            case "a1":
                $filterData = array_slice($spliceStartStop, 1, 5);
                $this->insertByCommand($filterData[0], $filterData[1], $thermostat['id']);
                break;
            case "08":
                $filterData = array_slice($spliceStartStop, 1, 11);
                $this->insertUpdateThermostatG8($filterData, $client_address);
                break;
        }
    }

    public function insertUpdateTermostatG1($filteredData, $client_address)
    {


        $room_tem = hexdec($filteredData[0]);
        $floor_tem = hexdec($filteredData[1]);
        $comp_tem = hexdec($filteredData[2]);
        $target_tem = hexdec($filteredData[3]);
        $relay_status = hexdec($filteredData[4]);

        $mac_data = array_slice($filteredData, 5, 6);
        $convert_mac = implode(':', $mac_data);
        $signal_strength = hexdec($filteredData[11]);


        $thermostat = Thermostat::where('therm_mac_address', $convert_mac)
            ->first();


        if ($thermostat) {

            $checkCommands = [
                'room_tem' => $room_tem,
                'floor_tem' => $floor_tem,
                'comp_tem' => $comp_tem,
                'target_tem' => $target_tem,
                'relay_status' => $relay_status,
            ];


            (in_array($room_tem, range(0, 50))) ?
                $thermostat->room_temp = $room_tem : null;
            (in_array($floor_tem, range(0, 50))) ?
                $thermostat->floor_temp = $floor_tem : null;
            (in_array($comp_tem, range(0, 50))) ?
                $thermostat->comp_temp = $comp_tem : null;
            (in_array($target_tem, range(0, 50))) ?
                $thermostat->target_temp = $target_tem : null;
            ($relay_status == 0 || $relay_status == 1) ? $thermostat->relay_status = $relay_status : null;
            // $thermostat->signal_strength = $signal_stren; not used yet
            $thermostat->ip_address = $client_address['ip'];
            $thermostat->port = $client_address['port'];

            (in_array($signal_strength, range(0, 100))) ?
                $thermostat->signal_strength = $signal_strength : null;


            $thermostat_stats = ThermostatStats::where('thermostat_id', '=',
                $thermostat->id)
                ->where('ip', '=', $client_address['ip'])
                ->where('port', '=', $client_address['port'])
                ->first();

            if (!$thermostat_stats) {
                $stats = new ThermostatStats();
                $stats->thermostat_id = $thermostat->id;
                $stats->port = $client_address['port'];
                $stats->ip = $client_address['ip'];
                $stats->connected = Carbon::now();
                $stats->save();

                $isMaster = $this->checkIsMaster($thermostat->id);
                if($isMaster)
                {
                    $this->setSlavesOn($thermostat->id);
                }

            } else {
                if ($thermostat_stats->disconnected != null) {
                    $thermostat_stats->disconnected = null;
                    $thermostat_stats->save();
                }
            }


            foreach ($checkCommands as $key => $value) {
                $this->checkLastCommand($thermostat->id, $key, $value);
            }

            if ($thermostat->isDirty()) {

                $this->setDailyStatistic($relay_status, $thermostat->id);

                $this->pusher($thermostat->id);
                // dispatch((new PusherJob($thermostat->id, false))->onQueue('high'));
            }


            $thermostat->save();

            return $thermostat->id;

        } else {

            dump('Mac Address: ' . $convert_mac);

            $first_set = FirstSetUpThermostat::where('mac_address',
                $convert_mac)->orderBy('id', 'desc')->first();

            if ($first_set) {


                $user = User::find($first_set->user_id);

                if (!$user) {
                    return false;
                }

                $property = Properties::find($first_set->property_id);
                if (!$property) {
                    return false;
                }


                if (!($property->lat === null && $property->lng === null)) {
                    $property->lat = $first_set->lat;
                    $property->lng = $first_set->lng;
                    $property->save();
                }


                if ($first_set->thermostat_type_id === 5) {
                    $property->app_type = 1;
                    $property->save();
                }

                $thermostat = new Thermostat();
                $thermostat->room_name = $first_set->room_name;
                $thermostat->set_temp = 16;
                $thermostat->room_area = $first_set->room_area;
                $thermostat->mat_power = $first_set->mat_power;
                $thermostat->thermostat_type_id = $first_set->thermostat_type_id;
                $thermostat->temp_limitation = $first_set->temp_limitation;
                $thermostat->therm_mac_address = $convert_mac;
                $thermostat->ip_address = $client_address['ip'];
                $thermostat->port = $client_address['port'];
                $thermostat->temp_measurement = $first_set->temp_measurement === 'C' ? 1 : 0;
                $thermostat->square_measurement = $first_set->square_measurement === 'm' ? 'm' : 'f';
                $thermostat->mode = 1;
                $thermostat->previous_state = 1;
                $thermostat->force_off = true;

                (in_array($room_tem, range(0, 50))) ?
                    $thermostat->room_temp = $room_tem : null;
                (in_array($floor_tem, range(0, 50))) ?
                    $thermostat->floor_temp = $floor_tem : null;
                (in_array($comp_tem, range(0, 50))) ?
                    $thermostat->comp_temp = $comp_tem : null;
                (in_array($target_tem, range(0, 50))) ?
                    $thermostat->target_temp = $target_tem : null;
                ($relay_status == 0 || $relay_status == 1) ?
                    $thermostat->relay_status = $relay_status : null;
                // $thermostat->signal_strength = $signal_stren;


                $thermostat->save();


                $stats = new ThermostatStats();
                $stats->thermostat_id = $thermostat->id;
                $stats->port = $client_address['port'];
                $stats->ip = $client_address['ip'];
                $stats->connected = Carbon::now();
                $stats->save();


                $this->pusher($thermostat->id, true);
                // dispatch((new PusherJob($thermostat->id, true))->onQueue('high'));


                $first_setups = FirstSetUpThermostat::where('mac_address', $convert_mac)->get();
                foreach ($first_setups as $first_setup) {
                    $first_setup->delete();
                }


                $user->thermostats()->syncWithoutDetaching($thermostat->id);
                $property->thermostats()->syncWithoutDetaching($thermostat->id);
                $user->properties()->syncWithoutDetaching($property->id);


                $t = new ThermostatController($this);

                $sensors_mode = 0;
                switch ($thermostat->thermostat_type_id) {
                    case 1:
                        $sensors_mode = 2;
                        break;
                    case 2:
                        $sensors_mode = 0;
                        break;
                    case 3:
                        $sensors_mode = 1;
                        break;
                    case 4:
                        $sensors_mode = 3;
                        break;
                    case 5:
                        $sensors_mode = 0;
                        break;
                    case 6:
                        $sensors_mode = 5;
                        break;
                }


                $sendCommand = [
                    "sensors_mode" => $sensors_mode,
                    "temp_measurement" => $thermostat->temp_measurement,
                    "id" => $thermostat->id,
                    "user_id" => $user->id,
                    "sched_temp" => 20,
                    "set_temp" => 16
                ];

                if ($first_set->temp_limitation != 0) {
                    $sendCommand['temp_limiter'] = $first_set->temp_limitation;
                }



                if ($t->checkMaster($thermostat->id)) {
                    $sendCommand['mode'] = 0;
                }


                if($t->checkPropertyMaster($thermostat->id)) {
                    dump('pravi tuka proverka');
                    $property->app_type = null;
                    $property->save();
                }


                //   dump($sendCommand);

                $request = new Request($sendCommand);
                $t->sentCommands($request, 5);


                // $t->sentAllCommands($thermostat->id);


                /*
                 * Check scheduler now
                 *
                 *
                 *
                 *
                $carbon = new \Carbon\Carbon();
                $carbon->setTimezone('Europe/Skopje');
                $time_now = $carbon->now('Europe/Skopje');

                $dayOfTheWeek = $time_now->dayOfWeek;

                $command = CommandScheduler::where('start_time', '<=', $time_now->toTimeString())
                    ->where('thermostat_id', '=', $thermostat->id)
                    ->where('day', '=', $dayOfTheWeek)->first();

                if ($command) {
                    dispatch((new ProcessCommands($command->command, $thermostat->id))->onQueue('high'));
                }
                */

                return $thermostat->id;
            } else {
                return false;
            }


        }
    }

    public function insertUpdateTermostatG2($filteredData, $thermostatId)
    {


        $thermostat = Thermostat::find($thermostatId);

        dump('Set temp for thermostat: ' . $thermostat->id);

        if ($thermostat) {
            $set_temp = hexdec($filteredData[0]);
            $sched_temp = hexdec($filteredData[1]);
            $max_temp = hexdec($filteredData[2]);
            $min_temp = hexdec($filteredData[3]);
            $offset_hex = hexdec($filteredData[4]);
            $offset_bin = str_split(decbin($offset_hex));
            $offset_sign = hexdec($offset_bin[0]);
            $offset_temp_slice = array_slice($offset_bin, 1, 7);
            $offset_temp = bindec(implode('', $offset_temp_slice));
            $temp_limiter = hexdec($filteredData[5]);


            $checkCommands = [
                'set_temp' => $set_temp,
                'sched_temp' => $sched_temp,
                'max_temp' => $max_temp,
                'min_temp' => $min_temp,
                'offset_sign' => $offset_sign,
                'offset_temp' => $offset_temp,
                'temp_limiter' => $temp_limiter,
            ];


            $dumped = $checkCommands;
            $dumped['thermostat_id'] = $thermostatId;
            //    dump($dumped);

            (in_array($set_temp, range(0, 50))) ?
                $thermostat->set_temp = $set_temp
                : null;
            (in_array($sched_temp, range(0, 50))) ?
                $thermostat->sched_temp = $sched_temp : null;
            (in_array($max_temp, range(22, 50))) ?
                $thermostat->max_temp = $max_temp
                : null;
            (in_array($min_temp, range(5, 21))) ?
                $thermostat->min_temp = $min_temp
                : null;
            ($offset_sign == 0 || $offset_sign == 1) ?
                $thermostat->offset_sign = $offset_sign : null;
            (in_array($offset_temp, range(5, 21))) ?
                $thermostat->offset_temp = $offset_temp : null;
            (in_array($temp_limiter, range(0, 50))) ?
                $thermostat->temp_limiter = $temp_limiter : null;


            foreach ($checkCommands as $key => $value) {
                $this->checkLastCommand($thermostat->id, $key, $value);
            }


            if ($this->last_command === 2) {
                if ($thermostat->isDirty('set_temp') || $thermostat->isDirty('sched_temp')) {
                    $from_thermostat = true;
                    $this->pusher($thermostatId, false, true, false, $from_thermostat);
                }
            }


            if ($thermostat->isDirty()) {
                $thermostat->save();


                $this->pusher($thermostat->id, false, false, false);
                // dispatch((new PusherJob($thermostat->id, false))->onQueue('high'));

            }


        }


    }

    public function insertUpdateTermostatG3($filteredData, $thermostatId)
    {
        $thermostat = Thermostat::find($thermostatId);

        if ($thermostat) {
            $mode = hexdec($filteredData[0]);
            $sensors_mode = hexdec($filteredData[1]);
            $temp_measurement = hexdec($filteredData[2]);
            $relay_opera = hexdec($filteredData[3]);
            $relay_limit = hexdec($filteredData[4]);
            $sensitivity = hexdec($filteredData[5]);
            $differential = hexdec($filteredData[6]);
            $cool_heat_mode = hexdec($filteredData[7]);
            $boiler_duration = hexdec($filteredData[8]);
            $home_router_mac_address = '00:00:00:00:00:00'; //need correct format for mac address
            $bathroom_on_low_heat = hexdec($filteredData[10]);
            $bathroom_delay_stby_low_heat = hexdec($filteredData[11]);
            $bathroom_on_med_heat = hexdec($filteredData[12]);
            $bathroom_delay_stby_med_heat = hexdec($filteredData[13]);
            $bathroom_on_high_heat = hexdec($filteredData[14]);
            $bathroom_delay_stby_high_heat = hexdec($filteredData[15]);
            $cool_room_check = hexdec($filteredData[16]);
            $boost = hexdec($filteredData[17]);
            $ligth_intensity = hexdec($filteredData[18]);
            $enab_vibration = hexdec($filteredData[19]);
            $enab_matrix = hexdec($filteredData[20]);
            $enab_hart_beep_led = hexdec($filteredData[21]);
            if (array_key_exists(22, $filteredData)) {

                $enab_lock = hexdec($filteredData[22]);
            } else {
                $enab_lock = 0;
            }


            $checkCommands = [
                'mode' => $mode,
                'sensors_mode' => $sensors_mode,
                'temp_measurement' => $temp_measurement,
                'relay_opera' => $relay_opera,
                'relay_limit' => $relay_limit,
                'sensitivity' => $sensitivity,
                'differential' => $differential,
                'cool_heat_mode' => $cool_heat_mode,
                'boiler_duration' => $boiler_duration,
                'bathroom_on_low_heat' => $bathroom_on_low_heat,
                'bathroom_delay_stby_low_heat' => $bathroom_delay_stby_low_heat,
                'bathroom_on_med_heat' => $bathroom_on_med_heat,
                'bathroom_delay_stby_med_heat' => $bathroom_delay_stby_med_heat,
                'bathroom_on_high_heat' => $bathroom_on_high_heat,
                'bathroom_delay_stby_high_heat' => $bathroom_delay_stby_high_heat,
                'cool_room_check' => $cool_room_check,
                'boost' => $boost,
                'ligth_intensity' => $ligth_intensity,
                'enab_vibration' => $enab_vibration,
                'enab_matrix' => $enab_matrix,
                'enab_hart_beep_led' => $enab_hart_beep_led,
                'enab_lock' => $enab_lock,
            ];


            //     dump('Boiler Duration: '.$boiler_duration);

            (in_array($mode, range(0, 4))) ? $thermostat->mode = $mode : null;
            ($temp_measurement == 0 || $temp_measurement == 1) ? $thermostat->temp_measurement = $temp_measurement : null;
            ($relay_opera == 0 || $relay_opera == 1) ? $thermostat->relay_opera = $relay_opera : null;
            (in_array($relay_limit, range(0, 10))) ? $thermostat->relay_limit = $relay_limit : null;
            (in_array($sensitivity, range(0, 3))) ?
                $thermostat->sensitivity = $sensitivity : null;
            (in_array($differential, range(0, 3))) ?
                $thermostat->differential = $differential : null;
            (in_array($cool_heat_mode, range(0, 2))) ?
                $thermostat->cool_heat_mode = $cool_heat_mode : null;
            (in_array($boiler_duration, range(30, 210))) ?
                $thermostat->boiler_duration = $boiler_duration : null;
            /* (in_array($home_router_mac_address, range(0,5))) ?*/
            $thermostat->home_router_mac_address
                = $home_router_mac_address /*: NULL*/
            ;

            (in_array($bathroom_on_low_heat, range(1, 30))) ?
                $thermostat->bathroom_on_low_heat = $bathroom_on_low_heat
                : null;

            (in_array($bathroom_delay_stby_low_heat, range(1, 30))) ?
                $thermostat->bathroom_delay_stby_low_heat
                    = $bathroom_delay_stby_low_heat : null;
            (in_array($bathroom_on_med_heat, range(1, 30))) ?
                $thermostat->bathroom_on_med_heat = $bathroom_on_med_heat
                : null;
            (in_array($bathroom_delay_stby_med_heat, range(1, 30))) ?
                $thermostat->bathroom_delay_stby_med_heat
                    = $bathroom_delay_stby_med_heat : null;
            (in_array($bathroom_on_high_heat, range(1, 30))) ?
                $thermostat->bathroom_on_high_heat = $bathroom_on_high_heat
                : null;
            (in_array($bathroom_delay_stby_high_heat, range(1, 30))) ?
                $thermostat->bathroom_delay_stby_high_heat
                    = $bathroom_delay_stby_high_heat : null;
            (in_array($cool_room_check, range(0, 120))) ?
                $thermostat->cool_room_check = $cool_room_check : null;
            (in_array($boost, range(0, 240))) ? $thermostat->boost = $boost
                : null;
            (in_array($ligth_intensity, range(0, 2))) ?
                $thermostat->ligth_intensity = $ligth_intensity : null;
            ($enab_vibration == 0 || $enab_vibration == 1) ?
                $thermostat->enab_vibration = $enab_vibration : null;
            ($enab_matrix == 0 || $enab_matrix == 1) ?
                $thermostat->enab_matrix = $enab_matrix : null;
            ($enab_hart_beep_led == 0 || $enab_hart_beep_led == 1) ?
                $thermostat->enab_hart_beep_led = $enab_hart_beep_led : null;

            if ($enab_lock) {
                ($enab_lock == 0 || $enab_lock == 1) ?
                    $thermostat->enab_lock = $enab_lock : null;
            }


            foreach ($checkCommands as $key => $value) {

                $this->checkLastCommand($thermostat->id, $key, $value);
            }


            // $this->checkLastCommands($thermostat->id, $checkCommands);

            $t = new ThermostatController($this);
            $user = $thermostat->users()->first();



            // Change app type always!
            if ($mode === 0) {

                if ($thermostat->thermostat_type_id === 5) {
                    $property = $thermostat->properties->first();
                    $property->app_type = 2;
                    $property->save();
                }
            }

            if ($mode === 1) {
                $thermostat->status_mode = $mode;
                if ($thermostat->thermostat_type_id === 5) {
                    $property = $thermostat->properties->first();
                    $property->app_type = 1;
                    $property->save();
                }
            }



            // Mode logic
            if ($thermostat->isDirty('mode')) {


                if ($mode === 0) {

                    if ($thermostat->thermostat_type_id === 5) {
                        $property = $thermostat->properties->first();
                        $property->app_type = 2;
                        $property->save();

                        foreach ($property->thermostats as $index => $therm) {
                            if ($thermostat->id != $therm->id) {
                                $offCommand = $t->makeModeCommand(20, 0, $user->id, $therm->id);
                                dispatch((new ProcessCommands($offCommand, $therm->id))->delay($index));
                                dispatch((new ProcessCommands($offCommand, $therm->id))->delay($index + 1));
                            }

                        }
                    }
                }

                if ($mode === 1) {
                    $thermostat->status_mode = $mode;
                    if ($thermostat->thermostat_type_id === 5) {
                        $property = $thermostat->properties->first();
                        $property->app_type = 1;
                        $property->save();
                        foreach ($property->thermostats as $index => $therm) {
                            if ($therm->id != $thermostat->id) {
                                $onCommand = $t->makeModeCommand(20, 1, $user->id, $therm->id);
                                dispatch((new ProcessCommands($onCommand, $therm->id))->delay($index));
                                dispatch((new ProcessCommands($onCommand, $therm->id))->delay($index + 1));
                            }
                        }
                    }
                }

                if ($mode === 2) {
                    $thermostat->status_mode = $mode;
                    $carbon = new \Carbon\Carbon();
                    $carbon->setTimezone('Europe/Skopje');
                    $time_now = $carbon->now('Europe/Skopje');

                    $dayOfTheWeek = $time_now->dayOfWeek;


                    $commands = CommandScheduler::where('start_time', '<=', $time_now->toTimeString())
                        ->where('day', '=', $dayOfTheWeek)
                        ->where('thermostat_id', '=', $thermostat->id)
                        ->orderBy('id', 'desc')->first();

                    if ($commands) {
                        if ($thermostat->thermostat_type_id === 5 && $commands->command_name === "mode") {
                            $property = $thermostat->properties->first();
                            foreach ($property->thermostats as $index => $therm) {
                                if ($thermostat->id != $therm->id) {
                                    $offCommand = $t->makeModeCommand(20, 0, $user->id, $therm->id);
                                    dispatch((new ProcessCommands($offCommand, $therm->id))->onQueue('high'));

                                }

                                dispatch((new ProcessCommands($commands->command, $thermostat->id))->onQueue('high'));

                            }
                        } elseif ($thermostat->thermostat_type_id === 5) {
                            $property = $thermostat->properties->first();
                            foreach ($property->thermostats as $index => $therm) {
                                if ($thermostat->id != $therm->id) {

                                    $onCommand = $t->makeModeCommand(20, 1, $user->id, $therm->id);
                                    dispatch((new ProcessCommands($onCommand, $therm->id))->onQueue('high'));
                                }

                            }

                            dispatch((new ProcessCommands($commands->command, $thermostat->id))->onQueue('high'));
                        } else {
                            dispatch((new ProcessCommands($commands->command, $thermostat->id))->onQueue('high'));
                        }
                    }
                }

                if ($mode > 2) {
                    $thermostat->status_mode = $mode;
                }


                $thermostat->save();

                $this->pusher($thermostat->id, false, true);
                //  dispatch((new PusherJob($thermostat->id, false, true))->onQueue('high'));
            } else {
                if ($thermostat->isDirty()) {

                    $thermostat->save();
                    dump('tuka eden dump');
                    $this->pusher($thermostat->id, false, false, true);
                    // dispatch((new PusherJob($thermostat->id, false))->onQueue('high'));

                }
            }
        }

    }

    public function insertUpdateTermostatG4($filteredData, $thermostatId)
    {

        $thermostat = Thermostat::find($thermostatId);

        if ($thermostat) {
            $last_command = hexdec($filteredData[0]);
            $last_operation = hexdec($filteredData[1]);
            $last_operation_value = hexdec($filteredData[2]);
            $restore_default = hexdec($filteredData[3]);
            $encription_code = hexdec($filteredData[4]);


            $this->last_command = $last_command;


            $checkCommands = [
                'last_command' => $last_command,
                'last_operation' => $last_operation,
                'last_operation_value' => $last_operation_value,
                'restore_default' => $restore_default,
                'encription_code' => $encription_code,
            ];


            (in_array($last_command, range(0, 2))) ? $thermostat->last_command = $last_command : null;
            (in_array($last_operation, range(0, 15))) ?
                $thermostat->last_operation = $last_operation : null;
            (in_array($last_operation_value, range(0, 50))) ?
                $thermostat->last_operation_value = $last_operation_value
                : null;
            ($restore_default == 0 || $restore_default == 1) ?
                $thermostat->restore_default = $restore_default : null;
            (in_array($encription_code, range(0, 999))) ?
                $thermostat->encription_code = $encription_code : null;


            foreach ($checkCommands as $key => $value) {
                $this->checkLastCommand($thermostat->id, $key, $value);
            }

            if ($thermostat->isDirty()) {
                $thermostat->save();

                $this->pusher($thermostat->id);
                // dispatch((new PusherJob($thermostat->id, false))->onQueue('high'));
            }
        }


    }

    public function insertUpdateTermostatG6($filteredData, $thermostatId)
    {

        $thermostat = Thermostat::find($thermostatId);

        if ($thermostat) {
            $indor_sensor = hexdec($filteredData[0]);
            $floor_sensor = hexdec($filteredData[1]);
            $commu_status = hexdec($filteredData[2]);


            $checkCommands = [
                'indor_sensor' => $indor_sensor,
                'floor_sensor' => $floor_sensor,
                'commu_status' => $commu_status,
            ];


            ($indor_sensor == 0 || $indor_sensor == 1) ?
                $thermostat->indor_sensor = $indor_sensor : null;
            ($floor_sensor == 0 || $floor_sensor == 1) ?
                $thermostat->floor_sensor = $floor_sensor : null;
            ($commu_status == 0 || $commu_status == 1) ?
                $thermostat->commu_status = $commu_status : null;


            foreach ($checkCommands as $key => $value) {
                $this->checkLastCommand($thermostat->id, $key, $value);
            }


            if ($thermostat->isDirty()) {
                $thermostat->save();

                $this->pusher($thermostat->id);
                // dispatch((new PusherJob($thermostat->id, false))->onQueue('high'));
            }
        }


    }

    public function insertUpdateTermostatG7($filteredData, $thermostatId)
    {
        $thermostat = Thermostat::find($thermostatId);

        if ($thermostat) {
            $cool_heat = hexdec($filteredData[0]);
            $fan_speed = hexdec($filteredData[1]);


            $checkCommands = [
                'cool_heat' => $cool_heat,
                'fan_speed' => $fan_speed,
            ];

            /*$commu_status  = hexdec($filteredData[2]);
            $indor_sensor  = hexdec($filteredData[3]);
            $floor_sensor  = hexdec($filteredData[4]);
            $commu_status  = hexdec($filteredData[5]); Spare*/

            ($cool_heat == 0 || $cool_heat == 1) ?
                $thermostat->cool_heat = $cool_heat : null;
            (in_array($fan_speed, range(0, 3))) ?
                $thermostat->fan_speed = $fan_speed : null;


            foreach ($checkCommands as $key => $value) {
                $this->checkLastCommand($thermostat->id, $key, $value);
            }


            if ($thermostat->isDirty()) {

                $thermostat->save();
                $this->pusher($thermostat->id);
                // dispatch((new PusherJob($thermostat->id, false))->onQueue('high'));
            }
        }


    }


    public function insertUpdateThermostatG8($filteredData, $client_address)
    {

        $relay_status = hexdec($filteredData[0]);
        $mac_data = array_slice($filteredData, 1, 6);
        $convert_mac = implode(':', $mac_data);
        $signal_strength = hexdec($filteredData[7]);
        $mode = hexdec($filteredData[8]);
        $boiler_duration = hexdec($filteredData[9]);
        $last_command = hexdec($filteredData[10]);


        $thermostat = Thermostat::where('therm_mac_address', $convert_mac)->first();
        if ($thermostat) {
            (in_array($signal_strength, range(0, 100))) ? $thermostat->signal_strength = $signal_strength : null;
            ($relay_status == 0 || $relay_status == 1) ? $thermostat->relay_status = $relay_status : null;
            (in_array($mode, range(0, 4))) ? $thermostat->mode = $mode : null;
            (in_array($boiler_duration, range(30, 120))) ? $thermostat->boiler_duration = $boiler_duration : null;
            (in_array($last_command, range(0, 2))) ? $thermostat->last_command = $last_command : null;

            $thermostat->ip_address = $client_address['ip'];
            $thermostat->port = $client_address['port'];

            $checkCommands = [
                'mode' => $mode,
                'boiler_duration' => $boiler_duration
            ];


            foreach ($checkCommands as $key => $value) {
                $this->checkLastCommand($thermostat->id, $key, $value);
            }

            $dumpCommands = [
                'thermostat' => $thermostat->room_name,
                'mode' => $mode,
                'boiler_duration' => $boiler_duration,
                'convert_mac' => $convert_mac,
                'signal_strength' => $signal_strength,
                'relay_status' => $relay_status,
                'last_command' => $last_command
            ];


            $thermostat->save();
            $this->pusher($thermostat->id, false, true, false, true);
            dispatch((new PusherThermostatJob($thermostat->id))->onQueue('high'));


            $thermostat_stats = ThermostatStats::where('thermostat_id', '=', $thermostat->id)
                ->where('ip', '=', $client_address['ip'])
                ->where('port', '=', $client_address['port'])
                ->first();

            if (!$thermostat_stats) {
                $stats = new ThermostatStats();
                $stats->thermostat_id = $thermostat->id;
                $stats->port = $client_address['port'];
                $stats->ip = $client_address['ip'];
                $stats->connected = Carbon::now();
                $stats->save();
            } else {
                if ($thermostat_stats->disconnected != null) {
                    $thermostat_stats->disconnected = null;
                    $thermostat_stats->save();
                }
            }

            $this->setDailyStatistic($relay_status, $thermostat->id);


        } else {

            $first_set = FirstSetUpThermostat::where('mac_address', $convert_mac)->orderBy('id', 'desc')->first();

            if ($first_set) {

                $user = User::find($first_set->user_id);

                if (!$user) {
                    return false;
                }

                $property = Properties::find($first_set->property_id);
                if (!$property) {
                    return false;
                }

                if (!($property->lat === null && $property->lng === null)) {
                    $property->lat = $first_set->lat;
                    $property->lng = $first_set->lng;
                    $property->save();
                }

                $thermostat = new Thermostat();
                $thermostat->room_name = $first_set->room_name;
                $thermostat->set_temp = 16;
                $thermostat->room_area = $first_set->room_area;
                $thermostat->mat_power = $first_set->mat_power;
                $thermostat->thermostat_type_id = $first_set->thermostat_type_id;
                $thermostat->temp_limitation = $first_set->temp_limitation;
                $thermostat->therm_mac_address = $convert_mac;
                $thermostat->ip_address = $client_address['ip'];
                $thermostat->port = $client_address['port'];
                $thermostat->temp_measurement = $first_set->temp_measurement === 'C' ? 1 : 0;
                $thermostat->square_measurement = $first_set->square_measurement === 'm' ? 'm' : 'f';
                $thermostat->mode = 1;
                $thermostat->previous_state = 1;
                $thermostat->force_off = true;

                $thermostat->save();

                $this->pusher($thermostat->id, true);
                // dispatch((new PusherJob($thermostat->id, true))->onQueue('high'));


                $first_setups = FirstSetUpThermostat::where('mac_address', $convert_mac)->get();
                foreach ($first_setups as $first_setup) {
                    $first_setup->delete();
                }


                $user->thermostats()->syncWithoutDetaching($thermostat->id);
                $property->thermostats()->syncWithoutDetaching($thermostat->id);
                $user->properties()->syncWithoutDetaching($property->id);


                $stats = new ThermostatStats();
                $stats->thermostat_id = $thermostat->id;
                $stats->port = $client_address['port'];
                $stats->ip = $client_address['ip'];
                $stats->connected = Carbon::now();
                $stats->save();


                $t = new ThermostatController($this);


                $sendCommand = [
                    "sensors_mode" => 5,
                    "id" => $thermostat->id,
                    "user_id" => $user->id,
                ];

                $request = new Request($sendCommand);
                $t->sentCommands($request, 5);


                return $thermostat->id;
            }
        }
    }

    public function insertByCommand($command_key, $value, $thermostatId)
    {

        $thermostat = Thermostat::find($thermostatId);


        if ($thermostat) {
            $command_key = hexdec($command_key);
            $value = hexdec($value);


            dump('Command Key: ' . $command_key . ' Command Value: ' . $value . 'thermostat_id' . $thermostat->id);

            $t = new ThermostatController($this);
            $registerData = $t->validateRegistersByRequest();
            $command_name = array_keys($registerData, $command_key);

            if (is_array($command_name) && !empty($command_name)) {
                $command_name = $command_name[0];
                //  $thermostat->toArray();
                $thermostat[$command_name] = $value;


                $t = new ThermostatController($this);
                $user = $thermostat->users()->first();

                if ($thermostat->isDirty('mode')) {

                    $mode = $value;


                    if ($mode === 0) {
                        if ($thermostat->thermostat_type_id === 5) {
                            $property = $thermostat->properties->first();
                            $property->app_type = 2;
                            $property->save();

                            foreach ($property->thermostats as $index => $therm) {
                                if ($thermostat->id != $therm->id) {
                                    $offCommand = $t->makeModeCommand(20, 0, $user->id, $therm->id);
                                    dispatch((new ProcessCommands($offCommand, $therm->id))->onQueue('high'));
                                    // dispatch((new ProcessCommands($offCommand, $therm->id))->delay($index + 1));
                                }

                            }
                        }

                        $this->pusher($thermostat->id, false, false, false, true);
                        $this->pusher($thermostat->id, false, false, false, false);
                        $this->pusher($thermostat->id, false, true, false, false);
                        $this->pusher($thermostat->id, false, true, false, true);

                    }

                    if ($mode === 1) {


                        $thermostat->status_mode = $mode;


                        // Boiler
                        if ($thermostat->sensors_mode === 5) {

                            var_dump('vlaga tuka');
                            $boiler_duration = $t->makeModeCommand(28, 30, $user->id, $thermostat->id);
                            dispatch((new ProcessCommands($boiler_duration, $thermostat->id))->onQueue('high'));
                        }
                        // End Boiler


                        if ($thermostat->thermostat_type_id === 5) {
                            $property = $thermostat->properties->first();
                            $property->app_type = 1;
                            $property->save();


                            $set_temp = $t->makeModeCommand(10, 45, $user->id, $thermostat->id);
                            dispatch((new ProcessCommands($set_temp, $thermostat->id))->delay(1));

                            foreach ($property->thermostats as $index => $therm) {
                                if ($therm->id != $thermostat->id) {
                                    $onCommand = $t->makeModeCommand(20, 1, $user->id, $therm->id);
                                    dispatch((new ProcessCommands($onCommand, $therm->id))->onQueue('high'));
                                    //  dispatch((new ProcessCommands($onCommand, $therm->id))->delay($index + 1));
                                }
                            }
                        }

                        $this->pusher($thermostat->id, false, false, false, true);
                        $this->pusher($thermostat->id, false, false, false, false);
                        $this->pusher($thermostat->id, false, true, false, false);
                        $this->pusher($thermostat->id, false, true, false, true);
                    }

                    if ($mode === 2) {
                        $thermostat->status_mode = $mode;
                        $carbon = new \Carbon\Carbon();
                        $carbon->setTimezone('Europe/Skopje');
                        $time_now = $carbon->now('Europe/Skopje');

                        $dayOfTheWeek = $time_now->dayOfWeek;
                        dump($dayOfTheWeek);

                        $commands = CommandScheduler::where('start_time', '<=', $time_now->toTimeString())
                            ->where('end_time', '>=', $time_now->toTimeString())
                            ->where('day', '=', $dayOfTheWeek)
                            ->where('thermostat_id', '=', $thermostat->id)
                            ->orderBy('id', 'asc')->first();


                        dump($commands);
                        dump($time_now);


                        if (!$commands) {
                            $commands = CommandScheduler::where('day', '<', $dayOfTheWeek)
                                ->where('thermostat_id', '=', $thermostat->id)
                                ->where('command_name', '!=', 'mode')
                                ->orderBy('id', 'desc')->first();
                        }



                        if ($commands) {


                            if ($thermostat->thermostat_type_id === 5 && $commands->command_name === "mode") {
                                $property = $thermostat->properties->first();
                                foreach ($property->thermostats as $index => $therm) {
                                    if ($thermostat->id != $therm->id) {
                                        $offCommand = $t->makeModeCommand(20, 0, $user->id, $therm->id);
                                        dispatch((new ProcessCommands($offCommand, $therm->id))->onQueue('high'));

                                    }
                                    dispatch((new PusherSetTempJob($thermostat->id, false))->onQueue('high'));
                                    dispatch((new ProcessCommands($commands->command, $thermostat->id))->onQueue('high'));

                                }
                            } elseif ($thermostat->thermostat_type_id === 5) {
                                $property = $thermostat->properties->first();
                                foreach ($property->thermostats as $index => $therm) {
                                    if ($thermostat->id != $therm->id) {

                                        $onCommand = $t->makeModeCommand(20, 1, $user->id, $therm->id);
                                        dispatch((new ProcessCommands($onCommand, $therm->id))->onQueue('high'));
                                    }

                                }
                                dispatch((new PusherSetTempJob($thermostat->id, false))->onQueue('high'));
                                dispatch((new ProcessCommands($commands->command, $thermostat->id))->onQueue('high'));
                            } else {


                                dispatch((new ProcessCommands($commands->command, $thermostat->id))->onQueue('high'));
                               // dispatch((new ProcessCommands($commands->command, $thermostat->id))->delay(2));
                                dump('tuka treba da ja pushti: ' . $commands->command_name . ' '. $commands->command_value);

                                $this->pusher($thermostat->id, false, false, false, true);
                                $this->pusher($thermostat->id, false, false, false, false);
                                $this->pusher($thermostat->id, false, true, false, false);
                                $this->pusher($thermostat->id, false, true, false, true);
                            }

                        }
                    }

                    if ($mode > 2) {
                        $thermostat->status_mode = $mode;
                        $this->pusher($thermostat->id, false, false, false, true);
                        $this->pusher($thermostat->id, false, false, false, false);
                        $this->pusher($thermostat->id, false, true, false, false);
                      //  dump('Pusher for override');
                        $this->pusher($thermostat->id, false, true, false, true);
                    }


                    $thermostat->save();


                    //  dispatch((new PusherJob($thermostat->id, false, true))->onQueue('high'));
                } else {
                    if ($thermostat->isDirty()) {
                        $this->pusher($thermostat->id, false, true, false, false);
                        $this->pusher($thermostat->id, false, true, false, false);
                        $this->pusher($thermostat->id, false, true, false, false);
                        $thermostat->save();


                    }
                }


                //   dump('Thermostat Name: ' . $thermostat->room_name . ' Command name: ' . $command_name . ' Value: ' . $value);
                $this->checkLastCommand($thermostat->id, $command_name, $value);


                /*
                $command = Commands::where('thermostat_id', '=', $thermostat->id)
                    ->where('executed', '=', 0)
                    ->orderBy('id', 'desc')
                    ->first();


                if ($command) {
                    $this->checkLastCommand($command->thermostat_id, $command->command_name, $command->command_value, 2);
                }
                */


            }
        }

    }

    public
    function calcAddChecksum(
        $prepared_pack
    )
    {
        $preparedData = array_splice($prepared_pack, -3);
        $preparedData = array_splice($prepared_pack, 2);
        $calcu_checksum = 0;
        foreach ($preparedData as $rD) {
            $hexDec = hexdec($rD);
            $calcu_checksum ^= $hexDec;
        }

        return dechex($calcu_checksum);
    }

    public
    function tranSpecRegister(
        $reg_val
    )
    {
        if ($reg_val['offset_sign'] == 1) {
            $offset_temp = $reg_val['offset_temp'] + 127;
        } else {
            $offset_temp = $reg_val['offset_temp'];
        }
        $offset_bin = decbin($offset_temp);
        $offset_dec = bindec($offset_bin);

        return dechex($offset_dec);
    }


    public
    function setDailyStatistic(
        $relay_status,
        $thermostat_id
    )
    {

        $all_stats = Statistic::where('thermostat_id', $thermostat_id)->get()->count();


        $thermostat = Thermostat::where('id', '=', $thermostat_id)->first();


        if ($all_stats === 0) {

            if ($relay_status === 1) {

                $statistic = new Statistic();
                $statistic->thermostat_id = $thermostat_id;
                $statistic->relay_status = $relay_status;
                $statistic->on_date = Carbon::now();
                $statistic->off_date = null;
                $statistic->save();
            }
        } else {
            $statistic = Statistic::where('thermostat_id', $thermostat_id)
                ->orderBy('id', 'desc')
                ->first();

            if ($statistic) {
                if ($relay_status === 0 && $statistic->off_date === null) {

                    $statistic->off_date = Carbon::now();
                    $statistic->mat_power = $thermostat->mat_power;
                    $startTime = Carbon::parse($statistic->on_date);
                    $finishTime = Carbon::parse(Carbon::now());
                    $statistic->working_time = $finishTime->diffInSeconds($startTime);
                    $statistic->relay_status = 0;
                    $statistic->save();
                }
            }

            if ($relay_status === 1 && $statistic->off_date != null) {

                $statistic = new Statistic();
                $statistic->thermostat_id = $thermostat_id;
                $statistic->relay_status = $relay_status;
                $statistic->on_date = Carbon::now();
                $statistic->off_date = null;
                $statistic->save();
            }
        }


    }


    public function pusher($thermostat_id, $first_time = false, $temperature = false, $settings = false, $from_thermostat = false)
    {

        $options = [
            'cluster' => env('PUSHER_CLUSTER'),
            'useTLS' => true,
        ];
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );

        $thermostat = Thermostat::where('id', $thermostat_id)->first();

        if ($thermostat) {

            $data['thermostat'] = $thermostat;

            $property = $thermostat->properties->first();


            $subscription_count = 0;
            $info = $pusher->get_channel_info('channel-' . $property['id'], ['info' => 'subscription_count']);
            if ($info) {
                $subscription_count = $info->subscription_count;
            }

            $propertyInfo = $pusher->get_channel_info('property-' . $property['id'], ["info" => "subscription_count"]);
            $property_subscription_count = 0;
            if ($propertyInfo) {
                $property_subscription_count = $propertyInfo->subscription_count;
            }


            if ($property_subscription_count > 0) {
                //   dump('Property JOB PUSHER');
                dispatch((new PusherPropertyJob($property['id'], $thermostat_id))->onQueue('high'));
            }

            if ($first_time) {
                //   dump('PUSHER FOR FIRST TIME');
                dispatch((new PusherFirstTimeJob($thermostat_id))->onQueue('high'));
                dispatch((new PusherFirstTimeJob($thermostat_id))->delay(5));
                dispatch((new PusherFirstTimeJob($thermostat_id))->delay(10));
                dispatch((new PusherFirstTimeJob($thermostat_id))->delay(15));
                dispatch((new PusherFirstTimeJob($thermostat_id))->delay(20));
                dispatch((new PusherFirstTimeJob($thermostat_id))->delay(25));
            }

            if ($subscription_count > 0) {

                if ($temperature) {

                    dispatch((new PusherSetTempJob($thermostat_id, $from_thermostat))->onQueue('high'));
                } elseif ($settings) {
                    dump('Property SETTINGS PUSHER');
                    dispatch((new PusherSettingsJob($thermostat_id))->onQueue('high'));
                } else {
                    //  dump('Property OTHER PUSHER');
                    dump('treba tuka da pushti');
                    dispatch((new PusherThermostatJob($thermostat_id))->onQueue('high'));
                }
            }


        }
    }


    public
    function checkLastCommand($thermostatId, $command_name, $command_value, $delay = false)
    {
        $command = Commands::where('thermostat_id', '=', $thermostatId)
            ->where('command_name', '=', $command_name)
            ->orderBy('id', 'desc')
            ->first();


        if ($command) {


            //  dump('Thermostat Name: '.$thermostatId.' Name: '.$command_name.' Value: '.$command_value.' Comamnd Name: '.$command->command_name.' Command Value: '.$command->command_value);
            if ($command->executed === 0) {


                if ($command->command_value != $command_value) {
                    if ($command->retry < 3) {
                        if ($delay) {

                            dispatch((new ProcessCommands($command->command, $thermostatId))->delay($delay));
                        } else {
                            if ($command->command_name === 'boiler_duration') {
                                Artisan::call("command:scheduler-command",
                                    [
                                        'thermostat_id' => $thermostatId,
                                        'setCommand' => $command->command
                                    ]);
                            }
                            dispatch((new ProcessCommands($command->command, $thermostatId))->onQueue('high'));
                            $this->pusher($thermostatId, false, false, false, true);
                        }


                        $thermostat = Thermostat::find($thermostatId);
                        $command->retry = $command->retry + 1;
                        $command->signal_strength = $thermostat->signal_strength;
                        $command->save();
                        $this->checkLastCommand($command->thermostat_id, $command->command_name, $command->command_value);


                    } else {
                        $command->delete();
                    }
                } else {

                    if (($command_name === "set_temp") || ($command_name === "sched_temp") || ($command_name === "mode") || ($command_name === "boiler_duration")) {


                        $this->pusher($thermostatId, false, true);

                    } else {

                        $this->pusher($thermostatId);

                    }
                    $thermostat = Thermostat::find($thermostatId);
                    $command->signal_strength = $thermostat->signal_strength;
                    $command->executed = true;
                    $command->save();

                }
            }
        }

    }

    public
    function checkLastCommands(
        $thermostatId,
        $commands
    )
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


        $thermostat = Thermostat::find($thermostatId)->toArray();
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


        foreach ($commands as $key => $requ_val) {
            if (array_key_exists($key, $dataArray)) {
                $sendData = dechex($requ_val);
                $finalData = (strlen($sendData) == 1) ? "0" . $sendData : $sendData;
                $prepared_pack[$dataArray[$key]] = $finalData;
            }
        }


        $checkSum = $this->calcAddChecksum($prepared_pack);
        $checkSum = (strlen($checkSum) == 1) ? "0" . $checkSum : $checkSum;


        $prepared_pack[12] = "00"; // home router mac address
        $prepared_pack[26] = $checkSum;


        $trim_command = str_replace(" ", "", implode(" ", $prepared_pack));

        //  dump($trim_command);

    }




    public function checkIsMaster($thermostat_id)
    {
        $thermostat = Thermostat::Find($thermostat_id);
        $property = $thermostat->properties->first();



        if ($property->app_type != null) {
            foreach ($property->thermostats as $thermostat) {
                if ($thermostat->thermostat_type_id === 5) {
                    return $status = true;
                }
            }
        }

    }

    public function setSlavesOff($thermostat_id)
    {
        $thermostat = Thermostat::Find($thermostat_id);

        if($thermostat->thermostat_type_id === 5) {
            $property = $thermostat->properties->first();
            $property->app_type = 2;
            $property->save();

            foreach ($property->thermostats as $index => $therm) {
                if ($thermostat->id != $therm->id) {
                    $t = new ThermostatController($this);
                    $offCommand = $t->makeModeCommand(20, 0, 1, $therm->id);
                    dispatch((new ProcessCommands($offCommand, $therm->id))->delay($index));
                    dispatch((new ProcessCommands($offCommand, $therm->id))->delay($index + 1));
                }

            }
        }
    }


    public function setSlavesOn($thermostat_id)
    {
        $thermostat = Thermostat::Find($thermostat_id);

        if($thermostat->thermostat_type_id === 5) {
            $property = $thermostat->properties->first();
            $property->app_type = 2;
            $property->save();

            foreach ($property->thermostats as $index => $therm) {
                if ($thermostat->id != $therm->id) {
                    $t = new ThermostatController($this);
                    $offCommand = $t->makeModeCommand(20, 1, 1, $therm->id);
                    dispatch((new ProcessCommands($offCommand, $therm->id))->delay($index));
                    dispatch((new ProcessCommands($offCommand, $therm->id))->delay($index + 1));
                }

            }
        }
    }


}