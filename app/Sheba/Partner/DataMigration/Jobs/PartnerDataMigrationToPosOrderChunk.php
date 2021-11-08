<?php namespace Sheba\Partner\DataMigration\Jobs;


use App\Jobs\Job;
use App\Sheba\Partner\DataMigration\PosOrderDataMigration;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class PartnerDataMigrationToPosOrderChunk extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $skip;
    private $take;
    private $partner;
    private $queueNo;

    public function __construct($skip, $take, $partner, $queueNo)
    {
        $this->skip = $skip;
        $this->take = $take;
        $this->partner = $partner;
        $this->queueNo = $queueNo;
    }


    public function handle()
    {
        $redis_pos_order_namespace = 'DataMigration::Partner::'.$this->partner->id.'::PosOrderChunk::Queue::';
        $previous_key = $redis_pos_order_namespace . ($this->queueNo - 1);
        if ($this->isInventoryQueuesProcessed() && !$this->isRedisKeyExists($previous_key)) {
            /** @var PosOrderDataMigration $posOrderDataMigration */
            $posOrderDataMigration = app(PosOrderDataMigration::class);
            $posOrderDataMigration->setPartner($this->partner)->setSkip($this->skip)->setTake($this->take)->migrate();
        }else {
            $this->release(10);
        }
    }

    private function isInventoryQueuesProcessed(): bool
    {
        return empty(Redis::keys('DataMigration::Partner::' . $this->partner->id . '::Inventory::Queue::*'));
    }

    private function isRedisKeyExists($key): bool
    {
        $key = Redis::get($key);
        return (bool)$key;
    }

    private function deleteRedisKey($key)
    {
        Redis::del($key);
    }
}