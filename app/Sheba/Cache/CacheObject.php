<?php namespace Sheba\Cache;


interface CacheObject
{
    public function setCacheRequest(CacheRequest $cache_request);

    public function getCacheName(): string;

    public function getRedisNamespace(): string;

    public function getExpirationTimeInSeconds(): int;

    public function getAllKeysRegularExpression(): string ;
}