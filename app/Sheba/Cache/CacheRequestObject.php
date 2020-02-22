<?php namespace Sheba\Cache;


interface CacheRequestObject
{
    public function getCacheObject(): CacheObject;

    public function getDataStoreObject(): DataStoreObject;
}