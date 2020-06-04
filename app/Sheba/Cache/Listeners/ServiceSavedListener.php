<?php namespace Sheba\Cache\Listeners;


use Sheba\Cache\CacheAside;
use Sheba\Cache\Sitemap\SitemapCacheRequest;
use Sheba\Dal\Service\Listeners\ServiceSaved as ServiceSavedEvent;
use Sheba\Report\Listeners\BaseSavedListener;

class ServiceSavedListener extends BaseSavedListener
{
    public function handle(ServiceSavedEvent $event)
    {
        /** @var CacheAside $cache_aside */
        $cache_aside = app(CacheAside::class);

        $sitemap_cache = new SitemapCacheRequest();
        $cache_aside->setCacheRequest($sitemap_cache)->deleteEntity();
    }
}