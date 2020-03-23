<?php namespace Sheba\Cache\Category\Review;

use Sheba\Cache\CacheFactory;
use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;

class ReviewCacheFactory implements CacheFactory
{

    public function getCacheObject(CacheRequest $cacheRequest): CacheObject
    {
        $review_cache = new ReviewCache();
        $review_cache->setCacheRequest($cacheRequest);
        return $review_cache;
    }

    public function getDataStoreObject(CacheRequest $cacheRequest): DataStoreObject
    {
        $review_data_store = new ReviewDataStore();
        $review_data_store->setCacheRequest($cacheRequest);
        return $review_data_store;
    }
}