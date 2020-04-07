<?php namespace Sheba\Cache\Category\Children\Services;

use Sheba\Cache\CacheFactory;
use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;

class ServicesCacheFactory implements CacheFactory
{
    public function getCacheObject(CacheRequest $cacheRequest): CacheObject
    {
        $cache = new ServicesCache();
        $cache->setCacheRequest($cacheRequest);
        return $cache;
    }

    public function getDataStoreObject(CacheRequest $cacheRequest): DataStoreObject
    {
        /** @var ServicesDataStore $data */
        $data = app(ServicesDataStore::class);
        $data->setCacheRequest($cacheRequest);
        return $data;
    }
}
