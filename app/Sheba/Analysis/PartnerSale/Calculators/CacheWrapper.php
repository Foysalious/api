<?php namespace Sheba\Analysis\PartnerSale\Calculators;

use Cache;
use Illuminate\Support\Collection;
use Sheba\Analysis\PartnerSale\PartnerSale;

class CacheWrapper extends PartnerSale
{
    private $redisNameSpace = 'PartnerSale';

    protected function calculate()
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