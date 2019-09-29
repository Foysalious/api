<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPartnerChangeLog extends Model
{
    protected $guarded = ['id'];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
