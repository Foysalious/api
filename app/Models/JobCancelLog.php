<?php namespace App\Models;

use Sheba\Dal\BaseModel;
use Sheba\Dal\JobCancelLog\Events\JobCancelLogSaved;
use Sheba\Report\Updater\JobCancelLog as ReportUpdater;
use Sheba\Report\Updater\UpdatesReport;

class JobCancelLog extends BaseModel implements UpdatesReport
{
    use ReportUpdater;

    protected $guarded = ['id'];

    protected static $savedEventClass = JobCancelLogSaved::class;

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
