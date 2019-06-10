<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelLog extends Model
{

    protected $guarded = ['id',];
    protected $dates = ['refilled_date'];
    protected $table = 'fuel_logs';

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}