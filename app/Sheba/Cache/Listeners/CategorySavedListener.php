<?php namespace Sheba\Cache\Listeners;

use Sheba\Cache\CacheAside;
use Sheba\Cache\Sitemap\SitemapCacheRequest;
use Sheba\Dal\Category\Events\CategorySaved as CategorySavedEvent;
use Sheba\Report\Listeners\BaseSavedListener;

class CategorySavedListener  extends BaseSavedListener
{
    public function handle(CategorySavedEvent $event)
    {
        /** @var CacheAside $cache_aside */
        $cache_aside = app(CacheAside::class);

        $sitemap_cache = new SitemapCacheRequest();
        $cache_aside->setCacheRequest($sitemap_cache)->deleteEntity();
    }
}