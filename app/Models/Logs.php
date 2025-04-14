<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Logs extends Model
{
    protected $table = 'logs';

    protected $fillable = [
        'user_id',
        'log',
        'file_name'
    ];
}
