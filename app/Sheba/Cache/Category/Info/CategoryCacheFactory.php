<?php namespace Sheba\Cache\Category\Info;


use Sheba\Cache\CacheFactory;
use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;

class CategoryCacheFactory implements CacheFactory
{

    public function getCacheObject(CacheRequest $cacheRequest): CacheObject
    {
        $cache = new CategoryCache();
        $cache->setCacheRequest($cacheRequest);
        return $cache;
    }

    public function getDataStoreObject(CacheRequest $cacheRequest): DataStoreObject
    {
        $data = new CategoryDataStore();
        $data->setCacheRequest($cacheRequest);
        return $data;
    }
}