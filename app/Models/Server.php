<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'servers';

    protected $fillable = [
        'ip_address',
        'port',
        'host',
        'description'
    ];


}
