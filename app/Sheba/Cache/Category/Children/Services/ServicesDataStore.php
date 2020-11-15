<?php namespace Sheba\Cache\Category\Children\Services;

use GuzzleHttp\Client;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;
use Sheba\Cache\Exceptions\CacheGenerationException;
use Throwable;

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
     * @throws CacheGenerationException
     */
    public function generate()
    {
        $client = new Client();
        try {
            $response = $client->get(config('sheba.api_url') . '/v2/categories/' . $this->servicesCacheRequest->getCategoryId() . '/services?location=' . $this->servicesCacheRequest->getLocationId());
        } catch (Throwable $e) {
            throw new CacheGenerationException();
        }
        $data = json_decode($response->getBody());
        if (!$data || $data->code != 200) return null;
        return ['category' => $data->category];
    }

}