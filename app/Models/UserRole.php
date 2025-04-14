<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = 'user_roles';

    protected $fillable = [
        'name',
        'acl'
    ];

    protected $hidden = [];

    public function users()
    {
        return $this->hasMany(
            'App\Models\UserRole',
            'role_id',
            'id'
        );
    }
}