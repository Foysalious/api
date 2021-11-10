<?php namespace Sheba\Partner\DataMigration;


use App\Models\PosOrder;
use Illuminate\Support\Facades\Redis;
use Sheba\Partner\DataMigration\Jobs\PartnerDataMigrationToPosOrderChunkJob;
use Sheba\Partner\DataMigration\Jobs\PartnerMigrationCompleteJob;
use Sheba\Pos\Repositories\PosOrderRepository;

class PosOrderDataMigrationChunk
{
    private $partner;
    const CHUNK_SIZE = 5000;
    /**
     * @var PosOrderRepository
     */
    private $posOrderRepository;
    private $currentQueue = 1;
    private $queue_and_connection_name;

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

    public function generate()
    {
        $posOrderCount = PosOrder::withTrashed()->where('partner_id', $this->partner->id)->where(function ($q) {
            $q->where('is_migrated', null)->orWhere('is_migrated', 0);
        })->count();
        $size = $posOrderCount < self::CHUNK_SIZE ? 1 : ceil($posOrderCount / self::CHUNK_SIZE);
        for ($i = 0; $i < $size; $i++) {
            $this->setRedisKey();
            dispatch(new PartnerDataMigrationToPosOrderChunkJob($i * self::CHUNK_SIZE, self::CHUNK_SIZE, $this->partner, $this->currentQueue, $this->queue_and_connection_name));
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