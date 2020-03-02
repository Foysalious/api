<?php namespace Sheba\Cache\CategoryGroup;

use Sheba\Cache\CacheFactory;
use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;

class CategoryGroupCacheFactory implements CacheFactory
{
    public function getCacheObject(CacheRequest $cacheRequest): CacheObject
    {
        $category_group_cache = new CategoryGroupCache();
        $category_group_cache->setCacheRequest($cacheRequest);

        return $category_group_cache;
    }

    public function getDataStoreObject(CacheRequest $cacheRequest): DataStoreObject
    {
        $data_store = new CategoryGroupDataStore();
        $data_store->setCacheRequest($cacheRequest);

        return $data_store;
    }
}
