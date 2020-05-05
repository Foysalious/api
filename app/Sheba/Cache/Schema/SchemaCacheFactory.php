<?php namespace Sheba\Cache\Schema;


use Sheba\Cache\CacheFactory;
use Sheba\Cache\CacheObject;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;

class SchemaCacheFactory implements CacheFactory
{

    public function getCacheObject(CacheRequest $cacheRequest): CacheObject
    {
        $schema_cache = new SchemaCache();
        $schema_cache->setCacheRequest($cacheRequest);
        return $schema_cache;
    }

    public function getDataStoreObject(CacheRequest $cacheRequest): DataStoreObject
    {
        $schema_data = app(SchemaDataStore::class);
        $schema_data->setCacheRequest($cacheRequest);
        return $schema_data;
    }
}