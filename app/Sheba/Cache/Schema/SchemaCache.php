<?php namespace Sheba\Cache\Schema;

use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;

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
        // TODO: Implement getAllKeysRegularExpression() method.
    }
}