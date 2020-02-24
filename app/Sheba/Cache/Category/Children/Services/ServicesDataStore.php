<?php namespace Sheba\Cache\Category\Children\Services;

use Dingo\Api\Routing\Helpers;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;

class ServicesDataStore implements DataStoreObject
{
    use Helpers;
    /** @var ServicesCacheRequest */
    private $servicesCacheRequest;


    public function setCacheRequest(CacheRequest $cache_request)
    {
        $this->servicesCacheRequest = $cache_request;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function generate()
    {
        $category = $this->api->get('/v2/categories/' . $this->servicesCacheRequest->getCategoryId() . '/services?location=' . $this->servicesCacheRequest->getLocationId());
        if (!$category) return null;
        return ['category' => $category];
    }

}