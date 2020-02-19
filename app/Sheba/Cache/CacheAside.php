<?php namespace Sheba\Cache;


use Illuminate\Contracts\Cache\Repository;
use Cache;

class CacheAside
{
    /** @var CacheObject */
    private $cacheObject;
    /** @var Repository $store */
    private $store;

    public function __construct()
    {
        $this->store = Cache::store('redis');
    }

    public function setCacheObject(CacheObject $cache_object)
    {
        $this->cacheObject = $cache_object;
        return $this;
    }

    public function getMyEntity()
    {
        $cache = $this->store->get($this->cacheObject->getCacheName());
        if ($cache) return json_decode($cache, true);
        $data_store_object = $this->cacheObject->generate();
        $this->store->put($this->cacheObject->getCacheName(), json_encode($data_store_object->get()), $this->cacheObject->getExpirationTimeInSeconds());
        return $data_store_object->get();
    }
}