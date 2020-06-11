<?php


namespace Sheba\Cache\Sitemap;


use App\Models\Category;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;
use Sheba\Dal\UniversalSlug\Model as UniversalSlugModel;

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
        $categories = [];
        $services = [];
        $master_categories = Category::whereHas('subCat', function ($q) {
            $q->has('publishedServices');
        })->with(['subCat' => function ($q) {
            $q->has('publishedServices')->select('id', 'parent_id', 'name')->with(['services' => function ($q) {
                $q->select('id', 'name', 'category_id')->published();
            }]);
        }])->parent()->published()->select('id', 'name')->get();
        foreach ($master_categories as $master_category) {
            array_push($categories, $master_category->id);
            $master_category['secondary_categories'] = $master_category->subCat;
            array_forget($master_category, 'subCat');
            foreach ($master_category->secondary_categories as $category) {
                array_push($categories, $category->id);
                foreach ($category->services as $service) {
                    array_push($services, $service->id);
                }
            }
        }
        $category_slugs = UniversalSlugModel::where('sluggable_type', 'like', '%' . 'category')->select('slug', 'sluggable_id')->whereIn('sluggable_id', $categories)->get()->pluck('slug', 'sluggable_id')->toArray();
        $service_slugs = UniversalSlugModel::where('sluggable_type', 'like', '%' . 'service')->select('slug', 'sluggable_id')->whereIn('sluggable_id', $services)->get()->pluck('slug', 'sluggable_id')->toArray();
        foreach ($master_categories as $master_category) {
            $master_category['slug'] = $category_slugs[$master_category->id] ?? null;
            foreach ($master_category->secondary_categories as $category) {
                $category['slug'] = $category_slugs[$category->id] ?? null;
                foreach ($category->services as $service) {
                    $service['slug'] = $service_slugs[$service->id] ?? null;
                }
            }
        }
        return array_values($master_categories->toArray());
    }
}