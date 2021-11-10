<?php namespace Sheba\Partner\DataMigration\Jobs;

use App\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class PartnerMigrationStartJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $partner;

    public function __construct($partner, $queue_and_connection_name)
    {
        $this->partner = $partner;
        $this->connection = $queue_and_connection_name;
        $this->queue = $queue_and_connection_name;
    }

    public function handle()
    {
        $key = 'DataMigration::Partner::'.$this->partner->id;
        Redis::del($key);
    }
}