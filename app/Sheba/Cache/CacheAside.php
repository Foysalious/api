<?php namespace Sheba\Cache;

use Illuminate\Contracts\Cache\Repository;
use Cache;
use Illuminate\Support\Facades\Redis;

class CacheAside
{
    /** @var CacheObject */
    private $cacheObject;
    /** @var DataStoreObject */
    private $dataStoreObject;
    /** @var Repository $store */
    private $store;
    /** @var CacheFactory */
    private $cacheFactory;
    /** @var CacheRequest */
    private $cacheRequest;
    private $cacheFactoryConfigurator;

    public function __construct(CacheFactoryConfigurator $cacheFactoryConfigurator)
    {
        $this->store = Cache::store('redis');
        $this->cacheFactoryConfigurator = $cacheFactoryConfigurator;
    }

    public function setCacheRequest($cacheRequest)
    {
        $this->cacheRequest = $cacheRequest;
        $this->setCacheFactory();

        return $this;
    }

    private function setCacheFactory()
    {
        $this->cacheFactory = $this->cacheFactoryConfigurator->getFactory($this->cacheRequest->getFactoryName());
        $this->setCacheObject($this->cacheFactory->getCacheObject($this->cacheRequest));
        $this->setDataStoreObject($this->cacheFactory->getDataStoreObject($this->cacheRequest));

        return $this;
    }

    private function setCacheObject(CacheObject $cacheObject)
    {
        $this->cacheObject = $cacheObject;
        return $this;
    }

    private function setDataStoreObject(DataStoreObject $dataStoreObject)
    {
        $this->dataStoreObject = $dataStoreObject;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getMyEntity()
    {
        $cache = $this->store->get($this->cacheObject->getCacheName());
        if ($cache && $data = json_decode($cache, true)) return $data;
        $data = $this->dataStoreObject->generate();
        $this->setOnCache($data);
        return $data;
    }

    private function setOnCache(array $data = null)
    {
        $this->store->put($this->cacheObject->getCacheName(), json_encode($data), $this->cacheObject->getExpirationTimeInSeconds() / 60);
    }

    public function setEntity()
    {
        $data = $this->dataStoreObject->generate();
        $this->deleteEntity();
        $this->setOnCache($data);
    }

    public function deleteEntity()
    {
        $regex = "laravel:" . $this->cacheObject->getAllKeysRegularExpression();
        $keys = Redis::keys($regex);
        foreach ($keys as $key) {
            Redis::del($key);
        }
    }
}
