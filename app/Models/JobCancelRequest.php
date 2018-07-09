<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobCancelRequest extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['approved_at'];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
