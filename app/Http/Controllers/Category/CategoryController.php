<?php namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryGroupCategory;
use App\Models\HyperLocal;
use App\Transformers\Category\CategoryTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use Sheba\Cache\CacheAside;
use Sheba\Cache\Category\Tree\CategoryTreeCache;
use Sheba\Cache\Category\Tree\CategoryTreeCacheRequest;
use Sheba\Dal\UniversalSlug\Model as UniversalSlugModel;
use Sheba\Dal\UniversalSlug\SluggableType;
use League\Fractal\Resource\Item;

class CategoryController extends Controller
{
    public function show($category, Request $request)
    {
        $category = Category::find($category);
        if (!$category) return api_response($request, null, 404);
        $fractal = new Manager();
        $resource = new Item($category, new CategoryTransformer());
        $data = $fractal->createData($resource)->toArray()['data'];
        return api_response($request, $data, 200, ['category' => $data]);
    }

    public function getSecondaries($category, Request $request)
    {
        $this->validate($request, ['lat' => 'required|numeric', 'lng' => 'required|numeric']);
        $category = Category::find($category);
        $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
        if (!$hyperLocation) return api_response($request, null, 404);
        $location = $hyperLocation->location;
        $best_deal_categories_id = explode(',', config('sheba.best_deal_ids'));
        $best_deal_category = CategoryGroupCategory::whereIn('category_group_id', $best_deal_categories_id)->pluck('category_id')->toArray();
        $category->load(['children' => function ($q) use ($best_deal_category, $location) {
            $q->select('id', 'name', 'parent_id', 'app_thumb', 'thumb', 'app_banner', 'banner', 'short_description', 'long_description')->published()->orderBy('order')
                ->whereNotIn('id', $best_deal_category)->whereHas('locations', function ($q) use ($location) {
                    $q->where('locations.id', $location->id);
                })->whereHas('services', function ($q) use ($location) {
                    $q->published()->whereHas('locations', function ($q) use ($location) {
                        $q->where('locations.id', $location->id);
                    });
                });
        }]);
        $category['slug'] = $category->getSlug();
        $children = $category->children;
        $secondary_categories_slug = UniversalSlugModel::where('sluggable_type', SluggableType::SECONDARY_CATEGORY)->pluck('slug', 'sluggable_id')->toArray();
        $children = $children->map(function ($child) use ($secondary_categories_slug) {
            $child['slug'] = array_key_exists($child->id, $secondary_categories_slug) ? $secondary_categories_slug[$child->id] : null;
            return $child;
        });
        if (count($children) == 0) return api_response($request, null, 404);
        $category = collect($category)->only(['name', 'id', 'banner', 'app_banner', 'slug']);
        $category->put('secondaries', $children->values()->all());
        return api_response($request, $category, 200, ['category' => $category]);
    }

    public function getTree(Request $request, CacheAside $cacheAside, CategoryTreeCacheRequest $categoryTreeCache)
    {
        $this->validate($request, ['location_id' => 'required|numeric']);
        $categoryTreeCache->setLocationId($request->location_id);
        return api_response($request, 1, 200, $cacheAside->setCacheRequest($categoryTreeCache)->getMyEntity());
    }
}
