<?php namespace Sheba\Cache\Category\Review;


use Sheba\Cache\CacheName;
use Sheba\Cache\CacheRequest;

class ReviewCacheRequest implements CacheRequest
{
    private $categoryId;

    public function setCategoryId($category_id)
    {
        $this->categoryId = $category_id;
        return $this;
    }

    public function getCategoryId()
    {
        return $this->categoryId;
    }

    public function getFactoryName()
    {
        return CacheName::CATEGORY_REVIEWS;
    }
}