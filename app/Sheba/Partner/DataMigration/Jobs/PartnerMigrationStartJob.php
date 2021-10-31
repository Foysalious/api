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

    public function __construct($partner)
    {
        $this->partner = $partner;
        $this->connection = 'pos_rebuild_data_migration';
        $this->queue = 'pos_rebuild_data_migration';
    }

    public function handle()
    {
        $key = 'DataMigration::Partner::'.$this->partner->id;
        Redis::get($key);
    }
}