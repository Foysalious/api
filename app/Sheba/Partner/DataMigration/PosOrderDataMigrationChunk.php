<?php namespace Sheba\Partner\DataMigration;


use Illuminate\Support\Facades\Redis;
use Sheba\Partner\DataMigration\Jobs\PartnerDataMigrationToPosOrderChunkJob;
use Sheba\Pos\Repositories\PosOrderRepository;

class PosOrderDataMigrationChunk
{
    private $partner;
    const CHUNK_SIZE = 5000;
    private $currentQueue = 1;
    private $queue_and_connection_name;
    private $shouldQueue;
    private $orderCount;

    public function __construct(PosOrderRepository $posOrderRepository)
    {
        $this->posOrderRepository = $posOrderRepository;
    }

    /**
     * @param $partner
     * @return PosOrderDataMigrationChunk
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $queue_and_connection_name
     * @return PosOrderDataMigrationChunk
     */
    public function setQueueAndConnectionName($queue_and_connection_name)
    {
        $this->queue_and_connection_name = $queue_and_connection_name;
        return $this;
    }

    /**
     * @param mixed $shouldQueue
     * @return PosOrderDataMigrationChunk
     */
    public function setShouldQueue($shouldQueue)
    {
        $this->shouldQueue = $shouldQueue;
        return $this;
    }

    /**
     * @param mixed $orderCount
     * @return PosOrderDataMigrationChunk
     */
    public function setOrderCount($orderCount)
    {
        $this->orderCount = $orderCount;
        return $this;
    }

    public function generate()
    {
        $size = $this->orderCount < self::CHUNK_SIZE ? 1 : ceil($this->orderCount / self::CHUNK_SIZE);
        for ($i = 0; $i < $size; $i++) {
            $this->setRedisKey();
            $this->shouldQueue ? dispatch(new PartnerDataMigrationToPosOrderChunkJob($i * self::CHUNK_SIZE, self::CHUNK_SIZE, $this->partner, $this->currentQueue, $this->queue_and_connection_name, $this->shouldQueue)) :
                dispatchJobNow(new PartnerDataMigrationToPosOrderChunkJob($i * self::CHUNK_SIZE, self::CHUNK_SIZE, $this->partner, $this->currentQueue, $this->queue_and_connection_name, $this->shouldQueue));
            $this->increaseCurrentQueueValue();
        }
    }

    private function setRedisKey()
    {
        $count = (int)Redis::get('PosOrderDataMigrationCount::' . $this->queue_and_connection_name);
        $count ? $count++ : $count = 1;
        Redis::set('PosOrderDataMigrationCount::' . $this->queue_and_connection_name, $count);
        Redis::set('DataMigration::Partner::' . $this->partner->id . '::PosOrderChunk::Queue::' . $this->currentQueue, 'initiated');
    }

    private function increaseCurrentQueueValue()
    {
        $this->currentQueue += 1;
    }


}