<?php namespace App\Models\Transport;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class BusRouteLocation extends Eloquent
{
    protected $connection = 'mongodb_atlas_conn';
    protected $fillable = ['name', 'bd_ticket_id', 'pekhom_id'];
}