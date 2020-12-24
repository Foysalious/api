<?php namespace Sheba\Cache\Category\Children;


use App\Sheba\Cache\Category\Children\CategoryChildrenCache;
use Sheba\Cache\CacheFactory;
use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;

class CategoryChildrenCacheFactory implements CacheFactory
{

    public function getCacheObject(CacheRequest $cacheRequest): CacheObject
    {
        $cache = new CategoryChildrenCache();
        $cache->setCacheRequest($cacheRequest);
        return $cache;
    }

    public function getDataStoreObject(CacheRequest $cacheRequest): DataStoreObject
    {
        $data = new CategoryChildrenDataStore();
        $data->setCacheRequest($cacheRequest);
        return $data;
    }
}