<?php namespace Sheba\Cache\Category\Review;


use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;

class ReviewCache implements CacheObject
{
    /** @var ReviewCacheRequest */
    private $reviewCacheRequest;

    public function getCacheName(): string
    {
        return sprintf("%s::%s::%d", $this->getRedisNamespace(), 'category', $this->reviewCacheRequest->getCategoryId());
    }

    public function getRedisNamespace(): string
    {
        return 'reviews';
    }

    public function getExpirationTimeInSeconds(): int
    {
        return 7 * 24 * 60 * 60;
    }

    public function setCacheRequest(CacheRequest $cache_request)
    {
        $this->reviewCacheRequest = $cache_request;
        return $this;
    }

    public function getAllKeysRegularExpression(): string
    {
        $category_id = $this->reviewCacheRequest->getCategoryId();
        return $this->getRedisNamespace() . "::category::" . ($category_id ? $category_id : "*");
    }
}