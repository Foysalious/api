<?php namespace Sheba\Cache\Location;

use Sheba\Cache\CacheFactory;
use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;

class LocationCacheFactory implements CacheFactory
{

    public function getCacheObject(CacheRequest $cacheRequest): CacheObject
    {
        return new LocationCache();
    }

    public function getDataStoreObject(CacheRequest $cacheRequest): DataStoreObject
    {
        return new LocationDataStoreObject();
    }
}