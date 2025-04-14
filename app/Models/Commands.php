<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commands extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'commands';

    protected $fillable = [
        'user_id',
        'thermostat_id',
        'command',
        'command_name',
        'command_value',
        'executed',
        'signal_strength'
    ];

}
