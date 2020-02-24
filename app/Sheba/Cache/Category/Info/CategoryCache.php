<?php namespace Sheba\Cache\Category\Info;


use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;

class CategoryCache implements CacheObject
{
    /** @var CategoryCacheRequest */
    private $categoryCacheRequest;

    public function setCacheRequest(CacheRequest $cache_request)
    {
        $this->categoryCacheRequest = $cache_request;
        return $this;
    }

    public function getCacheName(): string
    {
        return sprintf("%s::%d", 'category', $this->categoryCacheRequest->getCategoryId());
    }

    public function getExpirationTimeInSeconds(): int
    {
        return 24 * 60 * 60;
    }

    public function getAllKeysRegularExpression(): string
    {
        $category_id = $this->categoryCacheRequest->getCategoryId();
        return "::category::" . ($category_id ? $category_id : "*");
    }
}