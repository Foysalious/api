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
        $master_categories = Category::select('id', 'name')->parents()->has('subCat')->get();

        foreach ($master_categories as $master_category) {
            $is_car_rental = $master_category->isRentMaster();
            $car_rental_slug = config('sheba.car_rental.slug');

            $master_category['slug'] = $is_car_rental ? $car_rental_slug : $master_category->getSlug();
            $master_category['secondary_categories'] = $master_category->subCat()->select('id', 'name', 'parent_id')->has('publishedServices')->get();


            foreach ( $master_category['secondary_categories'] as $secondary_category) {
                $secondary_category['slug'] = $is_car_rental ? $car_rental_slug : $secondary_category->getSlug();
                $secondary_category['services'] = $secondary_category->publishedServices()->select('id', 'name')->get();

                foreach ( $secondary_category['services'] as $service) {
                    $service['slug'] = $is_car_rental ? $car_rental_slug : $service->getSlug();
                }

                $secondary_category['services'] = $secondary_category['services']->filter(function ($service, $key) {
                    return  $service['slug'] != null;
                });

                $secondary_category['services'] = $secondary_category['services']->toArray();
            }

            $master_category['secondary_categories'] = $master_category['secondary_categories']->filter(function ($cat, $key) {
                return  $cat['slug'] != null;
            });

            $master_category['secondary_categories'] =  $master_category['secondary_categories']->toArray();
        }

        $master_categories = $master_categories->filter(function ($cat, $key) {
            return  $cat['slug'] != null;
        });


        return $master_categories->toArray();
    }
}