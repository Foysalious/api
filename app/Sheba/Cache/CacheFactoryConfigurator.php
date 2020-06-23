<?php namespace Sheba\Cache;

use Sheba\Cache\Category\Children\CategoryChildrenCacheFactory;
use Sheba\Cache\Category\Children\Services\ServicesCacheFactory;
use Sheba\Cache\Category\HighDemand\CategoryHighDemandCacheFactory;
use Sheba\Cache\Category\Info\CategoryCacheFactory;
use Sheba\Cache\Category\Review\ReviewCacheFactory;
use Sheba\Cache\Category\Tree\CategoryTreeCacheFactory;
use Sheba\Cache\CategoryGroup\CategoryGroupCacheFactory;
use Sheba\Cache\Location\LocationCacheFactory;
use Sheba\Cache\CacheName as CacheName;
use Sheba\Cache\Schema\SchemaCacheFactory;
use Sheba\Cache\Sitemap\SitemapCacheFactory;

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
        elseif ($name == CacheName::CATEGORY_TREE) return new CategoryTreeCacheFactory();
        elseif ($name == CacheName::CATEGORY_GROUP) return new CategoryGroupCacheFactory();
        elseif ($name == CacheName::CATEGORY) return new CategoryCacheFactory();
        elseif ($name == CacheName::CATEGORY_CHILDREN) return new CategoryChildrenCacheFactory();
        elseif ($name == CacheName::SECONDARY_CATEGORY_SERVICES) return new ServicesCacheFactory();
        elseif ($name == CacheName::HIGH_DEMAND_CATEGORY) return new CategoryHighDemandCacheFactory();
        elseif ($name == CacheName::SITEMAP) return new SitemapCacheFactory();
    }
}
