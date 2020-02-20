<?php namespace Sheba\Cache\Schema;

use Sheba\Cache\CacheObject;
use Sheba\Cache\DataStoreObject;

class SchemaCache implements CacheObject
{
    private $type;
    private $typeId;
    /** @var DataStoreObject */
    private $dataStoreObject;

    public function __construct(SchemaDataStoreObject $data_store_object)
    {
        $this->dataStoreObject = $data_store_object;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;
        return $this;
    }

    public function getCacheName(): string
    {
        return sprintf("%s::%s_%d", $this->getRedisNamespace(), strtolower($this->type), $this->typeId);
    }

    public function getRedisNamespace(): string
    {
        return 'schema';
    }

    public function getExpirationTimeInSeconds(): int
    {
        return 24 * 60 * 60;
    }

    public function generate(): DataStoreObject
    {
        $model_name = "App\\Models\\" . ucfirst($this->type);
        $model = $model_name::find((int)$this->typeId);
        $this->dataStoreObject->setModel($model)->generateData();
        return $this->dataStoreObject;
    }
}