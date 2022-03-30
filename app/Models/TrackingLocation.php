<?php namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class TrackingLocation extends Eloquent
{
    protected $connection = 'mongodb2';

    protected $fillable = [
        'business_id', 'business_member_id', 'location', 'log', 'dateTime'
    ];

}