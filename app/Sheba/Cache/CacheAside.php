<?php namespace Sheba\Cache;


use Illuminate\Contracts\Cache\Repository;
use Cache;

class CacheAside
{
    /** @var CacheObject */
    private $cacheObject;
    /** @var DataStoreObject */
    private $dataStoreObject;
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

    public function setDataStoreObject(DataStoreObject $data_store_object)
    {
        $this->dataStoreObject = $data_store_object;
        return $this;
    }

    /**
     * @return array|mixed
     */
    public function getMyEntity()
    {
        $cache = $this->store->get($this->cacheObject->getCacheName());
        if ($cache) return json_decode($cache, true);
        $data_store_object = $this->cacheObject->generate();
        $this->setOnCache($data_store_object);
        return $data_store_object->get();
    }

    public function setEntity()
    {
        $data_store_object = $this->cacheObject->generate();
        $this->deleteEntity();
        $this->setOnCache($data_store_object);
    }

    public function deleteEntity()
    {
        $this->store->forget($this->cacheObject->getCacheName());
    }

    private function setOnCache(DataStoreObject $data_store_object)
    {
        $this->store->put($this->cacheObject->getCacheName(), json_encode($data_store_object->get()), $this->cacheObject->getExpirationTimeInSeconds());
    }
}