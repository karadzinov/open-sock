<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SchedulerCommand extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */

    public $requestTimezone = 'Asia/Jerusalem';

    public function toArray($request)
    {

        $this->getTimeZone();


        $timeStart = explode(':', $this->start_time);
        $timeEnd = explode(':', $this->end_time);


        $start_time = \Carbon\Carbon::createFromTime($timeStart[0], $timeStart[1], 0, 'Europe/Skopje');

        $this->start_time = $start_time->timezone($this->requestTimezone)->format('H:i');


        $timeEnd = \Carbon\Carbon::createFromTime($timeEnd[0], $timeEnd[1], 0, 'Europe/Skopje');

        $this->end_time = $timeEnd->timezone($this->requestTimezone)->format('H:i');

        $timeStart = explode(':', $this->start_time);
        $timeEnd = explode(':', $this->end_time);


        $retunData  = [
                'start_hour'    => (int)$timeStart[0],
                'start_minute'  => (int)$timeStart[1],
                'end_hour'    => 0,
                'end_minute'  => 0,
                'user_id'       => $this->user_id,
                'thermostat_id' => $this->thermostat_id,
                'command_name'  => $this->command_name,
                'command_value' => $this->command_value,
                'day'           => $this->day,
                'end_day'       => $this->end_day
            ];

        if(count($timeEnd) > 1 ){
            $retunData['end_hour'] = (int)$timeEnd[0];
            $retunData['end_minute']  = (int)$timeEnd[1];
        }

        return $retunData;

    }

    public function getTimeZone()
    {
        try {
            $record = app()->geoip->getIp();
            $record = app()->geoip->getLocation($record);

            $cc = json_encode($record->location);
            $cc = json_decode($cc);
            $timezone = $cc->time_zone;

        } catch (\Exception $e) {
            $timezone = 'Asia/Jerusalem';
        }

        $this->requestTimezone = $timezone;
    }
}
