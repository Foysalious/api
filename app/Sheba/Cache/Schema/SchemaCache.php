<?php namespace Sheba\Cache\Schema;

use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;

class SchemaCache implements CacheObject
{
    /** @var SchemaCacheRequest */
    private $schemaCacheRequest;

    public function getCacheName(): string
    {
        return sprintf("%s::%s::%d", $this->getRedisNamespace(), strtolower($this->schemaCacheRequest->getType()), $this->schemaCacheRequest->getTypeId());
    }

    public function getRedisNamespace(): string
    {
        return 'schema';
    }

    public function getExpirationTimeInSeconds(): int
    {
        return 24 * 60 * 60;
    }

    public function setCacheRequest(CacheRequest $cache_request)
    {
        $this->schemaCacheRequest = $cache_request;
        return $this;
    }

    public function getAllKeysRegularExpression(): string
    {
        $type = $this->schemaCacheRequest->getType();
        $type_id = $this->schemaCacheRequest->getTypeId();
        return $this->getRedisNamespace() . '::' . ($type ? strtolower($type) : '*') . '::' . ($type_id ? $type_id : '*');
    }
}