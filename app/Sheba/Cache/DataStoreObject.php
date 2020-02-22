<?php namespace Sheba\Cache;


interface DataStoreObject
{
    public function setCacheRequest(CacheRequest $request);

    public function generate(): array;

}