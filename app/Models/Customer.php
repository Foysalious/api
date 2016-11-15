<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable {

    protected $fillable = [
        'mobile', 'remember_token', 'password', 'email', 'mobile_verified'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];


}
