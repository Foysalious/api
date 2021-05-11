<?php namespace Sheba\QueueMonitor;

use App\Jobs\Job;
use Carbon\Carbon;

abstract class MonitoredJob extends Job
{
    /** @var string  */
    public $createdAt;
    /** @var string */
    public $title;

    public function __construct()
    {
        $this->createdAt = Carbon::now()->toDateTimeString();
        $this->title = $this->getTitle();
    }

    abstract protected function getTitle();
}
