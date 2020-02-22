<?php namespace Sheba\Cache;


interface CacheFactory
{
    public function getCacheObject(CacheRequest $cacheRequest): CacheObject;

    public function getDataStoreObject(CacheRequest $cacheRequest): DataStoreObject;
}