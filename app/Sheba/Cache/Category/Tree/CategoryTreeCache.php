<?php namespace Sheba\Cache\Category\Tree;


use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;

class CategoryTreeCache implements CacheObject
{
    /** @var CategoryTreeCacheRequest */
    private $categoryTreeCacheRequest;

    public function getCacheName(): string
    {
        return sprintf("%s::%s::%d", $this->getRedisNamespace(), 'location', $this->categoryTreeCacheRequest->getLocationId());
    }

    public function getRedisNamespace(): string
    {
        return 'category_tree';
    }

    public function getExpirationTimeInSeconds(): int
    {
        return 1 * 60 * 60;
    }

    public function setCacheRequest(CacheRequest $cache_request)
    {
        $this->categoryTreeCacheRequest = $cache_request;
    }

    public function getAllKeysRegularExpression(): string
    {
        $location_id = $this->categoryTreeCacheRequest->getLocationId();
        return $this->getRedisNamespace() . "::location::" . ($location_id ? $location_id : "*");
    }
}