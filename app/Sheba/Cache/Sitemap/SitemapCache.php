<?php namespace Sheba\Cache\Sitemap;

use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;

class SitemapCache implements CacheObject
{

    public function setCacheRequest(CacheRequest $cache_request)
    {
        // TODO: Implement setCacheRequest() method.
    }

    public function getCacheName(): string
    {
        return sprintf("%s::%s", $this->getRedisNamespace(), 'v3');
    }

    public function getRedisNamespace(): string
    {
        return 'sitemap';
    }

    public function getExpirationTimeInSeconds(): int
    {
        return 24 * 60 * 60;
    }

    public function getAllKeysRegularExpression(): string
    {
        return $this->getRedisNamespace() . "::*";
    }
}