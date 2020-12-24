<?php namespace Sheba\Cache\Schema;

use Sheba\Cache\CacheName;
use Sheba\Cache\CacheRequest;

class SchemaCacheRequest implements CacheRequest
{
    private $type;
    private $typeId;

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTypeId()
    {
        return (int)$this->typeId;
    }

    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;
        return $this;
    }

    public function getFactoryName()
    {
        return CacheName::SCHEMAS;
    }
}
