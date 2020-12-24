<?php namespace Sheba\Cache\CategoryGroup;


use Sheba\Cache\CacheName;
use Sheba\Cache\CacheRequest;

class CategoryGroupCacheRequest implements CacheRequest
{
    private $categoryGroupId;
    private $locationId;

    public function setLocationId($location_id)
    {
        $this->locationId = $location_id;
        return $this;
    }

    public function setCategoryGroupId($categoryGroupId)
    {
        $this->categoryGroupId = $categoryGroupId;
        return $this;
    }


    public function getCategoryGroupId()
    {
        return (int)$this->categoryGroupId;
    }

    public function getLocationId()
    {
        return (int)$this->locationId;
    }

    public function getFactoryName()
    {
        return CacheName::CATEGORY_GROUP;
    }
}