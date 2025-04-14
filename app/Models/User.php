<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Laravel\Passport\HasApiTokens;
use App\Models\Countries;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'lang',
        'email',
        'phone',
        'role_id',
        'country',
        'password',
        'code'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];


    public function thermostats()
    {
        return $this->belongsToMany('App\Models\Thermostat');
    }

    public function properties()
    {
        return $this->belongsToMany('App\Models\Properties');
    }

    public function role()
    {
        return $this->belongsTo(
            'App\Models\UserRole',
            'role_id',
            'id'
        );
    }

    public function country()
    {
        return $this->belongsTo(
            'App\Models\Countries',
            'country',
            'id'
        );
    }

    public function getCountryISO($id)
    {
        $country = Countries::where('id', '=', $id)->first();
        return $country->iso_3166_2;
    }

}
