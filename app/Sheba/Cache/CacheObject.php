<?php namespace Sheba\Cache;


interface CacheObject
{
    public function getCacheName(): string;

    public function getRedisNamespace(): string;

    public function generate(): DataStoreObject;

    public function getExpirationTimeInSeconds(): int;
}