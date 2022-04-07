<?php namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class TrackingLocation extends Eloquent
{
    protected $connection = 'mongodb';
    public $timestamps = true;
    protected $dates = ['date', 'created_at'];

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
