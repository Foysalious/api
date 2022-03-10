<?php namespace Sheba\Recommendations\HighlyDemands\Categories;

use Cache;
use Illuminate\Contracts\Cache\Repository;

class CacheWrapper extends Recommender
{
    private $redisNameSpace = 'high_demand_category';

    protected function recommendation()
    {
        /** @var Repository $store */
        $store = Cache::store('redis');

        $cache_name = sprintf("%s::%s:%d", $this->redisNameSpace, 'location', $this->locationId);

        if ($store->has($cache_name)) return $store->get($cache_name);

        $this->next->locationId = $this->locationId;
        $data = $this->next->recommendation();

        $end_date_of_cached = $this->timeFrame->forSixMonthFromNow()->end;
        $store->put($cache_name, $data, $end_date_of_cached);

        return $data;
    }
}
