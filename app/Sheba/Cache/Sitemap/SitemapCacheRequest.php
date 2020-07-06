<?php


namespace Sheba\Cache\Sitemap;


use Sheba\Cache\CacheName;
use Sheba\Cache\CacheRequest;

class SitemapCacheRequest implements CacheRequest
{
    public function getFactoryName()
    {
        return CacheName::SITEMAP;
    }
}