<?php namespace App\Sheba\Cache\Category\Tree;


use App\Models\Category;
use App\Models\CategoryGroupCategory;
use App\Models\Location;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\Category\Tree\CategoryTreeCacheRequest;
use Sheba\Cache\DataStoreObject;
use Sheba\Dal\UniversalSlug\Model as UniversalSlugModel;
use Sheba\Dal\UniversalSlug\SluggableType;

class CategoryTreeDataStore implements DataStoreObject
{
    /** @var CategoryTreeCacheRequest */
    private $categoryTreeRequest;

    public function setCacheRequest(CacheRequest $request)
    {
        $this->categoryTreeRequest = $request;
        return $this;
    }

    public function generate()
    {
        $location = Location::where('id', $this->categoryTreeRequest->getLocationId())->published()->hasGeoInformation()->first();
        if (!$location || !$location->hyperLocal) return null;
        $best_deal_category_group_id = explode(',', config('sheba.best_deal_ids'));
        $best_deal_category_ids = CategoryGroupCategory::select('category_group_id', 'category_id')
            ->whereIn('category_group_id', $best_deal_category_group_id)->pluck('category_id')->toArray();
        $categories = Category::published()
            ->whereHas('locations', function ($q) {
                $q->select('locations.id')->where('locations.id', $this->categoryTreeRequest->getLocationId());
            })
            ->whereHas('children', function ($q) use ($best_deal_category_ids) {
                $q->select('id', 'parent_id')->published()->whereNotIn('id', $best_deal_category_ids)
                    ->whereHas('locations', function ($q) {
                        $q->select('locations.id')->where('locations.id', $this->categoryTreeRequest->getLocationId());
                    })->whereHas('services', function ($q) {
                        $q->select('services.id')->published()->whereHas('locations', function ($q) {
                            $q->select('locations.id')->where('locations.id', $this->categoryTreeRequest->getLocationId());
                        });
                    });
            })
            ->with(['children' => function ($q) use ($best_deal_category_ids) {
                $q->select('id', 'name', 'thumb', 'parent_id', 'app_thumb', 'icon_png', 'icon_png_hover', 'icon_png_active', 'icon', 'icon_hover', 'slug', 'is_auto_sp_enabled')
                    ->whereHas('locations', function ($q) {
                        $q->select('locations.id')->where('locations.id', $this->categoryTreeRequest->getLocationId());
                    })->whereHas('services', function ($q) {
                        $q->select('services.id')->published()->whereHas('locations', function ($q) {
                            $q->select('locations.id')->where('locations.id', $this->categoryTreeRequest->getLocationId());
                        });
                    })->whereNotIn('id', $best_deal_category_ids)
                    ->published()->orderBy('order');
            }])
            ->select('id', 'name', 'parent_id', 'icon_png', 'icon_png_hover', 'icon_png_active', 'app_thumb', 'app_banner', 'is_auto_sp_enabled')
            ->parent()->orderBy('order')->get();
        $ids = [];
        foreach ($categories as $master_category) {
            array_push($ids, $master_category->id);
            foreach ($master_category->children as $category) {
                array_push($ids, $category->id);
            }
        }
        $slugs = UniversalSlugModel::where('sluggable_type', 'like', '%' . 'category')->select('slug', 'sluggable_id')->whereIn('sluggable_id', $ids)->get()
            ->pluck('slug', 'sluggable_id')->toArray();
        foreach ($categories as &$category) {
            $category->slug = isset($slugs[$category->id]) ? $slugs[$category->id] : null;
            array_forget($category, 'parent_id');

            foreach ($category->children as &$child) {
                $category->slug = isset($slugs[$category->id]) ? $slugs[$category->id] : null;
                array_forget($child, 'parent_id');
            }
        }
        return count($categories) > 0 ? ['categories' => $categories] : null;
    }
}