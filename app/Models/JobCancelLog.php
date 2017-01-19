<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobCancelLog extends Model
{
    protected $guarded = ['id'];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
