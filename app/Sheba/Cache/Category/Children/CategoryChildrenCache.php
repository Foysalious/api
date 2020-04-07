<?php namespace App\Sheba\Cache\Category\Children;

use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\Category\Children\CategoryChildrenCacheRequest;

class CategoryChildrenCache implements CacheObject
{
    /** @var CategoryChildrenCacheRequest */
    private $categoryChildrenCacheRequest;

    public function setCacheRequest(CacheRequest $cache_request)
    {
        $this->categoryChildrenCacheRequest = $cache_request;
        return $this;
    }

    public function getCacheName(): string
    {
        return sprintf("%s::%d::%s::%d", 'category_children', $this->categoryChildrenCacheRequest->getCategoryId(), 'location', $this->categoryChildrenCacheRequest->getLocationId());
    }

    public function getExpirationTimeInSeconds(): int
    {
        return 24 * 60 * 60;
    }

    public function getAllKeysRegularExpression(): string
    {
        $category_id = $this->categoryChildrenCacheRequest->getCategoryId();
        $location_id = $this->categoryChildrenCacheRequest->getLocationId();

        return "category_children::" . ($category_id ? $category_id : "*") . "::location::" . ($location_id ? $location_id : "*");
    }
}
