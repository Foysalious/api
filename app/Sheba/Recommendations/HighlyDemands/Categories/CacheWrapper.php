<?php namespace Sheba\Recommendations\HighlyDemands\Categories;

use Cache;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository;

class CacheWrapper extends Recommender
{
    private $redisNameSpace = 'HighDemandCategory';

    protected function recommendation()
    {
        /** @var Repository $store */
        $store = Cache::store('redis');

        $cache_name = sprintf("%s::%s_%d_data", $this->redisNameSpace, 'location', $this->locationId);

        if ($store->has($cache_name)) {
            return $store->get($cache_name);
        } else {
            $this->next->locationId = $this->locationId;
            $data = $this->next->recommendation();

            $end_date_of_cached = $this->timeFrame->forSixMonth(Carbon::now())->end;
            $expires_at = $end_date_of_cached->diffInMinutes(Carbon::now());
            $store->put($cache_name, $data, $expires_at);

            return $data;
        }
    }
}
