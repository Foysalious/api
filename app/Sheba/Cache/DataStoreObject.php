<?php namespace Sheba\Cache;

use Sheba\Cache\Exceptions\CacheGenerationException;

interface DataStoreObject
{
    public function setCacheRequest(CacheRequest $cache_request);

    /**
     * @return array|null
     * @throws CacheGenerationException
     */
    public function generate();
}
