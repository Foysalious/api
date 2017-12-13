<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobMaterialLog extends Model
{
    public $timestamps = false;
    protected $dates = ['created_at'];
    protected $guarded = ['id'];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function getOldDataAttribute($data)
    {
        return json_decode($data);
    }

    public function getNewDataAttribute($data)
    {
        return json_decode($data);
    }
}
