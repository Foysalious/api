<?php namespace Sheba\QueueMonitor;

use App\Jobs\Job;
use Carbon\Carbon;

abstract class MonitoredJob extends Job
{
    /** @var Carbon  */
    public $createdAt;
    /** @var string */
    public $title;

    public function __construct()
    {
        $this->createdAt = Carbon::now();
        $this->title = $this->getTitle();
    }

    abstract protected function getTitle();
}
