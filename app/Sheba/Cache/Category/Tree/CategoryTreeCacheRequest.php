<?php namespace Sheba\Cache\Category\Tree;


use Sheba\Cache\CacheName;
use Sheba\Cache\CacheRequest;

class CategoryTreeCacheRequest implements CacheRequest
{
    private $locationId;

    public function getLocationId()
    {
        return (int)$this->locationId;
    }


    public function setLocationId($locationId)
    {
        $this->locationId = $locationId;
        return $this;
    }

    public function getFactoryName()
    {
        return CacheName::CATEGORY_TREE;
    }
}