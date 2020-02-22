<?php namespace Sheba\Cache;


interface DataStoreObject
{

    /**
     * @param CacheRequest $request
     * @return mixed
     */
    public function setCacheRequest(CacheRequest $request);

    /**
     * @return array|null
     */
    public function generate();
}