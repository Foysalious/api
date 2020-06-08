<?php namespace Sheba\Cache\Sitemap;


use Sheba\Cache\CacheFactory;
use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;

class SitemapCacheFactory implements CacheFactory
{

    public function getCacheObject(CacheRequest $cacheRequest): CacheObject
    {
        return new SitemapCache();
    }

    public function getDataStoreObject(CacheRequest $cacheRequest): DataStoreObject
    {
        return new SitemapDataStore();
    }
}