<?php namespace Sheba\Cache\CategoryGroup;


use Sheba\Cache\CacheObject;
use Sheba\Cache\DataStoreObject;

class CategoryGroupCache implements CacheObject
{
    private $dataStoreObject;
    private $categoryGroupId;
    private $locationId;

    public function __construct(CategoryGroupDataStoreObject $data_store_object)
    {
        $this->dataStoreObject = $data_store_object;
    }

    public function setLocationId($location_id)
    {
        $this->locationId = $location_id;
        return $this;
    }

    public function setCategoryGroupId($categoryGroupId)
    {
        $this->categoryGroupId = $categoryGroupId;
        return $this;
    }

    public function getCacheName(): string
    {
        return sprintf("%s::%d_%s_%d", $this->getRedisNamespace(), $this->categoryGroupId, 'location', $this->locationId);
    }

    public function getRedisNamespace(): string
    {
        return 'category_group';
    }

    public function generate(): DataStoreObject
    {
        $this->dataStoreObject->setCategoryGroupId($this->categoryGroupId)->setLocationId($this->locationId)->generateData();
        return $this->dataStoreObject;
    }

    public function getExpirationTimeInSeconds(): int
    {
        return 30 * 24 * 60 * 60;
    }
}