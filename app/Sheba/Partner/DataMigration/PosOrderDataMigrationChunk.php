<?php namespace Sheba\Partner\DataMigration;


use App\Models\PosOrder;
use Illuminate\Support\Facades\Redis;
use Sheba\Partner\DataMigration\Jobs\PartnerDataMigrationToPosOrderChunk;

class PosOrderDataMigrationChunk
{
    private $partner;
    const CHUNK_SIZE = 5000;
    private $currentQueue = 1;

    /**
     * @param $partner
     * @return PosOrderDataMigrationChunk
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function generate()
    {
        $posOrderCount = PosOrder::withTrashed()->where('partner_id', $this->partner->id)->count();
        $size =  $posOrderCount < self::CHUNK_SIZE ? 1 : ceil($posOrderCount/self::CHUNK_SIZE);
        for($i=0; $i < $size; $i++) {
            $this->setRedisKey();
            dispatch(new PartnerDataMigrationToPosOrderChunk($i, self::CHUNK_SIZE, $this->partner, $this->currentQueue));
            $this->increaseCurrentQueueValue();
        }
    }

    private function setRedisKey()
    {
        Redis::set('DataMigration::Partner::' . $this->partner->id . '::PosOrderChunk::Queue::' . $this->currentQueue, 'initiated');
    }

    private function increaseCurrentQueueValue()
    {
        $this->currentQueue += 1;
    }


}