<?php namespace Sheba\Recommendations\HighlyDemands\Categories;

use Cache;
use Illuminate\Contracts\Cache\Repository;

class CacheWrapper extends Recommender
{
    private $redisNameSpace = 'HighDemandCategory';

    protected function recommendation()
    {
        $store = Cache::store('redis'); /** @var Repository $store */

        $cache_name = sprintf("%s::%d_%d_%d_data", $this->redisNameSpace, $this->year, $this->month, $this->day);

        if ($store->has($cache_name)) {
            return $store->get($cache_name);
        } else {
            $data = $this->next->recommendation();
            $store->forever($cache_name, $data);
            return $data;
        }
    }
}
