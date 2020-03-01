<?php namespace Sheba\Cache;

interface DataStoreObject
{
    public function setCacheRequest(CacheRequest $cache_request);

    /**
     * @return array|null
     */
    public function generate();
}
