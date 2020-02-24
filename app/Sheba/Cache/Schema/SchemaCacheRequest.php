<?php namespace Sheba\Cache\Schema;


use Sheba\Cache\CacheName;
use Sheba\Cache\CacheRequest;

class SchemaCacheRequest implements CacheRequest
{
    private $type;
    private $typeId;

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

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getTypeId()
    {
        return (int)$this->typeId;
    }


    public function getFactoryName()
    {
        return CacheName::SCHEMAS;
    }
}