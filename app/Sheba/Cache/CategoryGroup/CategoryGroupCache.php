<?php namespace Sheba\Cache\CategoryGroup;

use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;

class CategoryGroupCache implements CacheObject
{
    /** @var CategoryGroupCacheRequest */
    private $categoryGroupCacheRequest;

    public function getCacheName(): string
    {
        return sprintf("%s::%d::%s::%d", $this->getRedisNamespace(), $this->categoryGroupCacheRequest->getCategoryGroupId(), 'location', $this->categoryGroupCacheRequest->getLocationId());
    }

    public function getRedisNamespace(): string
    {
        return 'category_group';
    }

    public function getExpirationTimeInSeconds(): int
    {
        return 30 * 24 * 60 * 60;
    }

    public function setCacheRequest(CacheRequest $cache_request)
    {
        $this->categoryGroupCacheRequest = $cache_request;
        return $this;
    }

    public function getAllKeysRegularExpression(): string
    {
        $category_group_id = $this->categoryGroupCacheRequest->getCategoryGroupId();
        $location_id = $this->categoryGroupCacheRequest->getLocationId();
        return "category_group::" . ($category_group_id ? $category_group_id : "*") . "::location::" . ($location_id ? $location_id : "*");
    }
}
