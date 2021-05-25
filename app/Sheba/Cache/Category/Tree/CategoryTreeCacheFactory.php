<?php namespace Sheba\Cache\Category\Tree;


use App\Sheba\Cache\Category\Tree\CategoryTreeDataStore;
use Sheba\Cache\CacheFactory;
use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;

class CategoryTreeCacheFactory implements CacheFactory
{

    public function getCacheObject(CacheRequest $cacheRequest): CacheObject
    {
        $tree_cache = new CategoryTreeCache();
        $tree_cache->setCacheRequest($cacheRequest);
        return $tree_cache;
    }

    public function getDataStoreObject(CacheRequest $cacheRequest): DataStoreObject
    {
        $tree_data_store = app(CategoryTreeDataStore::class);
        $tree_data_store->setCacheRequest($cacheRequest);
        return $tree_data_store;
    }
}