<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Properties extends Model
{

    private $status = [];

    protected $table = 'properties';

    protected $fillable
        = [
            'name',
            'status_all',
            'app_type',
            'square_measure',
            'square_meters',
            'number_of_therm',
            'temp_unit',
            'heating_rate',
            'address',
            'lat',
            'lng',
            'user_id',
            'app_name'
        ];

    public function user()
    {
        return $this->hasOne('App\Models\User');
    }

    public function thermostats()
    {
        return $this->belongsToMany('App\Models\Thermostat');
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }

    public function firstSetup()
    {
        return $this->hasOne('App\Models\FirstSetUpThermostat', 'property_id', 'id')->orderBy('id', 'desc')->get()->unique('mac_address')
            ->toArray();
    }

    public function status_all()
    {
        $mode = $this->thermostats()->select('mode')->get();
        $on = 0;
        foreach ($mode as $m) {
            if ($m->mode != 0) {
                $on++;
            }
        }

        return $on === 0 ? 0 : 1;

    }

}
