<?php namespace Sheba\Cache\Category\HighDemand;

use Sheba\Cache\CacheName;
use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;

class CategoryHighDemandDataStore implements DataStoreObject
{
    /** @var CacheRequest $categoryHighDemandCacheRequest */
    private $categoryHighDemandCacheRequest;

    public function setCacheRequest(CacheRequest $cache_request)
    {
        $this->categoryHighDemandCacheRequest = $cache_request;
        return $this;
    }

    public function generate()
    {
        // TODO: Implement generate() method.
    }
}
