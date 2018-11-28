<?php namespace Sheba\Analysis\PartnerPerformance\Calculators;

use Cache;
use Sheba\Analysis\PartnerPerformance\PartnerPerformance;

class CacheWrapper extends PartnerPerformance
{
    private $redisNameSpace = 'PartnerPerformance';

    protected function get()
    {
        $store = Cache::store('redis'); /** @var \Illuminate\Contracts\Cache\Repository $store */

        $cache_name = sprintf("%s::%d_%d_%s_%s_%s_%s_data", $this->redisNameSpace);

        if ($store->has($cache_name)) {
            return $store->get($cache_name);
        } else {
            $data = $this->next->get();
            $store->forever($cache_name, $data);
            return $data;
        }
    }
}