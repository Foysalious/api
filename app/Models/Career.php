<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    protected $guarded = ['id'];
    protected $hidden = [
        'created_by',
        'created_by_name',
        'updated_by',
        'updated_by_name',
        'created_at',
        'updated_at'
    ];
}
