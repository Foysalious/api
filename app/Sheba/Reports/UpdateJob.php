<?php namespace Sheba\Reports;

use App\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class UpdateJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public function __construct()
    {
        $this->connection = 'report';
        $this->queue = 'report';
    }
}