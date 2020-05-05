<?php namespace Sheba\Cache\Listeners;

use App\Models\LocationService;
use App\Models\Service;
use Sheba\Cache\CacheAside;
use Sheba\Cache\Category\Children\CategoryChildrenCacheRequest;
use Sheba\Cache\Category\Children\Services\ServicesCacheRequest;
use Sheba\Cache\Category\HighDemand\CategoryHighDemandCacheRequest;
use Sheba\Cache\Category\Info\CategoryCacheRequest;
use Sheba\Cache\Category\Tree\CategoryTreeCacheRequest;
use Sheba\Cache\CategoryGroup\CategoryGroupCacheRequest;
use Sheba\Dal\LocationService\Events\LocationServiceSaved;

class LocationServiceListener
{
    public function handle(LocationServiceSaved $event)
    {
        /** @var LocationService $location_service */
        $location_service = $event->model;
        /** @var Service $service */
        $service = $location_service->service;
        /** @var CacheAside $cache_aside */
        $cache_aside = app(CacheAside::class);


        /** @var CategoryGroupCacheRequest $category_group_cache */
        $category_group_cache = new CategoryGroupCacheRequest();
        $category_group_cache->setLocationId($location_service->location_id);
        $cache_aside->setCacheRequest($category_group_cache)->deleteEntity();

        $category_tree_cache = new CategoryTreeCacheRequest();
        $category_tree_cache->setLocationId($location_service->location_id);
        $cache_aside->setCacheRequest($category_tree_cache)->deleteEntity();

        $category_cache = new CategoryCacheRequest();
        $category_cache->setCategoryId($service->category_id);
        $cache_aside->setCacheRequest($category_cache)->deleteEntity();

        $category_children = new CategoryChildrenCacheRequest();
        $category_children->setLocationId($location_service->location_id);
        $cache_aside->setCacheRequest($category_children)->deleteEntity();

        $services_cache = new ServicesCacheRequest();
        $services_cache->setLocationId($location_service->location_id);
        $cache_aside->setCacheRequest($services_cache)->deleteEntity();

        $category_highdemand_cache = new CategoryHighDemandCacheRequest();
        $category_highdemand_cache->setLocationId($location_service->location_id);
        $cache_aside->setCacheRequest($category_highdemand_cache)->deleteEntity();
    }
}