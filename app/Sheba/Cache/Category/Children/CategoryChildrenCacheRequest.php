<?php namespace Sheba\Cache\Category\Children;


use Sheba\Cache\CacheName;
use Sheba\Cache\CacheRequest;

class CategoryChildrenCacheRequest implements CacheRequest
{
    private $categoryId;
    private $locationId;
    
    public function getCategoryId()
    {
        return $this->categoryId;
    }


    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
        return $this;
    }


    public function getLocationId()
    {
        return $this->locationId;
    }

    public function setLocationId($locationId)
    {
        $this->locationId = $locationId;
        return $this;
    }

    public function getFactoryName()
    {
        return CacheName::CATEGORY_CHILDREN;
    }
}