<?php namespace Sheba\Analysis\PartnerSale\Calculators;

use Cache;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository;
use Sheba\Analysis\PartnerSale\PartnerSale;

class CacheWrapper extends PartnerSale
{
    const redisNameSpace = 'PartnerSale';

    protected function calculate()
    {
        $store = Cache::store('redis'); /** @var Repository $store */

        $cache_name = $this->getCacheName();
        if ($store->has($cache_name)) {
            return $store->get($cache_name);
        } else {
            $data = $this->next->get();
            $this->store($data);
            return $data;
        }
    }

    public function store($data)
    {
        $store = Cache::store('redis');
        /** @var Repository $store */
        $cache_name = $this->getCacheName();
        $store->add($cache_name, $data, Carbon::tomorrow());
    }

    private function getCacheName()
    {
        /** @var Repository $store */
        $start = $this->timeFrame->start->format('Y-m-d');
        $end = $this->timeFrame->end->format('Y-m-d');
        $cache_name = sprintf("%s::%s_%s_%d_%s", self::redisNameSpace, $start, $end, $this->partner->id, $this->frequency);
        return $cache_name;
    }
}
