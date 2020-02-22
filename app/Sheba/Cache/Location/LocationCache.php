<?php namespace Sheba\Cache\Location;

use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;

class LocationCache implements CacheObject
{
    public function getCacheName(): string
    {
        return sprintf("%s::%s", $this->getRedisNamespace(), 'v3');
    }

    public function getRedisNamespace(): string
    {
        return 'locations';
    }

    public function getExpirationTimeInSeconds(): int
    {
        return 24 * 60 * 60;
    }

    public function setCacheRequest(CacheRequest $cache_request)
    {
        // TODO: Implement setCacheRequest() method.
    }
}