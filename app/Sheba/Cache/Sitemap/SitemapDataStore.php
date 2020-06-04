<?php


namespace Sheba\Cache\Sitemap;


use App\Models\Category;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;

class SitemapDataStore implements DataStoreObject
{

    public function setCacheRequest(CacheRequest $cache_request)
    {
        // TODO: Implement setCacheRequest() method.
    }

    public function generate()
    {
        $data = $this->generateMasterCategoryTree();
        return $data;
    }

    public function generateMasterCategoryTree()
    {
        $master_categories = Category::select('id', 'name')->parents()->get();

        foreach ($master_categories as $master_category) {
            $master_category['slug'] = $master_category->getSlug();
            $master_category['secondary_categories'] = $master_category->subCat()->select('id', 'name')->get();

            foreach ( $master_category['secondary_categories'] as $secondary_category) {
                $secondary_category['slug'] = $secondary_category->getSlug();
                $secondary_category['services'] = $secondary_category->publishedServices()->select('id', 'name')->get();

                foreach ( $secondary_category['services'] as $service) {
                    $service['slug'] = $service->getSlug();
                }

                $secondary_category['services'] = $secondary_category['services']->toArray();
            }

            $master_category['secondary_categories'] =  $master_category['secondary_categories']->toArray();
        }

        return $master_categories->toArray();
    }
}