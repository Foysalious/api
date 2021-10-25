<?php namespace Sheba\Cache\Listeners;

use Sheba\Dal\Service\Service;
use Sheba\Cache\CacheAside;
use Sheba\Cache\Category\Children\CategoryChildrenCacheRequest;
use Sheba\Cache\Category\Children\Services\ServicesCacheRequest;
use Sheba\Cache\Category\Info\CategoryCacheRequest;
use Sheba\Cache\Category\Tree\CategoryTreeCacheRequest;
use Sheba\Cache\Sitemap\SitemapCacheRequest;
use Sheba\Dal\Service\Events\ServiceSaved as ServiceSavedEvent;

class ServiceSavedListener
{
    public function handle(ServiceSavedEvent $event)
    {
        /** @var Service $service */
        $service = $event->model;
        /** @var CacheAside $cache_aside */
        $cache_aside = app(CacheAside::class);

        $sitemap_cache = new SitemapCacheRequest();
        $cache_aside->setCacheRequest($sitemap_cache)->deleteEntity();

        $category_tree_cache = new CategoryTreeCacheRequest();
        $cache_aside->setCacheRequest($category_tree_cache)->deleteEntity();

        $category_cache = new CategoryCacheRequest();
        $category_cache->setCategoryId($service->category_id);
        $cache_aside->setCacheRequest($category_cache)->deleteEntity();

        $category_children = new CategoryChildrenCacheRequest();
        $category_children->setCategoryId($service->category_id);
        $cache_aside->setCacheRequest($category_children)->deleteEntity();

        $services_cache = new ServicesCacheRequest();
        $services_cache->setServiceId($service->id);
        $cache_aside->setCacheRequest($services_cache)->deleteEntity();
    }
}