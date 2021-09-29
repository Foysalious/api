<?php namespace Sheba\Cache\Listeners;

use Sheba\Cache\CacheAside;
use Sheba\Cache\Sitemap\SitemapCacheRequest;
use Sheba\Dal\UniversalSlug\Events\UniversalSlugSaved as UniversalSlugSavedEvent;

class UniversalSlugSavedListener
{
    public function handle(UniversalSlugSavedEvent $event)
    {
        /** @var CacheAside $cache_aside */
        $cache_aside = app(CacheAside::class);

        $sitemap_cache = new SitemapCacheRequest();
        $cache_aside->setCacheRequest($sitemap_cache)->deleteEntity();
    }
}
