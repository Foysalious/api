<?php namespace Sheba\Cache\Category\Review;

use App\Models\Category;
use Sheba\Cache\CacheObject;
use Sheba\Cache\DataStoreObject;

class ReviewCache implements CacheObject
{
    private $categoryId;
    /** @var DataStoreObject */
    private $dataStoreObject;

    public function __construct(ReviewDataStore $data_store_object)
    {
        $this->dataStoreObject = $data_store_object;
    }

    public function setCategoryId($category_id)
    {
        $this->categoryId = (int)$category_id;
        return $this;
    }

    public function getCacheName(): string
    {
        return sprintf("%s::%s_%d", $this->getRedisNamespace(), 'category', $this->categoryId);
    }

    public function getRedisNamespace(): string
    {
        return 'reviews';
    }

    public function generate(): DataStoreObject
    {
        $category = Category::find($this->categoryId);
        $this->dataStoreObject->setCategory($category)->generateData();
        return $this->dataStoreObject;
    }

    public function getExpirationTimeInSeconds(): int
    {
        return 24 * 60 * 60;
    }


}