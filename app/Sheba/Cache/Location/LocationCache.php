<?php namespace Sheba\Cache\Location;


use Sheba\Cache\CacheObject;
use Sheba\Cache\DataStoreObject;

class LocationCache implements CacheObject
{
    private $dataStoreObject;

    public function __construct(LocationDataStoreObject $data_store_object)
    {
        $this->dataStoreObject = $data_store_object;
    }

    public function getCacheName(): string
    {
        return sprintf("%s::%s", $this->getRedisNamespace(), 'v3');
    }

    public function getRedisNamespace(): string
    {
        return 'locations';
    }

    public function generate(): DataStoreObject
    {
        $this->dataStoreObject->generateData();
        return $this->dataStoreObject;
    }

    public function getExpirationTimeInSeconds(): int
    {
        return 24 * 60 * 60;
    }
}