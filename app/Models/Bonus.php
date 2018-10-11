<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['wallet' => 'double'];
}