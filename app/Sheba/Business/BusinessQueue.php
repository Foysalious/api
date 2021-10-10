<?php namespace App\Sheba\Business;

use App\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class BusinessQueue extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public function __construct()
    {
        $this->connection = 'business_notification';
        $this->queue = 'business_notification';
    }
}