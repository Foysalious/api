<?php namespace Sheba\Cache\Category\Children\Services;

use GuzzleHttp\Client;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;

class ServicesDataStore implements DataStoreObject
{
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
        $client = new Client();
        $response = $client->get(config('sheba.api_url') . '/v2/categories/' . $this->servicesCacheRequest->getCategoryId() . '/services?location=' . $this->servicesCacheRequest->getLocationId());
        $data = json_decode($response->getBody());
        if (!$data || $data->code != 200) return null;
        return ['category' => $data->category];
    }

}