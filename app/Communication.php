<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class Communication extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;


    protected $table = 'communicationLog';

    protected $dates = ['created_at', 'updated_at'];
      
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'address',
        'data',
        'bytes',
        'message',
    ];
}
