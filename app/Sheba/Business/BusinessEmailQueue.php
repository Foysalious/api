<?php namespace App\Sheba\Business;

use App\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BusinessEmailQueue extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public function __construct()
    {
        $this->connection = 'business_email';
        $this->queue = 'business_email';
    }
}
