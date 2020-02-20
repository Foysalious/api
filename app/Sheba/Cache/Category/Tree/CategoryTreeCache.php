<?php namespace Sheba\Cache\Category\Tree;


use App\Sheba\Cache\Category\Tree\CategoryTreeDataStore;
use Sheba\Cache\CacheObject;
use Sheba\Cache\DataStoreObject;

class CategoryTreeCache implements CacheObject
{
    private $locationId;
    private $categoryTreeDataStore;

    public function setLocationId($locationId)
    {
        $this->locationId = $locationId;
        return $this;
    }

    public function __construct(CategoryTreeDataStore $category_tree_dataStore)
    {
        $this->categoryTreeDataStore = $category_tree_dataStore;
    }

    public function getCacheName(): string
    {
        return sprintf("%s::%s_%d", $this->getRedisNamespace(), 'location', $this->locationId);
    }

    public function getRedisNamespace(): string
    {
        return 'category_tree';
    }

    public function generate(): DataStoreObject
    {
        $this->categoryTreeDataStore->setLocationId($this->locationId)->generateData();
        return $this->categoryTreeDataStore;
    }

    public function getExpirationTimeInSeconds(): int
    {
        return 1 * 60 * 60;
    }
}