<?php namespace Sheba\Cache\Category\HighDemand;

use Sheba\Cache\CacheFactory;
use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;

class CategoryHighDemandCacheFactory implements CacheFactory
{
    public function getCacheObject(CacheRequest $cacheRequest): CacheObject
    {
        $cache = new CategoryHighDemandCache();
        $cache->setCacheRequest($cacheRequest);
        return $cache;
    }

    public function getDataStoreObject(CacheRequest $cacheRequest): DataStoreObject
    {
        $data = new CategoryHighDemandDataStore();
        $data->setCacheRequest($cacheRequest);
        return $data;
    }
}
