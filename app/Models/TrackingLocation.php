<?php namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class TrackingLocation extends Eloquent
{
    protected $connection = 'mongodb';
    protected $collection = 'tracking_locations';
    protected $table = 'tracking_locations';

    protected $fillable = [
        'business_id', 'business_member_id', 'location', 'log', 'date', 'time', 'created_at'
    ];

    public function businessMember()
    {
        return $this->belongsTo(BusinessMember::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function getLocationAttribute($location)
    {
        return json_decode($location);
    }
}