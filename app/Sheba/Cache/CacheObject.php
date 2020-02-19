<?php namespace Sheba\Cache;


interface CacheObject
{
    public function getCacheName(): string;

    public function getRedisNamespace(): string;

    public function getExpirationTimeInSeconds(): int;

    public function generate(): DataStoreObject;
}