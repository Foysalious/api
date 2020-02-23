<?php namespace Sheba\Cache\Category\Info;


use Sheba\Cache\CacheName;
use Sheba\Cache\CacheRequest;

class CategoryCacheRequest implements CacheRequest
{
    private $categoryId;


    public function getCategoryId()
    {
        return $this->categoryId;
    }

    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    public function getFactoryName()
    {
        return CacheName::CATEGORY;
    }
}