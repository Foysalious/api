<?php namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class BusRouteLocation extends Eloquent
{
    protected $connection = 'mongodb';
    protected $fillable = ['name', 'bus_bd_id', 'pekhom_id'];
}