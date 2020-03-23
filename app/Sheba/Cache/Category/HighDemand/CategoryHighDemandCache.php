<?php namespace Sheba\Cache\Category\HighDemand;

use Sheba\Cache\CacheName;
use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;

class CategoryHighDemandCache implements CacheObject
{
    /** @var CategoryHighDemandCacheRequest $categoryHighDemandCacheRequest*/
    private $categoryHighDemandCacheRequest;

    public function setCacheRequest(CacheRequest $cache_request)
    {
        $this->categoryHighDemandCacheRequest = $cache_request;
        return $this;
    }

    public function getCacheName(): string
    {
        return sprintf("%s::%s:%d", CacheName::HIGH_DEMAND_CATEGORY, 'location', $this->categoryHighDemandCacheRequest->getLocationId());
    }

    public function getExpirationTimeInSeconds(): int
    {
        // TODO: Implement getExpirationTimeInSeconds() method.
    }

    public function getAllKeysRegularExpression(): string
    {
        $location_id = $this->categoryHighDemandCacheRequest->getLocationId();
        return CacheName::HIGH_DEMAND_CATEGORY . "::location:" . ($location_id ? $location_id : "*");
    }
}
