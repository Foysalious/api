<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Report\Updater\JobCancelLog as ReportUpdater;

class JobCancelLog extends Model
{
    use ReportUpdater;

    protected $guarded = ['id'];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
