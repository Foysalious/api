<?php namespace Sheba\Cache;

use Sheba\Cache\Category\Review\ReviewCacheFactory;
use Sheba\Cache\Location\LocationCacheFactory;
use Sheba\Cache\CacheName as CacheName;
use Sheba\Cache\Schema\SchemaCacheFactory;

class CacheFactoryConfigurator
{
    /**
     * @param $name
     * @return CacheFactory
     */
    public function getFactory($name)
    {
        if ($name == CacheName::CATEGORY_REVIEWS) return new ReviewCacheFactory();
        elseif ($name == CacheName::LOCATIONS) return new LocationCacheFactory();
        elseif ($name == CacheName::SCHEMAS) return new SchemaCacheFactory();
    }

}