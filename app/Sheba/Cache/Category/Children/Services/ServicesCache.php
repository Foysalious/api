<?php namespace Sheba\Cache\Category\Children\Services;


use Sheba\Cache\CacheName;
use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;

class ServicesCache implements CacheObject
{
    /** @var ServicesCacheRequest */
    private $servicesCacheRequest;

    public function setCacheRequest(CacheRequest $cache_request)
    {
        $this->servicesCacheRequest = $cache_request;
        return $this;
    }

    public function getCacheName(): string
    {
        return sprintf("%s::%d::%s::%d", CacheName::SECONDARY_CATEGORY_SERVICES, $this->servicesCacheRequest->getCategoryId(), 'location', $this->servicesCacheRequest->getLocationId());

    }

    public function getExpirationTimeInSeconds(): int
    {
        return 2 * 60 * 60;
    }

    public function getAllKeysRegularExpression(): string
    {
        $category_id = $this->servicesCacheRequest->getCategoryId();
        $location_id = $this->servicesCacheRequest->getLocationId();
        return CacheName::SECONDARY_CATEGORY_SERVICES . "::" . ($category_id ? $category_id : "*") . "::location::" . ($location_id ? $location_id : "*");

    }
}