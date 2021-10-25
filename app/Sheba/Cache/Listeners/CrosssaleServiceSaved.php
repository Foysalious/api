<?php namespace Sheba\Cache\Listeners;


use Sheba\Dal\CrosssaleService\Events\CrosssaleServiceSaved as CrosssaleServiceSavedEvent;
use Sheba\Dal\Service\Service;
use Sheba\Cache\CacheAside;
use Sheba\Cache\Category\Children\Services\ServicesCacheRequest;

class CrosssaleServiceSaved
{
    public function handle(CrosssaleServiceSavedEvent $event)
    {
        /** @var Service $service */
        $service = Service::find($event->model->service_id);
        /** @var CacheAside $cache_aside */
        $cache_aside = app(CacheAside::class);

        $services_cache = new ServicesCacheRequest();
        $services_cache->setCategoryId($service->category_id);
        $cache_aside->setCacheRequest($services_cache)->deleteEntity();
    }
}
