<?php namespace Sheba\Cache\Category\Children;


use App\Models\Category;
use App\Models\CategoryGroupCategory;
use App\Models\HyperLocal;
use App\Models\Location;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;
use Sheba\Dal\UniversalSlug\Model as UniversalSlugModel;
use Sheba\Dal\UniversalSlug\SluggableType;

class CategoryChildrenDataStore implements DataStoreObject
{
    /** @var CategoryChildrenCacheRequest */
    private $categoryChildrenCacheRequest;

    public function setCacheRequest(CacheRequest $cache_request)
    {
        $this->categoryChildrenCacheRequest = $cache_request;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function generate()
    {
        $category = Category::where([['parent_id', null], ['id', $this->categoryChildrenCacheRequest->getCategoryId()]])->first();
        if (!$category) return null;
        $location = Location::published()->where('id', $this->categoryChildrenCacheRequest->getLocationId())->first();
        if (!$location) return null;
        $best_deal_categories_id = explode(',', config('sheba.best_deal_ids'));
        $best_deal_category = CategoryGroupCategory::whereIn('category_group_id', $best_deal_categories_id)->pluck('category_id')->toArray();
        $category->load(['children' => function ($q) use ($best_deal_category, $location) {
            $q->select('id', 'name', 'parent_id', 'app_thumb', 'thumb', 'app_banner', 'banner', 'short_description', 'long_description', 'is_vat_applicable')->published()->orderBy('order')
                ->whereNotIn('id', $best_deal_category)->whereHas('locations', function ($q) use ($location) {
                    $q->where('locations.id', $location->id);
                })->whereHas('services', function ($q) use ($location) {
                    $q->published()->whereHas('locations', function ($q) use ($location) {
                        $q->where('locations.id', $location->id);
                    });
                });
        }]);
        $children = $category->children;
        if (count($children) == 0) return null;
        $ids = array_merge($children->pluck('id')->toArray(), [$category->id]);
        $slugs = UniversalSlugModel::where('sluggable_type', 'like', '%category')->whereIn('sluggable_id', $ids)->select('sluggable_id', 'slug')->get();
        $category['slug'] = $slugs->where('sluggable_id', $category->id)->first() ? $slugs->where('sluggable_id', $category->id)->first()->slug : null;
        $children->map(function ($child) use ($slugs) {
            $slug = $slugs->where('sluggable_id', $child->id)->first();
            $child['slug'] = $slug ? $slug->slug : null;
            return $child;
        });
        $category = collect($category)->only(['name', 'id', 'banner', 'app_banner', 'slug']);
        $category->put('secondaries', $children->values()->all());
        return ['category' => $category];
    }
}