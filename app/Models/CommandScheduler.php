<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommandScheduler extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'scheduler_commands';

    protected $fillable = [
        'user_id',
        'thermostat_id',
        'command',
        'start_time',
        'end_time',
        'day',
        'end_day',
        'command_name',
        'command_value',
        'time_zone'
    ];

}
