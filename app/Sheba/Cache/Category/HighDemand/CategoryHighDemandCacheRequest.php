<?php namespace Sheba\Cache\Category\HighDemand;

use Sheba\Cache\CacheName;
use Sheba\Cache\CacheRequest;

class CategoryHighDemandCacheRequest implements CacheRequest
{
    /** @var int $locationId */
    private $locationId;

    public function getFactoryName()
    {
        return CacheName::HIGH_DEMAND_CATEGORY;
    }

    public function getLocationId()
    {
        return $this->locationId;
    }

    public function setLocationId($location_id)
    {
        $this->locationId = (int)$location_id;
        return $this;
    }
}
