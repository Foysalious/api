<?php namespace Sheba\Cache\Listeners;

use Sheba\Cache\CacheAside;
use Sheba\Cache\Category\Children\Services\ServicesCacheRequest;
use Sheba\Dal\Discount\Discount;
use Sheba\Dal\Discount\Events\DiscountSaved;

class DiscountSavedListener
{
    public function handle(DiscountSaved $event)
    {
        /** @var Discount $job_material */
        $discount = $event->model;

        /** @var CacheAside $cache_aside */
        $cache_aside = app(CacheAside::class);

        $services_cache = new ServicesCacheRequest();
        $cache_aside->setCacheRequest($services_cache)->deleteEntity();
    }
}
