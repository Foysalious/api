<?php namespace Sheba\Cache\Location;


use Sheba\Cache\CacheName;
use Sheba\Cache\CacheRequest;

class LocationCacheRequest implements CacheRequest
{
    public function getFactoryName()
    {
        return CacheName::LOCATIONS;
    }
}