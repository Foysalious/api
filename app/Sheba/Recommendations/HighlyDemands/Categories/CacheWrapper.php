<?php namespace Sheba\Recommendations\HighlyDemands\Categories;

use Cache;
use Illuminate\Contracts\Cache\Repository;

class CacheWrapper extends Recommender
{
    private $redisNameSpace = 'HighDemandCategory';

    protected function recommendation()
    {
        /** @var Repository $store */
        $store = Cache::store('redis');

        $cache_name = sprintf("%s::%d_%d_%d_data", $this->redisNameSpace, $this->year, $this->month, $this->day);

        if ($store->has($cache_name)) {
            return $store->get($cache_name);
        } else {
            $this->next->locationId = $this->locationId;
            $data = $this->next->recommendation();
            $store->forever($cache_name, $data);
            return $data;
        }
    }
}
